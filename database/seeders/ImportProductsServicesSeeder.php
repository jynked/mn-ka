<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\Product;
use App\Models\Service;
use App\Models\Option;
use App\Models\OptionTree;
use App\Models\Instance;
use App\Models\Chapter;
use App\Models\Media;
use App\Models\Banner;
use Illuminate\Support\Facades\Log;

class ImportProductsServicesSeeder extends Seeder
{
    protected $importFile;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Определяем файл для импорта
        // Можно указать через переменную окружения или использовать последний экспортированный файл
        $this->importFile = env('IMPORT_FILE') 
            ?: database_path('seeders/exports/products_services_export.json');

        // Если файл не указан или не существует, ищем последний экспортированный файл
        if (!File::exists($this->importFile)) {
            $exportPath = database_path('seeders/exports');
            if (File::exists($exportPath)) {
                $files = File::glob($exportPath . '/products_services_export_*.json');
                if (!empty($files)) {
                    // Сортируем по времени модификации и берем последний
                    usort($files, function($a, $b) {
                        $timeA = @filemtime($a) ?: 0;
                        $timeB = @filemtime($b) ?: 0;
                        // Если время одинаковое, сортируем по имени (более новый по дате в имени)
                        if ($timeA === $timeB) {
                            return strcmp($b, $a); // Обратная сортировка по имени
                        }
                        return $timeB - $timeA;
                    });
                    $this->importFile = $files[0];
                    $this->command->info("📁 Автоматически выбран файл: " . basename($this->importFile));
                } else {
                    $this->command->warn('⚠️ Файл импорта не найден и нет экспортированных файлов');
                    $this->command->info('💡 Пропускаем импорт данных. Для импорта выполните: php artisan db:seed --class=ExportProductsServicesSeeder');
                    return;
                }
            } else {
                $this->command->warn('⚠️ Директория exports не найдена: ' . $exportPath);
                $this->command->info('💡 Пропускаем импорт данных. Для импорта выполните: php artisan db:seed --class=ExportProductsServicesSeeder');
                return;
            }
        } else {
            $this->command->info("📁 Используется указанный файл: " . basename($this->importFile));
        }

        $this->command->info('🚀 Начало импорта данных из файла: ' . $this->importFile);

