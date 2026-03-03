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

class ExportProductsServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Начало экспорта данных продуктов и сервисов...');

        try {
            $exportData = [
                'exported_at' => now()->toDateTimeString(),
                'version' => '1.0',
                'chapters' => $this->exportChapters(),
                'options' => $this->exportOptions(),
                'option_trees' => $this->exportOptionTrees(),
                'instances' => $this->exportInstances(),
                'services' => $this->exportServices(),
                'products' => $this->exportProducts(),
                'banners' => $this->exportBanners(),
                'relations' => $this->exportRelations(),
            ];

            // Сохраняем в JSON файл
            $exportPath = database_path('seeders/exports');
            if (!File::exists($exportPath)) {
                File::makeDirectory($exportPath, 0755, true);
            }

            $filename = $exportPath . '/products_services_export_' . date('Y-m-d_His') . '.json';
            File::put($filename, json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $this->command->info("✅ Экспорт завершен успешно!");
            $this->command->info("📁 Файл сохранен: {$filename}");
            $this->command->info("📊 Статистика:");
            $this->command->info("   - Разделов: " . count($exportData['chapters']));
            $this->command->info("   - Опций: " . count($exportData['options']));
            $this->command->info("   - Деревьев опций: " . count($exportData['option_trees']));
            $this->command->info("   - Экземпляров: " . count($exportData['instances']));
            $this->command->info("   - Услуг: " . count($exportData['services']));
            $this->command->info("   - Продуктов: " . count($exportData['products']));
            $this->command->info("   - Баннеров: " . count($exportData['banners']));

        } catch (\Exception $e) {
            $this->command->error('❌ Ошибка экспорта: ' . $e->getMessage());
            Log::error('Export seeder error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Экспорт разделов
     */
    protected function exportChapters(): array
    {
        $this->command->info('📁 Экспорт разделов...');
        
        $chapters = Chapter::all();
        $data = [];

        foreach ($chapters as $chapter) {
            $data[] = [
                'id' => $chapter->id,
                'name' => $chapter->name,
                'order' => $chapter->order,
                'is_active' => $chapter->is_active,
                'created_at' => $chapter->created_at?->toDateTimeString(),
                'updated_at' => $chapter->updated_at?->toDateTimeString(),
            ];
        }

        $this->command->info("✅ Экспортировано разделов: " . count($data));
        return $data;
    }

    /**
     * Экспорт опций
     */
    protected function exportOptions(): array
    {
        $this->command->info('📋 Экспорт опций...');
        
        $options = Option::all();
        $data = [];

        foreach ($options as $option) {
            $data[] = [
                'id' => $option->id,
                'name' => $option->name,
                'order' => $option->order,
                'is_active' => $option->is_active,
                'created_at' => $option->created_at?->toDateTimeString(),
                'updated_at' => $option->updated_at?->toDateTimeString(),
            ];
        }

        $this->command->info("✅ Экспортировано опций: " . count($data));
        return $data;
    }

    /**
     * Экспорт деревьев опций
     */
    protected function exportOptionTrees(): array
    {
        $this->command->info('🌳 Экспорт деревьев опций...');
        
        $trees = OptionTree::with('items')->get();
        $data = [];

        foreach ($trees as $tree) {
            $treeData = [
                'id' => $tree->id,
                'name' => $tree->name,
                'parent' => $tree->parent,
                'sort' => $tree->sort,
                'is_active' => $tree->is_active,
                'created_at' => $tree->created_at?->toDateTimeString(),
                'updated_at' => $tree->updated_at?->toDateTimeString(),
            ];

            // Экспортируем дочерние элементы
            if ($tree->items && $tree->items->count() > 0) {
                $treeData['items'] = [];
                foreach ($tree->items as $item) {
                    $treeData['items'][] = [
                        'id' => $item->id,
                        'name' => $item->name,
                        'parent' => $item->parent,
                        'sort' => $item->sort,
                        'is_active' => $item->is_active,
                    ];
                }
            }

            $data[] = $treeData;
        }

        $this->command->info("✅ Экспортировано деревьев опций: " . count($data));
        return $data;
    }

    /**
     * Экспорт экземпляров
     */
    protected function exportInstances(): array
    {
        $this->command->info('📦 Экспорт экземпляров...');
        
        $instances = Instance::all();
        $data = [];

        foreach ($instances as $instance) {
            $data[] = [
                'id' => $instance->id,
                'name' => $instance->name,
                'order' => $instance->order,
                'is_active' => $instance->is_active,
                'created_at' => $instance->created_at?->toDateTimeString(),
                'updated_at' => $instance->updated_at?->toDateTimeString(),
            ];
        }

        $this->command->info("✅ Экспортировано экземпляров: " . count($data));
        return $data;
    }

    /**
     * Экспорт услуг
     */
    protected function exportServices(): array
    {
        $this->command->info('💼 Экспорт услуг...');
        
        $services = Service::with(['image', 'icon', 'chapter'])->get();
        $data = [];

        foreach ($services as $service) {
            $serviceData = [
                'id' => $service->id,
                'name' => $service->name,
                'slug' => $service->slug,
                'description' => $service->description,
                'image_id' => $service->image_id,
                'icon_id' => $service->icon_id,
                'chapter_id' => $service->chapter_id,
                'order' => $service->order,
                'is_active' => $service->is_active,
                'created_at' => $service->created_at?->toDateTimeString(),
                'updated_at' => $service->updated_at?->toDateTimeString(),
            ];

            // Добавляем информацию о медиа файлах
            if ($service->image) {
                $serviceData['image'] = $this->exportMedia($service->image);
            }
            if ($service->icon) {
                $serviceData['icon'] = $this->exportMedia($service->icon);
            }

            $data[] = $serviceData;
        }

        $this->command->info("✅ Экспортировано услуг: " . count($data));
        return $data;
    }

    /**
     * Экспорт продуктов
     */
    protected function exportProducts(): array
    {
        $this->command->info('🛍️ Экспорт продуктов...');
        
        $products = Product::with(['image', 'icon', 'chapter'])->get();
        $data = [];

        foreach ($products as $product) {
            $productData = [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'image_id' => $product->image_id,
                'icon_id' => $product->icon_id,
                'chapter_id' => $product->chapter_id,
                'order' => $product->order,
                'is_active' => $product->is_active,
                'created_at' => $product->created_at?->toDateTimeString(),
                'updated_at' => $product->updated_at?->toDateTimeString(),
            ];

            // Добавляем информацию о медиа файлах
            if ($product->image) {
                $productData['image'] = $this->exportMedia($product->image);
            }
            if ($product->icon) {
                $productData['icon'] = $this->exportMedia($product->icon);
            }

            $data[] = $productData;
        }

        $this->command->info("✅ Экспортировано продуктов: " . count($data));
        return $data;
    }

    /**
     * Экспорт баннеров
     */
    protected function exportBanners(): array
    {
        $this->command->info('🎨 Экспорт баннеров...');
        
        $banners = Banner::all();
        $data = [];

        foreach ($banners as $banner) {
            $bannerData = [
                'id' => $banner->id,
                'title' => $banner->title,
                'slug' => $banner->slug,
                'background_image' => $banner->background_image,
                'heading_1' => $banner->heading_1,
                'heading_2' => $banner->heading_2,
                'description' => $banner->description,
                'button_text' => $banner->button_text,
                'button_type' => $banner->button_type,
                'button_value' => $banner->button_value,
                'height_desktop' => $banner->height_desktop,
                'height_mobile' => $banner->height_mobile,
                'is_active' => $banner->is_active,
                'order' => $banner->order,
                'created_at' => $banner->created_at?->toDateTimeString(),
                'updated_at' => $banner->updated_at?->toDateTimeString(),
            ];

            $data[] = $bannerData;
        }

        $this->command->info("✅ Экспортировано баннеров: " . count($data));
        return $data;
    }

    /**
     * Экспорт связей между таблицами
     */
    protected function exportRelations(): array
    {
        $this->command->info('🔗 Экспорт связей...');
        
        $relations = [
            'product_service' => DB::table('product_service')->get()->map(function ($row) {
                return [
                    'product_id' => $row->product_id,
                    'service_id' => $row->service_id,
                ];
            })->toArray(),
            
            'option_service' => DB::table('option_service')->get()->map(function ($row) {
                return [
                    'option_id' => $row->option_id,
                    'service_id' => $row->service_id,
                ];
            })->toArray(),
            
            'option_tree_service' => DB::table('option_tree_service')->get()->map(function ($row) {
                return [
                    'option_tree_id' => $row->option_tree_id,
                    'service_id' => $row->service_id,
                ];
            })->toArray(),
            
            'instance_service' => DB::table('instance_service')->get()->map(function ($row) {
                return [
                    'instance_id' => $row->instance_id,
                    'service_id' => $row->service_id,
                ];
            })->toArray(),
        ];

        $this->command->info("✅ Экспортировано связей:");
        $this->command->info("   - product_service: " . count($relations['product_service']));
        $this->command->info("   - option_service: " . count($relations['option_service']));
        $this->command->info("   - option_tree_service: " . count($relations['option_tree_service']));
        $this->command->info("   - instance_service: " . count($relations['instance_service']));

        return $relations;
    }

    /**
     * Экспорт информации о медиа файле
     */
    protected function exportMedia(Media $media): array
    {
        return [
            'id' => $media->id,
            'name' => $media->name,
            'original_name' => $media->original_name,
            'extension' => $media->extension,
            'disk' => $media->disk,
            'width' => $media->width,
            'height' => $media->height,
            'type' => $media->type,
            'size' => $media->size,
            'metadata' => $media->metadata,
            'path' => $media->disk ? ($media->disk . '/' . $media->name) : null,
        ];
    }
}
