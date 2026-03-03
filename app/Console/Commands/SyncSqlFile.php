<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use ZipArchive;

class SyncSqlFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync-sql-file 
                            {--skip-files : Пропустить синхронизацию файлов (только БД)}
                            {--dry-run : Показать что будет сделано без выполнения}
                            {--insecure : Отключить проверку SSL сертификата}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Синхронизация базы данных и файлов public/upload с сервером';

    /**
     * Таблицы, которые нужно исключить из синхронизации
     */
    protected $excludedTables = [
        'users',
        'telegraph_chats',
        'telegraph_bots',
        'telegram_settings',
        'telegram_admin_requests',
        'sessions',
        'role_user',
        'roles',
        'request_history',
        'product_requests',
        'personal_access_tokens',
        'password_reset_tokens',
        'notifications',
        'job_batches',
        'jobs',
        'migrations',
    ];

    protected $tempDir;
    protected $sqlFile;
    protected $filesArchive;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Начало синхронизации БД и файлов...');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $skipFiles = $this->option('skip-files');

        try {
            // Создаем временную директорию
            $this->tempDir = storage_path('app/temp/sync-' . time());
            if (!$dryRun) {
                File::makeDirectory($this->tempDir, 0755, true);
            }

            // Шаг 1: Создание дампа БД
            $this->createDatabaseDump($dryRun);

            // Шаг 2: Создание архива файлов (если не пропущено)
            if (!$skipFiles) {
                $this->createFilesArchive($dryRun);
            } else {
                $this->warn('⚠️  Пропущена синхронизация файлов (--skip-files)');
            }

            // Шаг 3: Отправка на сервер
            if (!$dryRun) {
                $this->sendToServer($skipFiles);
            } else {
                $this->info('📤 [DRY-RUN] Отправка на сервер пропущена');
            }

            // Очистка временных файлов
            if (!$dryRun) {
                $this->cleanup();
            }

            $this->newLine();
            $this->info('✅ Синхронизация успешно завершена!');
            return 0;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Ошибка синхронизации: ' . $e->getMessage());
            if ($this->option('verbose')) {
                $this->error($e->getTraceAsString());
            }
            
            // Очистка при ошибке
            if (!$dryRun && isset($this->tempDir) && File::exists($this->tempDir)) {
                $this->cleanup();
            }
            
            return 1;
        }
    }

    /**
     * Создание дампа базы данных
     */
    protected function createDatabaseDump(bool $dryRun): void
    {
        $this->info('💾 Шаг 1: Создание дампа базы данных...');

        if ($dryRun) {
            $this->line('  [DRY-RUN] Создание SQL дампа');
            return;
        }

        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        $this->sqlFile = $this->tempDir . '/database.sql';

        if ($connection === 'sqlite') {
            // Для SQLite просто копируем файл
            $dbPath = $config['database'];
            if (!file_exists($dbPath)) {
                throw new \Exception("Файл базы данных не найден: {$dbPath}");
            }
            
            // Создаем SQL дамп из SQLite
            $this->createSqliteDump($dbPath, $this->sqlFile);
        } elseif (in_array($connection, ['mysql', 'mariadb'])) {
            // Для MySQL/MariaDB используем mysqldump
            $this->createMysqlDump($config, $this->sqlFile);
        } else {
            throw new \Exception("Неподдерживаемый тип БД: {$connection}");
        }

        $size = filesize($this->sqlFile);
        $sizeMB = round($size / 1024 / 1024, 2);
        $this->info("  ✅ Дамп создан: {$sizeMB} MB");
        $this->newLine();
    }

    /**
     * Создание дампа SQLite
     */
    protected function createSqliteDump(string $dbPath, string $outputFile): void
    {
        // Используем sqlite3 для создания дампа
        $process = Process::run("sqlite3 " . escapeshellarg($dbPath) . " .dump > " . escapeshellarg($outputFile));
        
        if (!$process->successful()) {
            // Альтернативный способ через PHP
            $this->createSqliteDumpPhp($dbPath, $outputFile);
        }
    }

    /**
     * Создание дампа SQLite через PHP
     */
    protected function createSqliteDumpPhp(string $dbPath, string $outputFile): void
    {
        $db = new \PDO("sqlite:{$dbPath}");
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        $output = fopen($outputFile, 'w');
        
        // Получаем все таблицы
        $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(\PDO::FETCH_COLUMN);
        
        // Фильтруем исключенные таблицы
        $tables = array_filter($tables, function($table) {
            return !in_array($table, $this->excludedTables) && $table !== 'sqlite_sequence';
        });
        
        foreach ($tables as $table) {
            
            // Получаем структуру таблицы
            $createTable = $db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name=" . $db->quote($table))->fetchColumn();
            fwrite($output, $createTable . ";\n\n");
            
            // Получаем данные
            $rows = $db->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $columns = array_keys($row);
                $values = array_map(function($value) use ($db) {
                    return $value === null ? 'NULL' : $db->quote($value);
                }, array_values($row));
                
                $sql = "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
                fwrite($output, $sql);
            }
            fwrite($output, "\n");
        }
        
        fclose($output);
    }

    /**
     * Создание дампа MySQL
     */
    protected function createMysqlDump(array $config, string $outputFile): void
    {
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? '3306';
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];
        
        // Сначала пробуем использовать mysqldump
        $mysqldumpAvailable = $this->checkMysqldumpAvailable();
        
        if ($mysqldumpAvailable) {
            // Формируем список исключаемых таблиц для mysqldump
            $ignoreTables = '';
            if (!empty($this->excludedTables)) {
                $ignoreTables = '--ignore-table=' . $database . '.' . implode(' --ignore-table=' . $database . '.', $this->excludedTables);
            }
            
            $command = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s %s > %s',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                $ignoreTables,
                escapeshellarg($database),
                escapeshellarg($outputFile)
            );
            
            $process = Process::run($command);
            
            if ($process->successful()) {
                if (!empty($this->excludedTables)) {
                    $this->line('  ℹ️  Исключено таблиц из дампа: ' . count($this->excludedTables));
                }
                return; // Успешно создан через mysqldump
            }
            
            // Если mysqldump не сработал, пробуем через PHP
            $this->warn('  ⚠️  mysqldump не сработал, используем PHP метод');
        } else {
            $this->line('  ℹ️  mysqldump не найден, используем PHP метод');
        }
        
        // Альтернативный способ через PHP/PDO
        $this->createMysqlDumpPhp($config, $outputFile);
    }

    /**
     * Проверить доступность mysqldump
     */
    protected function checkMysqldumpAvailable(): bool
    {
        try {
            $process = Process::run('mysqldump --version');
            return $process->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Создание дампа MySQL через PHP
     */
    protected function createMysqlDumpPhp(array $config, string $outputFile): void
    {
        $database = $config['database'];
        
        try {
            // Используем существующее подключение Laravel (уже настроено правильно)
            $pdo = \DB::connection()->getPdo();
            
            $output = fopen($outputFile, 'w');
            
            // Записываем заголовок
            fwrite($output, "-- MySQL dump created by PHP\n");
            fwrite($output, "-- Database: {$database}\n");
            fwrite($output, "-- Date: " . date('Y-m-d H:i:s') . "\n\n");
            fwrite($output, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
            fwrite($output, "SET time_zone = \"+00:00\";\n\n");
            
            // Получаем все таблицы
            $allTables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
            
            // Фильтруем исключенные таблицы
            $tables = array_filter($allTables, function($table) {
                return !in_array($table, $this->excludedTables);
            });
            
            $excludedCount = count($allTables) - count($tables);
            if ($excludedCount > 0) {
                $this->line("  ℹ️  Исключено таблиц из дампа: {$excludedCount}");
            }
            
            foreach ($tables as $table) {
                // Получаем структуру таблицы
                $createTable = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
                fwrite($output, "\n--\n-- Table structure for table `{$table}`\n--\n\n");
                fwrite($output, "DROP TABLE IF EXISTS `{$table}`;\n");
                fwrite($output, $createTable['Create Table'] . ";\n\n");
                
                // Получаем данные
                $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
                
                if (count($rows) > 0) {
                    fwrite($output, "\n--\n-- Dumping data for table `{$table}`\n--\n\n");
                    
                    foreach ($rows as $row) {
                        $columns = array_keys($row);
                        $values = array_map(function($value) use ($pdo) {
                            return $value === null ? 'NULL' : $pdo->quote($value);
                        }, array_values($row));
                        
                        $sql = "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
                        fwrite($output, $sql);
                    }
                    fwrite($output, "\n");
                }
            }
            
            fclose($output);
            
        } catch (\Exception $e) {
            throw new \Exception("Ошибка создания дампа MySQL через PHP: " . $e->getMessage());
        }
    }

    /**
     * Создание архива файлов
     */
    protected function createFilesArchive(bool $dryRun): void
    {
        $this->info('📦 Шаг 2: Создание архива файлов...');

        if ($dryRun) {
            $this->line('  [DRY-RUN] Создание ZIP архива с файлами из public/upload');
            return;
        }

        $uploadDir = public_path('upload');
        
        if (!File::exists($uploadDir)) {
            $this->warn('  ⚠️  Директория public/upload не найдена');
            return;
        }

        $this->filesArchive = $this->tempDir . '/upload_files.zip';
        
        if (!class_exists('ZipArchive')) {
            throw new \Exception('Класс ZipArchive не доступен. Установите расширение php-zip');
        }
        
        $zip = new ZipArchive();
        if ($zip->open($this->filesArchive, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Не удалось создать ZIP архив');
        }

        $files = File::allFiles($uploadDir);
        $fileCount = 0;
        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        foreach ($files as $file) {
            $relativePath = str_replace(public_path('upload') . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $zip->addFile($file->getPathname(), $relativePath);
            $fileCount++;
            $bar->advance();
        }

        $zip->close();
        $bar->finish();
        $this->newLine();

        $size = filesize($this->filesArchive);
        $sizeMB = round($size / 1024 / 1024, 2);
        $this->info("  ✅ Архив создан: {$fileCount} файлов, {$sizeMB} MB");
        $this->newLine();
    }

    /**
     * Отправка на сервер
     */
    protected function sendToServer(bool $skipFiles): void
    {
        $this->info('📤 Шаг 3: Отправка на сервер...');

        $serverUrl = env('SERVER_URL');
        $deployToken = env('DEPLOY_TOKEN');

        if (!$serverUrl) {
            throw new \Exception('SERVER_URL не настроен в .env');
        }

        if (!$deployToken) {
            throw new \Exception('DEPLOY_TOKEN не настроен в .env');
        }

        // Формируем URL
        $syncUrl = rtrim($serverUrl, '/');
        if (str_contains($syncUrl, '/api/')) {
            $pos = strpos($syncUrl, '/api/');
            $syncUrl = substr($syncUrl, 0, $pos);
        }
        $syncUrl .= '/api/sync-sql-file';

        $this->line("  📡 URL: {$syncUrl}");

        try {
            $httpClient = Http::timeout(600); // 10 минут таймаут

            if ($this->option('insecure') || env('APP_ENV') === 'local') {
                $httpClient = $httpClient->withoutVerifying();
                if ($this->option('insecure')) {
                    $this->warn('  ⚠️  Проверка SSL сертификата отключена (--insecure)');
                }
            }

            // Подготавливаем multipart данные
            $multipart = [];
            
            // SQL файл
            $sqlFileHandle = fopen($this->sqlFile, 'r');
            $multipart[] = [
                'name' => 'sql_file',
                'contents' => $sqlFileHandle,
                'filename' => 'database.sql',
            ];
            
            // Архив файлов (если есть)
            $filesArchiveHandle = null;
            if (!$skipFiles && isset($this->filesArchive) && file_exists($this->filesArchive)) {
                $filesArchiveHandle = fopen($this->filesArchive, 'r');
                $multipart[] = [
                    'name' => 'files_archive',
                    'contents' => $filesArchiveHandle,
                    'filename' => 'upload_files.zip',
                ];
            }
            
            // Дополнительные поля
            $multipart[] = [
                'name' => 'skip_files',
                'contents' => $skipFiles ? '1' : '0',
            ];

            $response = $httpClient->withHeaders([
                    'X-Deploy-Token' => $deployToken,
                ])
                ->asMultipart()
                ->post($syncUrl, $multipart);

            // Закрываем файлы
            if ($sqlFileHandle && is_resource($sqlFileHandle)) {
                fclose($sqlFileHandle);
            }
            if ($filesArchiveHandle && is_resource($filesArchiveHandle)) {
                fclose($filesArchiveHandle);
            }

            if ($response->successful()) {
                $data = $response->json();
                
                $this->newLine();
                $this->info('  ✅ Сервер ответил успешно:');
                $this->line("     БД восстановлена: " . ($data['data']['database_restored'] ?? 'unknown'));
                if (!$skipFiles) {
                    $this->line("     Файлов обработано: " . ($data['data']['files_processed'] ?? '0'));
                    $this->line("     Файлов пропущено (дубли): " . ($data['data']['files_skipped'] ?? '0'));
                }
                $this->line("     Время выполнения: {$data['data']['duration_seconds']}с");
            } else {
                $errorData = $response->json();
                throw new \Exception(
                    "Ошибка синхронизации на сервере (HTTP {$response->status()}): " . 
                    ($errorData['message'] ?? $response->body())
                );
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new \Exception("Не удалось подключиться к серверу: " . $e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception("Ошибка отправки запроса: " . $e->getMessage());
        }

        $this->newLine();
    }

    /**
     * Очистка временных файлов
     */
    protected function cleanup(): void
    {
        if (isset($this->tempDir) && File::exists($this->tempDir)) {
            File::deleteDirectory($this->tempDir);
            $this->line('  🧹 Временные файлы удалены');
        }
    }
}

