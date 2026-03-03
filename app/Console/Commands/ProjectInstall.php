<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProjectInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:install 
                            {--sql-file= : Путь к SQL файлу для импорта (например: dsc23ytp_lag_crm.sql)}
                            {--skip-migrate : Пропустить выполнение миграций}
                            {--skip-seed : Пропустить выполнение seeders}
                            {--skip-import : Пропустить импорт SQL файла}
                            {--skip-user : Пропустить создание пользователя}
                            {--force : Пропустить подтверждения}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Полная установка проекта: миграции, импорт SQL, seeders, создание пользователя';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Начало установки проекта...');
        $this->newLine();

        // Шаг 1: Миграции
        if (!$this->option('skip-migrate')) {
            $this->info('📋 Шаг 1: Выполнение миграций...');
            try {
                Artisan::call('migrate', ['--force' => true]);
                $this->info('✅ Миграции выполнены успешно');
            } catch (\Exception $e) {
                $this->error('❌ Ошибка при выполнении миграций: ' . $e->getMessage());
                if (!$this->option('force') && !$this->confirm('Продолжить установку?')) {
                    return 1;
                }
            }
            $this->newLine();
        }

        // Шаг 2: Импорт SQL файла
        if (!$this->option('skip-import')) {
            $sqlFile = $this->option('sql-file') ?: 'dsc23ytp_lag_crm.sql';
            
            // Проверяем файл в корне проекта и в текущей директории
            $sqlPath = null;
            if (File::exists($sqlFile)) {
                $sqlPath = $sqlFile;
            } elseif (File::exists(base_path($sqlFile))) {
                $sqlPath = base_path($sqlFile);
            } elseif (File::exists(storage_path($sqlFile))) {
                $sqlPath = storage_path($sqlFile);
            }

            if ($sqlPath && File::exists($sqlPath)) {
                $this->info("📥 Шаг 2: Импорт SQL файла: {$sqlPath}");
                
                if ($this->option('force') || $this->confirm('Импортировать SQL файл?', true)) {
                    try {
                        Artisan::call('db:import-sql', [
                            'file' => $sqlPath,
                            '--skip-fk' => true,
                        ]);
                        
                        $output = Artisan::output();
                        if (!empty($output)) {
                            $this->line($output);
                        }
                        
                        $this->info('✅ SQL файл импортирован успешно');
                    } catch (\Exception $e) {
                        $this->error('❌ Ошибка при импорте SQL: ' . $e->getMessage());
                        if (!$this->option('force') && !$this->confirm('Продолжить установку?')) {
                            return 1;
                        }
                    }
                }
            } else {
                $this->warn("⚠️  SQL файл не найден: {$sqlFile}");
                $this->warn("   Пропускаем импорт. Вы можете импортировать позже:");
                $this->warn("   php artisan db:import-sql путь/к/файлу.sql --skip-fk");
            }
            $this->newLine();
        }

        // Шаг 3: Seeders (опционально - могут конфликтовать с импортированными данными)
        if (!$this->option('skip-seed')) {
            $this->info('🌱 Шаг 3: Выполнение seeders...');
            
            if ($this->option('force') || $this->confirm('Выполнить seeders? (могут конфликтовать с импортированными данными)', false)) {
                try {
                    Artisan::call('db:seed', ['--force' => true]);
                    $this->info('✅ Seeders выполнены успешно');
                } catch (\Exception $e) {
                    $this->warn('⚠️  Ошибка при выполнении seeders: ' . $e->getMessage());
                    $this->warn('   Это нормально, если данные уже импортированы из SQL');
                }
            } else {
                $this->info('⏭️  Seeders пропущены');
            }
            $this->newLine();
        }

        // Шаг 4: Создание пользователя
        if (!$this->option('skip-user')) {
            $this->info('👤 Шаг 4: Создание администратора...');
            
            if ($this->option('force') || $this->confirm('Создать администратора?', true)) {
                try {
                    Artisan::call('user:create');
                    $output = Artisan::output();
                    if (!empty($output)) {
                        $this->line($output);
                    }
                    $this->info('✅ Пользователь создан успешно');
                } catch (\Exception $e) {
                    $this->error('❌ Ошибка при создании пользователя: ' . $e->getMessage());
                    $this->warn('   Вы можете создать пользователя позже: php artisan user:create');
                }
            }
            $this->newLine();
        }

        // Шаг 5: Очистка кеша
        $this->info('🧹 Очистка кеша...');
        try {
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            $this->info('✅ Кеш очищен');
        } catch (\Exception $e) {
            $this->warn('⚠️  Ошибка при очистке кеша: ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('🎉 Установка проекта завершена!');
        $this->newLine();
        
        $this->line('📝 Следующие шаги:');
        $this->line('   1. Проверьте настройки в .env файле');
        $this->line('   2. Проверьте права доступа: chmod -R 775 storage bootstrap/cache');
        $this->line('   3. Откройте сайт в браузере');
        $this->newLine();

        return 0;
    }
}

