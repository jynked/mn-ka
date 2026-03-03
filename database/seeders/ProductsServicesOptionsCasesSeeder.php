<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\Service;
use App\Models\Option;
use App\Models\OptionTree;
use App\Models\Instance;
use App\Models\ProjectCase;
use App\Models\Media;
use App\Models\Chapter;
use Illuminate\Support\Facades\Log;

class ProductsServicesOptionsCasesSeeder extends Seeder
{
    protected $oldProjectPath;
    protected $oldDbConnection;

    public function __construct()
    {
        // Путь к старому проекту
        $this->oldProjectPath = env('OLD_PROJECT_PATH', 'C:\OSPanel\domains\lagom');
        
        // Настройка подключения к старой БД
        $this->oldDbConnection = [
            'driver' => 'mysql',
            'host' => env('OLD_DB_HOST', '127.0.0.1'),
            'port' => env('OLD_DB_PORT', '3306'),
            'database' => env('OLD_DB_DATABASE', 'lagom'),
            'username' => env('OLD_DB_USERNAME', 'root'),
            'password' => env('OLD_DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Начало импорта данных из старого проекта...');

        try {
            // Подключаемся к старой БД (опционально)
            if (!$this->connectToOldDatabase()) {
                $this->command->info('ℹ️ Импорт из старой БД пропущен. Используйте локальные seeders для создания данных.');
                return;
            }

            // Импортируем данные в правильном порядке
            $this->importChapters();
            $this->importOptions();
            $this->importOptionTrees();
            $this->importInstances();
            $this->importServices();
            $this->importProducts();
            $this->importCases();

            $this->command->info('✅ Импорт данных завершен успешно!');
        } catch (\Exception $e) {
            $this->command->error('❌ Ошибка импорта: ' . $e->getMessage());
            Log::error('Seeder error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Не бросаем исключение, чтобы не прерывать другие seeders
            $this->command->warn('⚠️ Продолжаем выполнение других seeders...');
        }
    }

    /**
     * Подключение к старой базе данных
     */
    protected function connectToOldDatabase(): bool
    {
        try {
            config(['database.connections.old_db' => $this->oldDbConnection]);
            DB::purge('old_db');
            DB::reconnect('old_db');
            
            // Проверяем подключение
            DB::connection('old_db')->select('SELECT 1');
            $this->command->info('✅ Подключение к старой БД установлено');
            return true;
        } catch (\Exception $e) {
            $this->command->warn('⚠️ Не удалось подключиться к старой БД: ' . $e->getMessage());
            $this->command->info('ℹ️ Пропускаем импорт данных из старой БД. Используйте локальные seeders.');
            return false;
        }
    }

    /**
     * Импорт разделов (chapters)
     */
    protected function importChapters(): void
    {
        $this->command->info('📁 Импорт разделов...');
        
        try {
            $oldChapters = DB::connection('old_db')->table('chapters')->get();
            $imported = 0;

            foreach ($oldChapters as $oldChapter) {
            $chapterData = [
                'name' => $oldChapter->name ?? '',
                'order' => $oldChapter->order ?? 0,
                'is_active' => $oldChapter->is_active ?? true,
            ];
            
            // Добавляем поля только если они существуют в таблице
            if (Schema::hasColumn('chapters', 'slug')) {
                $chapterData['slug'] = $oldChapter->slug ?? Str::slug($oldChapter->name ?? '');
            }
            
            if (Schema::hasColumn('chapters', 'description')) {
                $chapterData['description'] = $oldChapter->description ?? null;
            }
            
            $chapter = Chapter::firstOrCreate(
                ['id' => $oldChapter->id],
                $chapterData
            );
                $imported++;
            }

            $this->command->info("✅ Импортировано разделов: {$imported}");
        } catch (\Exception $e) {
            $this->command->warn("⚠️ Ошибка импорта разделов: " . $e->getMessage());
        }
    }

    /**
     * Импорт опций
     */
    protected function importOptions(): void
    {
        $this->command->info('📋 Импорт опций...');
        
        try {
            $oldOptions = DB::connection('old_db')->table('options')->get();
            $imported = 0;

            foreach ($oldOptions as $oldOption) {
                Option::firstOrCreate(
                    ['id' => $oldOption->id],
                    [
                        'name' => $oldOption->name ?? '',
                        'order' => $oldOption->order ?? 0,
                        'is_active' => true,
                    ]
                );
                $imported++;
            }

            $this->command->info("✅ Импортировано опций: {$imported}");
        } catch (\Exception $e) {
            $this->command->warn("⚠️ Ошибка импорта опций: " . $e->getMessage());
        }
    }

    /**
     * Импорт деревьев опций
     */
    protected function importOptionTrees(): void
    {
        $this->command->info('🌳 Импорт деревьев опций...');
        
        try {
            $oldOptionTrees = DB::connection('old_db')->table('option_trees')->get();
            $imported = 0;

            foreach ($oldOptionTrees as $oldTree) {
                OptionTree::firstOrCreate(
                    ['id' => $oldTree->id],
                    [
                        'name' => $oldTree->name ?? '',
                        'parent' => $oldTree->parent ?? 0,
                        'sort' => $oldTree->sort ?? 0,
                        'is_active' => true,
                    ]
                );
                $imported++;
            }

            $this->command->info("✅ Импортировано деревьев опций: {$imported}");
        } catch (\Exception $e) {
            $this->command->warn("⚠️ Ошибка импорта деревьев опций: " . $e->getMessage());
        }
    }

    /**
     * Импорт экземпляров
     */
    protected function importInstances(): void
    {
        $this->command->info('📦 Импорт экземпляров...');
        
        try {
            $oldInstances = DB::connection('old_db')->table('instances')->get();
            $imported = 0;

            foreach ($oldInstances as $oldInstance) {
                Instance::firstOrCreate(
                    ['id' => $oldInstance->id],
                    [
                        'name' => $oldInstance->name ?? '',
                        'order' => $oldInstance->order ?? 0,
                        'is_active' => true,
                    ]
                );
                $imported++;
            }

            $this->command->info("✅ Импортировано экземпляров: {$imported}");
        } catch (\Exception $e) {
            $this->command->warn("⚠️ Ошибка импорта экземпляров: " . $e->getMessage());
        }
    }

    /**
     * Импорт услуг
     */
    protected function importServices(): void
    {
        $this->command->info('💼 Импорт услуг...');
        
        try {
            $oldServices = DB::connection('old_db')
                ->table('services')
                ->get();
            
            $imported = 0;
            $servicesMap = [];

            foreach ($oldServices as $oldService) {
                // Импортируем изображения
                $imageId = null;
                $iconId = null;

                if ($oldService->image_id) {
                    $imageId = $this->importMedia($oldService->image_id, 'services');
                }

                if ($oldService->icon_id) {
                    $iconId = $this->importMedia($oldService->icon_id, 'icons');
                }

                // Создаем услугу
                $service = Service::firstOrCreate(
                    ['id' => $oldService->id],
                    [
                        'name' => $oldService->name ?? '',
                        'slug' => $oldService->slug ?? Str::slug($oldService->name ?? ''),
                        'description' => $this->parseJsonField($oldService->description ?? null),
                        'image_id' => $imageId,
                        'icon_id' => $iconId,
                        'chapter_id' => $oldService->chapter_id ?? null,
                        'order' => $oldService->order ?? 0,
                        'is_active' => true,
                    ]
                );

                $servicesMap[$oldService->id] = $service;
                $imported++;

                // Импортируем связи с опциями (option_tree_service)
                if ($oldService->id) {
                    $this->importServiceOptionTrees($service->id, $oldService->id);
                    $this->importServiceOptions($service->id, $oldService->id);
                    $this->importServiceInstances($service->id, $oldService->id);
                }
            }

            $this->command->info("✅ Импортировано услуг: {$imported}");
        } catch (\Exception $e) {
            $this->command->warn("⚠️ Ошибка импорта услуг: " . $e->getMessage());
        }
    }

    /**
     * Импорт продуктов
     */
    protected function importProducts(): void
    {
        $this->command->info('🛍️ Импорт продуктов...');
        
        try {
            $oldProducts = DB::connection('old_db')
                ->table('products')
                ->get();
            
            $imported = 0;

            foreach ($oldProducts as $oldProduct) {
                // Импортируем изображения
                $imageId = null;
                $iconId = null;

                if ($oldProduct->image_id) {
                    $imageId = $this->importMedia($oldProduct->image_id, 'products');
                }

                if ($oldProduct->icon_id) {
                    $iconId = $this->importMedia($oldProduct->icon_id, 'icons');
                }

                // Создаем продукт
                $product = Product::firstOrCreate(
                    ['id' => $oldProduct->id],
                    [
                        'name' => $oldProduct->name ?? '',
                        'slug' => $oldProduct->slug ?? Str::slug($oldProduct->name ?? ''),
                        'description' => $this->parseJsonField($oldProduct->description ?? null),
                        'image_id' => $imageId,
                        'icon_id' => $iconId,
                        'chapter_id' => $oldProduct->chapter_id ?? null,
                        'order' => $oldProduct->order ?? 0,
                        'is_active' => true,
                    ]
                );

                $imported++;

                // Импортируем связи с услугами (product_service)
                if ($oldProduct->id) {
                    $this->importProductServices($product->id, $oldProduct->id);
                }
            }

            $this->command->info("✅ Импортировано продуктов: {$imported}");
        } catch (\Exception $e) {
            $this->command->warn("⚠️ Ошибка импорта продуктов: " . $e->getMessage());
        }
    }

    /**
     * Импорт кейсов
     */
    protected function importCases(): void
    {
        $this->command->info('📚 Импорт кейсов...');
        
        try {
            $oldCases = DB::connection('old_db')
                ->table('cases')
                ->get();
            
            $imported = 0;

            foreach ($oldCases as $oldCase) {
                // Импортируем изображения
                $imageId = null;
                $iconId = null;

                if ($oldCase->image_id) {
                    $imageId = $this->importMedia($oldCase->image_id, 'cases');
                }

                if ($oldCase->icon_id) {
                    $iconId = $this->importMedia($oldCase->icon_id, 'icons');
                }

                // Создаем кейс
                $case = ProjectCase::firstOrCreate(
                    ['id' => $oldCase->id],
                    [
                        'name' => $oldCase->name ?? '',
                        'slug' => $oldCase->slug ?? Str::slug($oldCase->name ?? ''),
                        'description' => $this->parseJsonField($oldCase->description ?? null),
                        'html' => $this->parseJsonField($oldCase->html ?? null),
                        'image_id' => $imageId,
                        'icon_id' => $iconId,
                        'chapter_id' => $oldCase->chapter_id ?? null,
                        'order' => $oldCase->order ?? 0,
                        'is_active' => $oldCase->is_active ?? true,
                    ]
                );

                $imported++;

                // Импортируем связи
                if ($oldCase->id) {
                    $this->importCaseServices($case->id, $oldCase->id);
                    $this->importCaseProducts($case->id, $oldCase->id);
                    $this->importCaseImages($case->id, $oldCase->id);
                }
            }

            $this->command->info("✅ Импортировано кейсов: {$imported}");
        } catch (\Exception $e) {
            $this->command->warn("⚠️ Ошибка импорта кейсов: " . $e->getMessage());
        }
    }

    /**
     * Импорт медиа файла
     */
    protected function importMedia(?int $oldMediaId, string $category = 'general'): ?int
    {
        if (!$oldMediaId) {
            return null;
        }

        try {
            // Проверяем, не импортирован ли уже
            $existingMedia = Media::where('id', $oldMediaId)->first();
            if ($existingMedia) {
                return $existingMedia->id;
            }

            // Получаем данные из старой БД
            $oldMedia = DB::connection('old_db')
                ->table('media')
                ->where('id', $oldMediaId)
                ->first();

            if (!$oldMedia) {
                return null;
            }

            // Определяем путь к файлу в старом проекте
            $oldPath = $this->getOldMediaPath($oldMedia);
            if (!$oldPath || !file_exists($oldPath)) {
                $this->command->warn("⚠️ Файл не найден: {$oldPath}");
                return null;
            }

            // Создаем директорию для нового файла
            $newDir = public_path("upload/{$category}");
            if (!File::exists($newDir)) {
                File::makeDirectory($newDir, 0755, true);
            }

            // Копируем файл
            $extension = pathinfo($oldMedia->name ?? $oldMedia->original_name ?? 'file', PATHINFO_EXTENSION);
            $newFileName = ($oldMedia->name ?? uniqid()) . '.' . $extension;
            $newPath = $newDir . '/' . $newFileName;

            if (!copy($oldPath, $newPath)) {
                $this->command->warn("⚠️ Не удалось скопировать файл: {$oldPath}");
                return null;
            }

            // Получаем размеры изображения
            $width = null;
            $height = null;
            if ($oldMedia->type === 'photo' || in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $imageInfo = @getimagesize($newPath);
                if ($imageInfo !== false) {
                    $width = $imageInfo[0];
                    $height = $imageInfo[1];
                }
            }

            // Получаем общую папку
            $commonFolder = \App\Models\Folder::withoutUserScope()->where('slug', 'common')->first();
            
            // Создаем запись в новой БД
            $media = Media::create([
                'id' => $oldMedia->id,
                'name' => $newFileName,
                'original_name' => $oldMedia->original_name ?? $newFileName,
                'extension' => $extension,
                'disk' => "upload/{$category}",
                'width' => $width,
                'height' => $height,
                'type' => $oldMedia->type ?? 'photo',
                'size' => $oldMedia->size ?? filesize($newPath),
                'folder_id' => $commonFolder ? $commonFolder->id : null,
                'user_id' => null,
                'temporary' => false,
                'metadata' => json_encode([
                    'path' => "upload/{$category}/{$newFileName}",
                    'mime_type' => mime_content_type($newPath),
                ]),
            ]);

            return $media->id;
        } catch (\Exception $e) {
            $this->command->warn("⚠️ Ошибка импорта медиа {$oldMediaId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Получить путь к медиа файлу (сначала проверяем локальные файлы)
     */
    protected function getOldMediaPath($oldMedia): ?string
    {
        // Сначала проверяем локальные файлы в текущем проекте
        $localPaths = [];

        // Если есть metadata с путем
        if ($oldMedia->metadata) {
            $metadata = json_decode($oldMedia->metadata, true);
            if (isset($metadata['path'])) {
                $localPaths[] = public_path(ltrim($metadata['path'], '/'));
            }
        }

        // Путь через disk и name
        if ($oldMedia->disk && $oldMedia->name) {
            $localPaths[] = public_path(ltrim($oldMedia->disk, '/') . '/' . $oldMedia->name);
        }

        // Стандартные локальные пути
        if ($oldMedia->name) {
            $localPaths[] = public_path('upload/' . $oldMedia->name);
            $localPaths[] = public_path('uploads/' . $oldMedia->name);
            $localPaths[] = public_path('img/services/' . $oldMedia->name);
            $localPaths[] = public_path('img/system/' . $oldMedia->name);
        }

        // Проверяем локальные файлы
        foreach ($localPaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // Если локально не найдено, пробуем старый проект (опционально)
        if ($this->oldProjectPath && File::exists($this->oldProjectPath)) {
            $oldPaths = [];

            // Если есть metadata с путем
            if ($oldMedia->metadata) {
                $metadata = json_decode($oldMedia->metadata, true);
                if (isset($metadata['path'])) {
                    $oldPaths[] = $this->oldProjectPath . '/public/' . ltrim($metadata['path'], '/');
                }
            }

            // Путь через disk и name
            if ($oldMedia->disk && $oldMedia->name) {
                $oldPaths[] = $this->oldProjectPath . '/public/' . ltrim($oldMedia->disk, '/') . '/' . $oldMedia->name;
            }

            // Стандартные пути
            if ($oldMedia->name) {
                $oldPaths[] = $this->oldProjectPath . '/public/upload/' . $oldMedia->name;
                $oldPaths[] = $this->oldProjectPath . '/public/uploads/' . $oldMedia->name;
            }

            foreach ($oldPaths as $path) {
                if (file_exists($path)) {
                    return $path;
                }
            }
        }

        return null;
    }

    /**
     * Импорт связей услуги с деревьями опций
     */
    protected function importServiceOptionTrees(int $serviceId, int $oldServiceId): void
    {
        try {
            $oldRelations = DB::connection('old_db')
                ->table('option_tree_service')
                ->where('service_id', $oldServiceId)
                ->pluck('option_tree_id')
                ->toArray();

            if (!empty($oldRelations)) {
                $service = Service::find($serviceId);
                if ($service) {
                    $service->optionTrees()->sync($oldRelations);
                }
            }
        } catch (\Exception $e) {
            // Игнорируем ошибки связей
        }
    }

    /**
     * Импорт связей услуги с опциями
     */
    protected function importServiceOptions(int $serviceId, int $oldServiceId): void
    {
        try {
            $oldRelations = DB::connection('old_db')
                ->table('option_service')
                ->where('service_id', $oldServiceId)
                ->pluck('option_id')
                ->toArray();

            if (!empty($oldRelations)) {
                $service = Service::find($serviceId);
                if ($service) {
                    $service->options()->sync($oldRelations);
                }
            }
        } catch (\Exception $e) {
            // Игнорируем ошибки связей
        }
    }

    /**
     * Импорт связей услуги с экземплярами
     */
    protected function importServiceInstances(int $serviceId, int $oldServiceId): void
    {
        try {
            $oldRelations = DB::connection('old_db')
                ->table('instance_service')
                ->where('service_id', $oldServiceId)
                ->pluck('instance_id')
                ->toArray();

            if (!empty($oldRelations)) {
                $service = Service::find($serviceId);
                if ($service) {
                    $service->instances()->sync($oldRelations);
                }
            }
        } catch (\Exception $e) {
            // Игнорируем ошибки связей
        }
    }

    /**
     * Импорт связей продукта с услугами
     */
    protected function importProductServices(int $productId, int $oldProductId): void
    {
        try {
            $oldRelations = DB::connection('old_db')
                ->table('product_service')
                ->where('product_id', $oldProductId)
                ->pluck('service_id')
                ->toArray();

            if (!empty($oldRelations)) {
                $product = Product::find($productId);
                if ($product) {
                    $product->services()->sync($oldRelations);
                }
            }
        } catch (\Exception $e) {
            // Игнорируем ошибки связей
        }
    }

    /**
     * Импорт связей кейса с услугами
     */
    protected function importCaseServices(int $caseId, int $oldCaseId): void
    {
        try {
            $oldRelations = DB::connection('old_db')
                ->table('cases_service')
                ->where('cases_id', $oldCaseId)
                ->pluck('service_id')
                ->toArray();

            if (!empty($oldRelations)) {
                $case = ProjectCase::find($caseId);
                if ($case) {
                    $case->services()->sync($oldRelations);
                }
            }
        } catch (\Exception $e) {
            // Игнорируем ошибки связей
        }
    }

    /**
     * Импорт связей кейса с продуктами
     */
    protected function importCaseProducts(int $caseId, int $oldCaseId): void
    {
        try {
            $oldRelations = DB::connection('old_db')
                ->table('cases_product')
                ->where('cases_id', $oldCaseId)
                ->pluck('product_id')
                ->toArray();

            if (!empty($oldRelations)) {
                $case = ProjectCase::find($caseId);
                if ($case) {
                    $case->products()->sync($oldRelations);
                }
            }
        } catch (\Exception $e) {
            // Игнорируем ошибки связей
        }
    }

    /**
     * Импорт изображений кейса
     */
    protected function importCaseImages(int $caseId, int $oldCaseId): void
    {
        try {
            $oldImageIds = DB::connection('old_db')
                ->table('cases_image')
                ->where('cases_id', $oldCaseId)
                ->pluck('image_id')
                ->toArray();

            if (!empty($oldImageIds)) {
                $newImageIds = [];
                foreach ($oldImageIds as $oldImageId) {
                    $newImageId = $this->importMedia($oldImageId, 'cases');
                    if ($newImageId) {
                        $newImageIds[] = $newImageId;
                    }
                }

                if (!empty($newImageIds)) {
                    $case = ProjectCase::find($caseId);
                    if ($case) {
                        $case->images()->sync($newImageIds);
                    }
                }
            }
        } catch (\Exception $e) {
            // Игнорируем ошибки связей
        }
    }

    /**
     * Парсинг JSON поля
     */
    protected function parseJsonField($value): ?array
    {
        if (is_null($value)) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
            return ['ru' => $value];
        }

        return null;
    }
}
