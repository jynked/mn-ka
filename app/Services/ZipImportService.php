<?php

namespace App\Services;

use ZipArchive;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class ZipImportService
{
    /**
     * Распаковать ZIP архив во временную директорию
     *
     * @param string $zipPath Путь к ZIP файлу
     * @return array|false Возвращает ['extractPath' => string, 'csvPath' => string] или false при ошибке
     */
    public function extractZip(string $zipPath): array|false
    {
        if (!extension_loaded('zip')) {
            Log::error('ZIP extension not loaded');
            return false;
        }

        $zip = new ZipArchive();
        $result = $zip->open($zipPath);

        if ($result !== true) {
            Log::error("Failed to open ZIP file: {$zipPath}, error code: {$result}");
            return false;
        }

        // Создаем временную директорию для распаковки
        $extractPath = storage_path('app/temp/import_' . uniqid());
        
        if (!File::exists($extractPath)) {
            File::makeDirectory($extractPath, 0755, true);
        }

        // Распаковываем архив
        if (!$zip->extractTo($extractPath)) {
            $zip->close();
            Log::error("Failed to extract ZIP to: {$extractPath}");
            File::deleteDirectory($extractPath);
            return false;
        }

        $zip->close();

        // Ищем CSV файл в распакованном архиве
        $csvPath = $this->findCsvFile($extractPath);

        if (!$csvPath) {
            Log::error("CSV file not found in ZIP archive");
            File::deleteDirectory($extractPath);
            return false;
        }

        return [
            'extractPath' => $extractPath,
            'csvPath' => $csvPath,
        ];
    }

    /**
     * Найти CSV файл в директории (рекурсивно)
     *
     * @param string $directory
     * @return string|false
     */
    protected function findCsvFile(string $directory): string|false
    {
        $files = File::allFiles($directory);
        
        foreach ($files as $file) {
            $extension = strtolower($file->getExtension());
            if (in_array($extension, ['csv', 'txt'])) {
                return $file->getPathname();
            }
        }

        return false;
    }

    /**
     * Получить путь к файлу изображения относительно корня распаковки
     *
     * @param string $extractPath Корневая директория распаковки
     * @param string $imagePath Путь из CSV (например, "images/product1.jpg")
     * @return string|false Полный путь к файлу или false если не найден
     */
    public function resolveImagePath(string $extractPath, string $imagePath): string|false
    {
        // Нормализуем путь (убираем начальные слэши и точки)
        $imagePath = ltrim($imagePath, '/\\');
        $imagePath = str_replace('..', '', $imagePath); // Защита от directory traversal
        
        // Пробуем разные варианты пути
        $possiblePaths = [
            $extractPath . DIRECTORY_SEPARATOR . $imagePath,
            $extractPath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $imagePath),
            $extractPath . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $imagePath),
        ];

        foreach ($possiblePaths as $fullPath) {
            // Проверяем что путь находится внутри extractPath (безопасность)
            $realExtractPath = realpath($extractPath);
            $realFullPath = realpath(dirname($fullPath));
            
            if ($realExtractPath && $realFullPath && strpos($realFullPath, $realExtractPath) === 0) {
                if (file_exists($fullPath) && is_file($fullPath)) {
                    return $fullPath;
                }
            }
        }

        return false;
    }

    /**
     * Очистить временную директорию после импорта
     *
     * @param string $extractPath
     * @return void
     */
    public function cleanup(string $extractPath): void
    {
        try {
            if (File::exists($extractPath)) {
                File::deleteDirectory($extractPath);
            }
        } catch (\Exception $e) {
            Log::warning("Failed to cleanup temp directory: {$extractPath}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Проверить, является ли файл ZIP архивом
     *
     * @param string $filePath
     * @return bool
     */
    public function isZipFile(string $filePath): bool
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        return in_array($mimeType, [
            'application/zip',
            'application/x-zip-compressed',
        ]);
    }
}

