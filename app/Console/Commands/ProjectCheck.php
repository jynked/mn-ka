<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ProjectCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверка проекта перед переносом на сервер';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Проверка проекта перед переносом на сервер...');
        $this->newLine();

        $errors = 0;
        $warnings = 0;

        // 1. Проверка миграций
        $this->info('📋 1. Проверка миграций...');
        $migrationResult = $this->checkMigrations();
        if ($migrationResult['errors'] > 0) {
            $errors += $migrationResult['errors'];
        }
        if ($migrationResult['warnings'] > 0) {
            $warnings += $migrationResult['warnings'];
        }
        $this->newLine();

        // 2. Проверка Artisan команд
        $this->info('⚙️  2. Проверка Artisan команд...');
        $commandResult = $this->checkCommands();
        if ($commandResult['errors'] > 0) {
            $errors += $commandResult['errors'];
        }
        $this->newLine();

        // 3. Проверка конфигурационных файлов
        $this->info('📁 3. Проверка конфигурационных файлов...');
        $configResult = $this->checkConfigFiles();
        if ($configResult['errors'] > 0) {
            $errors += $configResult['errors'];
        }
        $this->newLine();

        // 4. Проверка SQL файла
        $this->info('💾 4. Проверка SQL файла...');
        $sqlResult = $this->checkSqlFile();
        if ($sqlResult['errors'] > 0) {
            $errors += $sqlResult['errors'];
        }
        if ($sqlResult['warnings'] > 0) {
            $warnings += $sqlResult['warnings'];
        }
        $this->newLine();

        // 5. Проверка структуры директорий
        $this->info('📂 5. Проверка структуры директорий...');
        $dirResult = $this->checkDirectories();
        if ($dirResult['errors'] > 0) {
            $errors += $dirResult['errors'];
        }
        $this->newLine();

        // Итоги
        $this->info('═══════════════════════════════════════════════════');
        if ($errors === 0 && $warnings === 0) {
            $this->info('✅ Все проверки пройдены успешно!');
            $this->newLine();
            $this->line('Проект готов к переносу на сервер.');
        } else {
            if ($errors > 0) {
                $this->error("❌ Найдено ошибок: {$errors}");
            }
            if ($warnings > 0) {
                $this->warn("⚠️  Найдено предупреждений: {$warnings}");
            }
        }
        $this->newLine();

        return $errors > 0 ? 1 : 0;
    }

    /**
     * Проверка миграций
     */
    protected function checkMigrations(): array
    {
        $errors = 0;
        $warnings = 0;
        $migrationsPath = database_path('migrations');

        if (!File::exists($migrationsPath)) {
            $this->error('   ❌ Директория миграций не найдена');
            return ['errors' => 1, 'warnings' => 0];
        }

        $migrations = File::files($migrationsPath);
        $this->line("   Найдено миграций: " . count($migrations));

        // Проверка критичных миграций
        $criticalMigrations = [
            'create_users_table',
            'create_media_table',
            'create_products_table',
            'create_services_table',
            'create_cases_table',
        ];

        foreach ($criticalMigrations as $critical) {
            $found = false;
            foreach ($migrations as $migration) {
                if (str_contains($migration->getFilename(), $critical)) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $this->warn("   ⚠️  Не найдена критичная миграция: {$critical}");
                $warnings++;
            }
        }

        // Проверка исправленной миграции
        $fixedMigration = '2025_11_08_171010_add_protected_to_folders_table.php';
        if (File::exists($migrationsPath . '/' . $fixedMigration)) {
            $content = File::get($migrationsPath . '/' . $fixedMigration);
            if (str_contains($content, 'Schema::hasColumn')) {
                $this->line("   ✅ Миграция add_protected_to_folders_table исправлена");
            } else {
                $this->warn("   ⚠️  Миграция add_protected_to_folders_table не содержит проверку hasColumn");
                $warnings++;
            }
        }

        return ['errors' => $errors, 'warnings' => $warnings];
    }

    /**
     * Проверка Artisan команд
     */
    protected function checkCommands(): array
    {
        $errors = 0;
        $requiredCommands = [
            'ProjectInstall' => 'app/Console/Commands/ProjectInstall.php',
            'ImportSqlFile' => 'app/Console/Commands/ImportSqlFile.php',
            'CreateUser' => 'app/Console/Commands/CreateUser.php',
        ];

        foreach ($requiredCommands as $name => $path) {
            $fullPath = base_path($path);
            if (File::exists($fullPath)) {
                $this->line("   ✅ Команда {$name} найдена");
            } else {
                $this->error("   ❌ Команда {$name} не найдена: {$path}");
                $errors++;
            }
        }

        return ['errors' => $errors, 'warnings' => 0];
    }

    /**
     * Проверка конфигурационных файлов
     */
    protected function checkConfigFiles(): array
    {
        $errors = 0;
        $requiredFiles = [
            '.env.example' => 'Шаблон переменных окружения',
            'composer.json' => 'Зависимости PHP',
            'package.json' => 'Зависимости Node.js',
            'artisan' => 'Файл Artisan',
        ];

        foreach ($requiredFiles as $file => $description) {
            if (File::exists(base_path($file))) {
                $this->line("   ✅ {$file} ({$description})");
            } else {
                $this->error("   ❌ Не найден: {$file} ({$description})");
                $errors++;
            }
        }

        return ['errors' => $errors, 'warnings' => 0];
    }

    /**
     * Проверка SQL файла
     */
    protected function checkSqlFile(): array
    {
        $errors = 0;
        $warnings = 0;
        $sqlFile = 'dsc23ytp_lag_crm.sql';

        // Проверяем в нескольких местах
        $possiblePaths = [
            base_path($sqlFile),
            base_path('../' . $sqlFile),
            storage_path($sqlFile),
        ];

        $found = false;
        foreach ($possiblePaths as $path) {
            if (File::exists($path)) {
                $found = true;
                $size = File::size($path);
                $sizeMB = round($size / 1024 / 1024, 2);
                $this->line("   ✅ SQL файл найден: {$path}");
                $this->line("      Размер: {$sizeMB} MB");

                // Проверка содержимого
                $content = File::get($path);
                if (str_contains($content, 'CREATE TABLE')) {
                    $tableCount = substr_count($content, 'CREATE TABLE');
                    $this->line("      Найдено таблиц: {$tableCount}");
                }
                if (str_contains($content, 'INSERT INTO')) {
                    $insertCount = substr_count($content, 'INSERT INTO');
                    $this->line("      Найдено INSERT запросов: {$insertCount}");
                }
                
                // Предупреждение о foreign keys
                if (str_contains($content, 'FOREIGN KEY')) {
                    $fkCount = substr_count($content, 'FOREIGN KEY');
                    $this->warn("      ⚠️  Найдено FOREIGN KEY: {$fkCount} (будут пропущены при импорте с --skip-fk)");
                    $warnings++;
                }

                break;
            }
        }

        if (!$found) {
            $this->warn("   ⚠️  SQL файл не найден: {$sqlFile}");
            $this->warn("      Убедитесь, что файл находится в корне проекта или укажите путь при установке");
            $warnings++;
        }

        return ['errors' => $errors, 'warnings' => $warnings];
    }

    /**
     * Проверка структуры директорий
     */
    protected function checkDirectories(): array
    {
        $errors = 0;
        $requiredDirs = [
            'app' => 'Код приложения',
            'database/migrations' => 'Миграции',
            'database/seeders' => 'Seeders',
            'storage' => 'Хранилище',
            'storage/logs' => 'Логи',
            'bootstrap/cache' => 'Кеш бутстрап',
            'public' => 'Публичная директория',
            'config' => 'Конфигурация',
        ];

        foreach ($requiredDirs as $dir => $description) {
            $path = base_path($dir);
            if (File::isDirectory($path)) {
                $this->line("   ✅ {$dir}/ ({$description})");
            } else {
                $this->error("   ❌ Директория не найдена: {$dir}/ ({$description})");
                $errors++;
            }
        }

        return ['errors' => $errors, 'warnings' => 0];
    }
}

