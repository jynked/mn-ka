<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Folder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class MediaImportService
{
    /**
     * Загрузить файл изображения в Media библиотеку
     *
     * @param string $filePath Полный путь к файлу
     * @param int|null|string $folderIdOrName ID папки, название папки или null для общей папки
     * @return Media|false
     */
    public function uploadImageFromPath(string $filePath, int|string|null $folderIdOrName = null): Media|false
    {
        if (!file_exists($filePath) || !is_file($filePath)) {
            Log::error("File not found: {$filePath}");
            return false;
        }

        // Получаем информацию о файле
        $originalName = basename($filePath);
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        // Проверяем, что это изображение
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        if (!in_array($extension, $allowedExtensions)) {
            Log::error("Invalid image extension: {$extension}");
            return false;
        }

        $mimeType = mime_content_type($filePath);
        $fileSize = filesize($filePath);

        // Определяем тип файла
        $type = $this->getFileType($mimeType);

        // Определяем ID папки
        $folderId = $this->resolveFolderId($folderIdOrName);

        // Генерируем уникальное имя файла
        $fileName = uniqid() . '_' . time() . '.' . $extension;

        // Определяем путь для сохранения
        $uploadPath = 'upload';
        if ($folderId) {
            $folder = Folder::find($folderId);
            if ($folder) {
                $folderPath = $this->getFolderPath($folder);
                $uploadPath = 'upload/' . $folderPath;
            }
        }

        // Создаём директорию если не существует
        $fullPath = public_path($uploadPath);
        if (!File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
        }

        // Копируем файл
        $targetPath = $fullPath . '/' . $fileName;
        if (!copy($filePath, $targetPath)) {
            Log::error("Failed to copy file from {$filePath} to {$targetPath}");
            return false;
        }

        $relativePath = $uploadPath . '/' . $fileName;

        // Получаем размеры изображения
        $width = null;
        $height = null;
        if ($type === 'photo' && in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $imageInfo = @getimagesize($targetPath);
            if ($imageInfo !== false) {
                $width = $imageInfo[0];
                $height = $imageInfo[1];
            }
        }

        // Сохраняем в БД
        try {
            $media = Media::create([
                'name' => $fileName,
                'original_name' => $originalName,
                'extension' => $extension,
                'disk' => $uploadPath,
                'width' => $width,
                'height' => $height,
                'type' => $type,
                'size' => $fileSize,
                'folder_id' => $folderId,
                'user_id' => auth()->check() ? auth()->id() : null,
                'temporary' => false,
                'metadata' => json_encode([
                    'path' => $relativePath,
                    'mime_type' => $mimeType
                ])
            ]);

            return $media;
        } catch (\Exception $e) {
            Log::error("Failed to create Media record: " . $e->getMessage());
            // Удаляем скопированный файл при ошибке
            if (file_exists($targetPath)) {
                @unlink($targetPath);
            }
            return false;
        }
    }

    /**
     * Определить ID папки по ID, имени или создать новую
     *
     * @param int|string|null $folderIdOrName
     * @return int|null
     */
    protected function resolveFolderId(int|string|null $folderIdOrName): ?int
    {
        // Если передан null, используем общую папку
        if ($folderIdOrName === null) {
            $commonFolder = Folder::where('slug', 'common')->first();
            return $commonFolder?->id;
        }

        // Если передан int, это уже ID папки
        if (is_int($folderIdOrName)) {
            return $folderIdOrName;
        }

        // Если передана строка, ищем папку по slug или создаем новую
        if (is_string($folderIdOrName)) {
            $slug = \Illuminate\Support\Str::slug($folderIdOrName);
            
            // Ищем существующую папку
            $folder = Folder::where('slug', $slug)->first();
            
            // Если не нашли, создаем новую
            if (!$folder) {
                $folder = Folder::create([
                    'name' => ucfirst($folderIdOrName),
                    'slug' => $slug,
                    'parent_id' => null,
                ]);
            }
            
            return $folder->id;
        }

        return null;
    }

    /**
     * Определить тип файла по MIME типу
     *
     * @param string $mimeType
     * @return string
     */
    protected function getFileType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'photo';
        }
        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }
        return 'document';
    }

    /**
     * Получить путь папки из иерархии
     *
     * @param Folder $folder
     * @return string
     */
    protected function getFolderPath(Folder $folder): string
    {
        $path = [];
        $current = Folder::with('parent')->find($folder->id);
        
        while ($current) {
            array_unshift($path, $current->slug);
            $current = $current->parent;
        }
        
        return implode('/', $path);
    }
}