        try {
            $importData = json_decode(File::get($this->importFile), true);

            if (!$importData) {
                throw new \Exception('Не удалось прочитать JSON файл');
            }

            $this->command->info("📊 Версия экспорта: " . ($importData['version'] ?? 'неизвестна'));
            $this->command->info("📅 Дата экспорта: " . ($importData['exported_at'] ?? 'неизвестна'));
            
            // Проверяем наличие баннеров в файле
            $bannersCount = count($importData['banners'] ?? []);
            if ($bannersCount > 0) {
                $this->command->info("🎨 Баннеров в файле: {$bannersCount}");
            } else {
                $this->command->warn("⚠️ Баннеры не найдены в файле экспорта");
            }

            // Импортируем данные в правильном порядке
            $this->importChapters($importData['chapters'] ?? []);
            $this->importOptions($importData['options'] ?? []);
            $this->importOptionTrees($importData['option_trees'] ?? []);
            $this->importInstances($importData['instances'] ?? []);
            $this->importMediaFiles($importData);
            $this->importServices($importData['services'] ?? []);
            $this->importProducts($importData['products'] ?? []);
            $this->importBanners($importData['banners'] ?? []);
            $this->importRelations($importData['relations'] ?? []);

            $this->command->info('✅ Импорт завершен успешно!');
        } catch (\Exception $e) {
            $this->command->error('❌ Ошибка импорта: ' . $e->getMessage());
            Log::error('Import seeder error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Импорт разделов
     */
    protected function importChapters(array $chapters): void
    {
        $this->command->info('📁 Импорт разделов...');
        
        $imported = 0;
        foreach ($chapters as $chapterData) {
            Chapter::updateOrCreate(
                ['id' => $chapterData['id']],
                [
                    'name' => $chapterData['name'],
                    'order' => $chapterData['order'] ?? 0,
                    'is_active' => $chapterData['is_active'] ?? true,
                ]
            );
            $imported++;
        }

        $this->command->info("✅ Импортировано разделов: {$imported}");
    }

    /**
     * Импорт опций
     */
    protected function importOptions(array $options): void
    {
        $this->command->info('📋 Импорт опций...');
        
        $imported = 0;
        foreach ($options as $optionData) {
            Option::updateOrCreate(
                ['id' => $optionData['id']],
                [
                    'name' => $optionData['name'],
                    'order' => $optionData['order'] ?? 0,
                    'is_active' => $optionData['is_active'] ?? true,
                ]
            );
            $imported++;
        }

        $this->command->info("✅ Импортировано опций: {$imported}");
    }

    /**
     * Импорт деревьев опций
     */
    protected function importOptionTrees(array $trees): void
    {
        $this->command->info('🌳 Импорт деревьев опций...');
        
        $imported = 0;
        
        // Сначала импортируем корневые элементы (parent = 0 или null)
        $rootTrees = array_filter($trees, function($tree) {
            return empty($tree['parent']) || $tree['parent'] == 0;
        });
        
        foreach ($rootTrees as $treeData) {
            $this->importOptionTree($treeData);
            $imported++;
        }

        // Затем импортируем дочерние элементы
        $childTrees = array_filter($trees, function($tree) {
            return !empty($tree['parent']) && $tree['parent'] != 0;
        });
        
        foreach ($childTrees as $treeData) {
            $this->importOptionTree($treeData);
            $imported++;
        }

        $this->command->info("✅ Импортировано деревьев опций: {$imported}");
    }

    /**
     * Импорт одного дерева опций
     */
    protected function importOptionTree(array $treeData): void
    {
        OptionTree::updateOrCreate(
            ['id' => $treeData['id']],
            [
                'name' => $treeData['name'],
                'parent' => $treeData['parent'] ?? 0,
                'sort' => $treeData['sort'] ?? 0,
                'is_active' => $treeData['is_active'] ?? true,
            ]
        );
    }

    /**
     * Импорт экземпляров
     */
    protected function importInstances(array $instances): void
    {
        $this->command->info('📦 Импорт экземпляров...');
        
        $imported = 0;
        foreach ($instances as $instanceData) {
            Instance::updateOrCreate(
                ['id' => $instanceData['id']],
                [
                    'name' => $instanceData['name'],
                    'order' => $instanceData['order'] ?? 0,
                    'is_active' => $instanceData['is_active'] ?? true,
                ]
            );
            $imported++;
        }

        $this->command->info("✅ Импортировано экземпляров: {$imported}");
    }

    /**
     * Импорт медиа файлов из экспортированных данных
     */
    protected function importMediaFiles(array $importData): void
    {
        $this->command->info('🖼️ Импорт информации о медиа файлах...');
        
        $mediaIds = [];
        
        // Собираем все ID медиа файлов из услуг
        foreach ($importData['services'] ?? [] as $service) {
            if (!empty($service['image_id'])) {
                $mediaIds[] = $service['image_id'];
            }
            if (!empty($service['icon_id'])) {
                $mediaIds[] = $service['icon_id'];
            }
        }
        
        // Собираем все ID медиа файлов из продуктов
        foreach ($importData['products'] ?? [] as $product) {
            if (!empty($product['image_id'])) {
                $mediaIds[] = $product['image_id'];
            }
            if (!empty($product['icon_id'])) {
                $mediaIds[] = $product['icon_id'];
            }
        }
        
        $mediaIds = array_unique($mediaIds);
        $imported = 0;
        
        // Импортируем медиа файлы из услуг
        foreach ($importData['services'] ?? [] as $service) {
            if (!empty($service['image'])) {
                $this->importMedia($service['image']);
                $imported++;
            }
            if (!empty($service['icon'])) {
                $this->importMedia($service['icon']);
                $imported++;
            }
        }
        
        // Импортируем медиа файлы из продуктов
        foreach ($importData['products'] ?? [] as $product) {
            if (!empty($product['image'])) {
                $this->importMedia($product['image']);
                $imported++;
            }
            if (!empty($product['icon'])) {
                $this->importMedia($product['icon']);
                $imported++;
            }
        }
        
        $this->command->info("✅ Импортировано медиа файлов: {$imported}");
    }

    /**
     * Импорт одного медиа файла
     */
    protected function importMedia(array $mediaData): void
    {
        // Проверяем, существует ли уже медиа файл
        $existing = Media::find($mediaData['id']);
        if ($existing) {
            return;
        }

        Media::create([
            'id' => $mediaData['id'],
            'name' => $mediaData['name'],
            'original_name' => $mediaData['original_name'] ?? $mediaData['name'],
            'extension' => $mediaData['extension'] ?? pathinfo($mediaData['name'], PATHINFO_EXTENSION),
            'disk' => $mediaData['disk'],
            'width' => $mediaData['width'] ?? null,
            'height' => $mediaData['height'] ?? null,
            'type' => $mediaData['type'] ?? 'photo',
            'size' => $mediaData['size'] ?? null,
            'metadata' => $mediaData['metadata'] ?? null,
            'folder_id' => null,
            'user_id' => null,
            'temporary' => false,
        ]);
    }

    /**
     * Импорт услуг
     */
    protected function importServices(array $services): void
    {
        $this->command->info('💼 Импорт услуг...');
        
        $imported = 0;
        foreach ($services as $serviceData) {
            Service::updateOrCreate(
                ['id' => $serviceData['id']],
                [
                    'name' => $serviceData['name'],
                    'slug' => $serviceData['slug'],
                    'description' => $serviceData['description'] ?? null,
                    'image_id' => $serviceData['image_id'] ?? null,
                    'icon_id' => $serviceData['icon_id'] ?? null,
                    'chapter_id' => $serviceData['chapter_id'] ?? null,
                    'order' => $serviceData['order'] ?? 0,
                    'is_active' => $serviceData['is_active'] ?? true,
                ]
            );
            $imported++;
        }

        $this->command->info("✅ Импортировано услуг: {$imported}");
    }

    /**
     * Импорт продуктов
     */
    protected function importProducts(array $products): void
    {
        $this->command->info('🛍️ Импорт продуктов...');
        
        $imported = 0;
        foreach ($products as $productData) {
            Product::updateOrCreate(
                ['id' => $productData['id']],
                [
                    'name' => $productData['name'],
                    'slug' => $productData['slug'],
                    'description' => $productData['description'] ?? null,
                    'image_id' => $productData['image_id'] ?? null,
                    'icon_id' => $productData['icon_id'] ?? null,
                    'chapter_id' => $productData['chapter_id'] ?? null,
                    'order' => $productData['order'] ?? 0,
                    'is_active' => $productData['is_active'] ?? true,
                ]
            );
            $imported++;
        }

        $this->command->info("✅ Импортировано продуктов: {$imported}");
    }

    /**
     * Импорт баннеров
     */
    protected function importBanners(array $banners): void
    {
        $this->command->info('🎨 Импорт баннеров...');
        
        if (empty($banners)) {
            $this->command->warn('⚠️ Баннеры не найдены в файле экспорта');
            return;
        }
        
        $imported = 0;
        foreach ($banners as $bannerData) {
            try {
                // Используем slug для поиска, так как он уникален и более надежен
                $banner = Banner::updateOrCreate(
                    ['slug' => $bannerData['slug']],
                    [
                        'title' => $bannerData['title'],
                        'background_image' => $bannerData['background_image'] ?? null,
                        'heading_1' => $bannerData['heading_1'] ?? null,
                        'heading_2' => $bannerData['heading_2'] ?? null,
                        'description' => $bannerData['description'] ?? null,
                        'button_text' => $bannerData['button_text'] ?? null,
                        'button_type' => $bannerData['button_type'] ?? 'url',
                        'button_value' => $bannerData['button_value'] ?? null,
                        'height_desktop' => $bannerData['height_desktop'] ?? null,
                        'height_mobile' => $bannerData['height_mobile'] ?? null,
                        'is_active' => $bannerData['is_active'] ?? true,
                        'order' => $bannerData['order'] ?? 0,
                    ]
                );
                
                // Если ID был указан и отличается, обновляем его (для совместимости)
                if (isset($bannerData['id']) && $banner->id != $bannerData['id']) {
                    // Не обновляем ID, так как это может вызвать проблемы
                    // Просто логируем
                    $this->command->warn("⚠️ ID баннера отличается: ожидался {$bannerData['id']}, получен {$banner->id}");
                }
                
                $imported++;
                $this->command->line("  ✅ Баннер импортирован: {$banner->title} (slug: {$banner->slug})");
            } catch (\Exception $e) {
                $this->command->error("  ❌ Ошибка импорта баннера: " . $e->getMessage());
                Log::error('Ошибка импорта баннера', [
                    'banner_data' => $bannerData,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->command->info("✅ Импортировано баннеров: {$imported}");
    }

    /**
     * Импорт связей
     */
    protected function importRelations(array $relations): void
    {
        $this->command->info('🔗 Импорт связей...');
        
        // Импорт связей product_service
        if (!empty($relations['product_service'])) {
            DB::table('product_service')->truncate();
            foreach ($relations['product_service'] as $relation) {
                DB::table('product_service')->insert([
                    'product_id' => $relation['product_id'],
                    'service_id' => $relation['service_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->command->info("✅ Импортировано связей product_service: " . count($relations['product_service']));
        }

        // Импорт связей option_service
        if (!empty($relations['option_service'])) {
            DB::table('option_service')->truncate();
            foreach ($relations['option_service'] as $relation) {
                DB::table('option_service')->insert([
                    'option_id' => $relation['option_id'],
                    'service_id' => $relation['service_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->command->info("✅ Импортировано связей option_service: " . count($relations['option_service']));
        }

        // Импорт связей option_tree_service
        if (!empty($relations['option_tree_service'])) {
            DB::table('option_tree_service')->truncate();
            foreach ($relations['option_tree_service'] as $relation) {
                DB::table('option_tree_service')->insert([
                    'option_tree_id' => $relation['option_tree_id'],
                    'service_id' => $relation['service_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->command->info("✅ Импортировано связей option_tree_service: " . count($relations['option_tree_service']));
        }

        // Импорт связей instance_service
        if (!empty($relations['instance_service'])) {
            DB::table('instance_service')->truncate();
            foreach ($relations['instance_service'] as $relation) {
                DB::table('instance_service')->insert([
                    'instance_id' => $relation['instance_id'],
                    'service_id' => $relation['service_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->command->info("✅ Импортировано связей instance_service: " . count($relations['instance_service']));
        }
    }
}
