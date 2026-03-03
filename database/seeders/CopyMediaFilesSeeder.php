<?php

namespace Database\Seeders;

use Database\Seeders\Traits\MediaRegistrationTrait;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CopyMediaFilesSeeder extends Seeder
{
    use MediaRegistrationTrait;
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
        $this->command->info('🚀 Начало копирования медиа файлов из старого проекта...');

        try {
            // Подключаемся к старой БД (опционально)
            if (!$this->connectToOldDatabase()) {
                $this->command->info('ℹ️ Копирование медиа файлов из старой БД пропущено.');
                $this->command->info('ℹ️ Используйте локальные файлы из public/img/ и public/upload/');
                return;
            }

            // Собираем все ID медиа файлов, которые используются
            $mediaIds = $this->collectAllMediaIds();

            $this->command->info("📋 Найдено медиа файлов для копирования: " . count($mediaIds));

            // Копируем файлы
            $copied = 0;
            $failed = 0;

            foreach ($mediaIds as $mediaId) {
                if ($this->copyMediaFile($mediaId)) {
                    $copied++;
                } else {
                    $failed++;
                }
            }

            $this->command->info("✅ Скопировано файлов: {$copied}");
            if ($failed > 0) {
                $this->command->warn("⚠️ Не удалось скопировать файлов: {$failed}");
            }

            $this->command->info('✅ Копирование медиа файлов завершено!');
        } catch (\Exception $e) {
            $this->command->error('❌ Ошибка копирования: ' . $e->getMessage());
            Log::error('CopyMediaFilesSeeder error', [
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
            $this->command->info('ℹ️ Пропускаем копирование медиа файлов из старой БД.');
            return false;
        }
    }

    /**
     * Собрать все ID медиа файлов, которые используются
     */
    protected function collectAllMediaIds(): array
    {
        $mediaIds = [];

        // Изображения и иконки продуктов
        $productImages = DB::connection('old_db')
            ->table('products')
            ->whereNotNull('image_id')
            ->pluck('image_id')
            ->toArray();
        $productIcons = DB::connection('old_db')
            ->table('products')
            ->whereNotNull('icon_id')
            ->pluck('icon_id')
            ->toArray();

        // Изображения и иконки услуг
        $serviceImages = DB::connection('old_db')
            ->table('services')
            ->whereNotNull('image_id')
            ->pluck('image_id')
            ->toArray();
        $serviceIcons = DB::connection('old_db')
            ->table('services')
            ->whereNotNull('icon_id')
            ->pluck('icon_id')
            ->toArray();

        // Изображения и иконки кейсов
        $caseImages = DB::connection('old_db')
            ->table('cases')
            ->whereNotNull('image_id')
            ->pluck('image_id')
            ->toArray();
        $caseIcons = DB::connection('old_db')
            ->table('cases')
            ->whereNotNull('icon_id')
            ->pluck('icon_id')
            ->toArray();

        // Изображения из галереи кейсов
        $caseGalleryImages = DB::connection('old_db')
            ->table('cases_image')
            ->pluck('image_id')
            ->toArray();

        // Объединяем все ID и убираем дубликаты
        $allIds = array_merge(
            $productImages,
            $productIcons,
            $serviceImages,
            $serviceIcons,
            $caseImages,
            $caseIcons,
            $caseGalleryImages
        );

        $mediaIds = array_unique($allIds);
        $mediaIds = array_filter($mediaIds); // Убираем null значения

        return array_values($mediaIds);
    }

    /**
     * Скопировать медиа файл
     */
    protected function copyMediaFile(int $mediaId): bool
    {
        try {
            // Получаем данные из старой БД
            $oldMedia = DB::connection('old_db')
                ->table('media')
                ->where('id', $mediaId)
                ->first();

            if (!$oldMedia) {
                $this->command->warn("⚠️ Медиа файл с ID {$mediaId} не найден в старой БД");
                return false;
            }

            // Определяем путь к файлу в старом проекте
            $oldPath = $this->getOldMediaPath($oldMedia);
            if (!$oldPath || !file_exists($oldPath)) {
                $this->command->warn("⚠️ Файл не найден: {$oldPath} (ID: {$mediaId})");
                return false;
            }

            // Определяем категорию для копирования
            $category = $this->determineCategory($mediaId);

            // Создаем директорию для нового файла
            $newDir = public_path("upload/{$category}");
            if (!File::exists($newDir)) {
                File::makeDirectory($newDir, 0755, true);
            }

            // Определяем имя файла
            $extension = pathinfo($oldMedia->name ?? $oldMedia->original_name ?? 'file', PATHINFO_EXTENSION);
            if (empty($extension) && $oldMedia->extension) {
                $extension = $oldMedia->extension;
            }
            
            $newFileName = ($oldMedia->name ?? uniqid()) . '.' . $extension;
            $newPath = $newDir . '/' . $newFileName;

            // Если файл уже существует, пропускаем
            if (file_exists($newPath)) {
                $this->command->info("ℹ️ Файл уже существует: {$newFileName}");
                return true;
            }

            // Копируем файл
            if (!copy($oldPath, $newPath)) {
                $this->command->warn("⚠️ Не удалось скопировать файл: {$oldPath} -> {$newPath}");
                return false;
            }

            $this->command->info("✅ Скопирован: {$newFileName}");
            
            // Регистрируем файл в media библиотеке
            $relativePath = "upload/{$category}/{$newFileName}";
            $this->registerMediaByPath($relativePath, $category);
            
            return true;
        } catch (\Exception $e) {
            $this->command->warn("⚠️ Ошибка копирования медиа {$mediaId}: " . $e->getMessage());
            return false;
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

        // Путь через original_name
        if ($oldMedia->original_name) {
            $localPaths[] = public_path('upload/' . $oldMedia->original_name);
            $localPaths[] = public_path('uploads/' . $oldMedia->original_name);
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

            // Путь через original_name
            if ($oldMedia->original_name) {
                $oldPaths[] = $this->oldProjectPath . '/public/upload/' . $oldMedia->original_name;
                $oldPaths[] = $this->oldProjectPath . '/public/uploads/' . $oldMedia->original_name;
            }

            // Проверяем каждый путь
            foreach ($oldPaths as $path) {
                if (file_exists($path)) {
                    return $path;
                }
            }
        }

        return null;
    }

    /**
     * Определить категорию для медиа файла
     */
    protected function determineCategory(int $mediaId): string
    {
        // Проверяем, где используется этот медиа файл
        $isProductImage = DB::connection('old_db')
            ->table('products')
            ->where('image_id', $mediaId)
            ->exists();
        
        $isProductIcon = DB::connection('old_db')
            ->table('products')
            ->where('icon_id', $mediaId)
            ->exists();

        $isServiceImage = DB::connection('old_db')
            ->table('services')
            ->where('image_id', $mediaId)
            ->exists();
        
        $isServiceIcon = DB::connection('old_db')
            ->table('services')
            ->where('icon_id', $mediaId)
            ->exists();

        $isCaseImage = DB::connection('old_db')
            ->table('cases')
            ->where('image_id', $mediaId)
            ->exists();
        
        $isCaseIcon = DB::connection('old_db')
            ->table('cases')
            ->where('icon_id', $mediaId)
            ->exists();

        $isCaseGallery = DB::connection('old_db')
            ->table('cases_image')
            ->where('image_id', $mediaId)
            ->exists();

        if ($isProductImage || $isProductIcon) {
            return $isProductIcon ? 'icons' : 'products';
        }

        if ($isServiceImage || $isServiceIcon) {
            return $isServiceIcon ? 'icons' : 'services';
        }

        if ($isCaseImage || $isCaseIcon || $isCaseGallery) {
            return ($isCaseIcon) ? 'icons' : 'cases';
        }

        // По умолчанию
        return 'general';
    }
}


