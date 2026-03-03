<?php

namespace Database\Seeders;

use Database\Seeders\Traits\MediaRegistrationTrait;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

/**
 * Seeder для регистрации всех изображений, используемых в seeders, в таблицу media
 */
class RegisterAllMediaFilesSeeder extends Seeder
{
    use MediaRegistrationTrait;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Начало регистрации всех медиа файлов...');

        $totalRegistered = 0;

        // Регистрируем файлы из различных директорий
        $directories = [
            'img/system' => 'system',
            'img/team' => 'team',
            'img/delete' => 'delete',
            'upload/icons' => 'icons',
            'upload/products' => 'products',
            'upload/services' => 'services',
            'upload/cases' => 'cases',
            'upload/how-work' => 'how-work',
            'upload/quiz' => 'quiz',
        ];

        foreach ($directories as $directory => $category) {
            $count = $this->registerMediaFromDirectory($directory, $category);
            $totalRegistered += $count;
            
            if ($count > 0) {
                $this->command->info("✓ Зарегистрировано в {$directory}: {$count} файлов");
            }
        }

        // Регистрируем файлы, используемые в настройках
        $this->registerSettingsMedia();

        // Регистрируем файлы из CopyMediaFilesSeeder (если они были скопированы, но не зарегистрированы)
        $this->registerCopiedMediaFiles();

        $this->command->info("✅ Всего зарегистрировано медиа файлов: {$totalRegistered}");
        $this->command->info('✅ Регистрация медиа файлов завершена!');
    }

    /**
     * Регистрировать медиа файлы из настроек
     */
    protected function registerSettingsMedia(): void
    {
        // Проверяем HowWorkBlockSettings
        $this->registerHowWorkMedia();
        
        // Проверяем AboutSettings
        $this->registerAboutSettingsMedia();
        
        // Проверяем другие настройки, которые могут содержать пути к изображениям
    }

    /**
     * Регистрировать медиа из HowWorkBlockSettings
     */
    protected function registerHowWorkMedia(): void
    {
        try {
            $settings = DB::table('how_work_block_settings')->first();
            if ($settings && !empty($settings->image)) {
                $this->registerMediaByPath($settings->image, 'how-work');
            }
        } catch (\Exception $e) {
            // Таблица может не существовать
        }
    }

    /**
     * Регистрировать медиа из AboutSettings
     */
    protected function registerAboutSettingsMedia(): void
    {
        try {
            $settings = DB::table('about_settings')->first();
            if (!$settings) {
                return;
            }

            // Баннер
            if (!empty($settings->banner_image)) {
                $this->registerMediaByPath($settings->banner_image, 'about');
            }

            // Статистика
            if (!empty($settings->statistics)) {
                $statistics = json_decode($settings->statistics, true);
                if (is_array($statistics)) {
                    foreach ($statistics as $stat) {
                        if (!empty($stat['icon'])) {
                            $this->registerMediaByPath($stat['icon'], 'about');
                        }
                    }
                }
            }

            // Клиенты
            if (!empty($settings->clients)) {
                $clients = json_decode($settings->clients, true);
                if (is_array($clients)) {
                    foreach ($clients as $client) {
                        if (!empty($client['icon'])) {
                            $this->registerMediaByPath($client['icon'], 'about');
                        }
                    }
                }
            }

            // Команда
            if (!empty($settings->team)) {
                $team = json_decode($settings->team, true);
                if (is_array($team)) {
                    foreach ($team as $member) {
                        if (!empty($member['photo'])) {
                            $this->registerMediaByPath($member['photo'], 'about');
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Таблица может не существовать
        }
    }

    /**
     * Регистрировать файлы, которые были скопированы, но не зарегистрированы в media
     */
    protected function registerCopiedMediaFiles(): void
    {
        $this->command->info('Проверка скопированных файлов...');
        
        $uploadDirs = [
            'upload/icons',
            'upload/products',
            'upload/services',
            'upload/cases',
            'upload/how-work',
            'upload/quiz',
            'upload/general',
        ];

        $count = 0;
        foreach ($uploadDirs as $dir) {
            $category = basename($dir);
            $registered = $this->registerMediaFromDirectory($dir, $category);
            $count += $registered;
        }

        if ($count > 0) {
            $this->command->info("✓ Зарегистрировано скопированных файлов: {$count}");
        }
    }
}





