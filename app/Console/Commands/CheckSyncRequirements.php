<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class CheckSyncRequirements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:check-requirements';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверка требований для команды sync-sql-file на сервере';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Проверка требований для синхронизации БД и файлов...');
        $this->newLine();

        $allOk = true;

        // Проверка 1: PHP Zip расширение
        $this->info('1️⃣ Проверка PHP Zip расширения...');
        if (extension_loaded('zip') && class_exists('ZipArchive')) {
            $this->info('   ✅ PHP Zip расширение установлено');
        } else {
            $this->error('   ❌ PHP Zip расширение НЕ установлено');
            $this->line('   💡 Установите: sudo apt-get install php-zip (Ubuntu/Debian)');
            $this->line('   💡 Или: sudo yum install php-zip (CentOS/RHEL)');
            $allOk = false;
        }
        $this->newLine();

        // Проверка 2: MySQL/MariaDB утилиты
        $this->info('2️⃣ Проверка MySQL/MariaDB утилит...');
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (in_array($connection, ['mysql', 'mariadb'])) {
            // Проверка mysqldump
            $mysqldumpCheck = Process::run('which mysqldump');
            if ($mysqldumpCheck->successful() && !empty(trim($mysqldumpCheck->output()))) {
                $mysqldumpPath = trim($mysqldumpCheck->output());
                $this->info("   ✅ mysqldump найден: {$mysqldumpPath}");
                
                // Проверка версии
                $versionCheck = Process::run('mysqldump --version');
                if ($versionCheck->successful()) {
                    $this->line('   📋 ' . trim($versionCheck->output()));
                }
            } else {
                $this->error('   ❌ mysqldump НЕ найден в PATH');
                $this->line('   💡 Установите MySQL клиент: sudo apt-get install mysql-client (Ubuntu/Debian)');
                $this->line('   💡 Или: sudo yum install mysql (CentOS/RHEL)');
                $allOk = false;
            }

            // Проверка mysql
            $mysqlCheck = Process::run('which mysql');
            if ($mysqlCheck->successful() && !empty(trim($mysqlCheck->output()))) {
                $mysqlPath = trim($mysqlCheck->output());
                $this->info("   ✅ mysql найден: {$mysqlPath}");
                
                // Проверка версии
                $versionCheck = Process::run('mysql --version');
                if ($versionCheck->successful()) {
                    $this->line('   📋 ' . trim($versionCheck->output()));
                }
            } else {
                $this->error('   ❌ mysql НЕ найден в PATH');
                $this->line('   💡 Установите MySQL клиент: sudo apt-get install mysql-client (Ubuntu/Debian)');
                $this->line('   💡 Или: sudo yum install mysql (CentOS/RHEL)');
                $allOk = false;
            }

            // Проверка подключения к БД
            $this->newLine();
            $this->info('   🔌 Проверка подключения к БД...');
            try {
                \DB::connection()->getPdo();
                $this->info('   ✅ Подключение к БД успешно');
                $this->line("   📋 База данных: {$config['database']}");
                $this->line("   📋 Хост: {$config['host']}:{$config['port']}");
            } catch (\Exception $e) {
                $this->warn('   ⚠️  Не удалось подключиться к БД: ' . $e->getMessage());
            }
        } elseif ($connection === 'sqlite') {
            $this->info('   ℹ️  Используется SQLite - MySQL утилиты не требуются');
            
            // Проверка sqlite3
            $sqliteCheck = Process::run('which sqlite3');
            if ($sqliteCheck->successful() && !empty(trim($sqliteCheck->output()))) {
                $sqlitePath = trim($sqliteCheck->output());
                $this->info("   ✅ sqlite3 найден: {$sqlitePath}");
            } else {
                $this->warn('   ⚠️  sqlite3 не найден (будет использован PHP метод)');
            }
        } else {
            $this->warn("   ⚠️  Неподдерживаемый тип БД: {$connection}");
        }
        $this->newLine();

        // Проверка 3: Конфигурация
        $this->info('3️⃣ Проверка конфигурации...');
        $serverUrl = env('SERVER_URL');
        $deployToken = env('DEPLOY_TOKEN');

        if ($serverUrl) {
            $this->info("   ✅ SERVER_URL настроен: {$serverUrl}");
        } else {
            $this->error('   ❌ SERVER_URL не настроен в .env');
            $allOk = false;
        }

        if ($deployToken) {
            $tokenLength = strlen($deployToken);
            $this->info("   ✅ DEPLOY_TOKEN настроен (длина: {$tokenLength} символов)");
        } else {
            $this->error('   ❌ DEPLOY_TOKEN не настроен в .env');
            $allOk = false;
        }
        $this->newLine();

        // Проверка 4: Права доступа
        $this->info('4️⃣ Проверка прав доступа...');
        $uploadDir = public_path('upload');
        $tempDir = storage_path('app/temp');

        if (is_dir($uploadDir)) {
            if (is_writable($uploadDir)) {
                $this->info("   ✅ Директория upload доступна для записи: {$uploadDir}");
            } else {
                $this->error("   ❌ Директория upload НЕ доступна для записи: {$uploadDir}");
                $this->line("   💡 Выполните: chmod -R 755 {$uploadDir}");
                $allOk = false;
            }
        } else {
            $this->warn("   ⚠️  Директория upload не существует: {$uploadDir}");
            $this->line("   💡 Будет создана автоматически при синхронизации");
        }

        if (!is_dir($tempDir)) {
            $tempParent = dirname($tempDir);
            if (is_writable($tempParent)) {
                $this->info("   ✅ Директория для временных файлов доступна: {$tempParent}");
            } else {
                $this->error("   ❌ Директория для временных файлов НЕ доступна: {$tempParent}");
                $this->line("   💡 Выполните: chmod -R 755 {$tempParent}");
                $allOk = false;
            }
        } else {
            if (is_writable($tempDir)) {
                $this->info("   ✅ Директория temp доступна для записи: {$tempDir}");
            } else {
                $this->error("   ❌ Директория temp НЕ доступна для записи: {$tempDir}");
                $this->line("   💡 Выполните: chmod -R 755 {$tempDir}");
                $allOk = false;
            }
        }
        $this->newLine();

        // Итоговый результат
        $this->newLine();
        if ($allOk) {
            $this->info('✅ Все требования выполнены! Команда sync-sql-file готова к использованию.');
        } else {
            $this->error('❌ Некоторые требования не выполнены. Исправьте ошибки выше.');
            return 1;
        }

        return 0;
    }
}

