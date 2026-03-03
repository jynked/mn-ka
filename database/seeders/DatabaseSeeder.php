<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            MenuSeeder::class,
            AppCategorySeeder::class, // Категории заявителя
            QuizImagesSeeder::class,
            QuizSeeder::class,
            HowWorkBlockSettingsSeeder::class,
            CasesSeeder::class,
            FaqBlockSettingsSeeder::class,
            WhyChooseUsBlockSettingsSeeder::class,
            CasesBlockSettingsSeeder::class,
            HomePageBlocksSeeder::class, // Блоки главной страницы
            CopyMediaFilesSeeder::class,
            ProductsServicesOptionsCasesSeeder::class,
            ImportProductsServicesSeeder::class, // Импорт данных продуктов, сервисов и баннеров из JSON
            AboutSettingsSeeder::class,
            ContactSettingsSeeder::class,
            FooterSettingsSeeder::class,
            RegisterAllMediaFilesSeeder::class, // Регистрируем все медиа файлы в таблицу media
            UpdateMediaFolderSeeder::class, // Обновляем папки для всех медиа файлов
        ]);
    }
}
