<?php

namespace Database\Seeders\Traits;

use App\Models\Folder;
use App\Models\Media;
use Illuminate\Support\Facades\File;

trait MediaRegistrationTrait
{
    /**
     * Получить или создать общую папку
     */
    protected function getOrCreateCommonFolder(): ?Folder
    {
        try {
            // Ищем общую папку по slug (обходим UserScope, так как это системная папка)
            $commonFolder = Folder::withoutUserScope()->where('slug', 'common')->first();
            
            if (!$commonFolder) {
                // Если папка не найдена, создаем её
                $commonFolder = Folder::withoutUserScope()->create([
                    'name' => 'Общая',
                    'slug' => 'common',
                    'src' => 'folder',
                    'parent_id' => null,
                    'position' => 0,
                    'protected' => true,
                ]);
                
                if ($this->command) {
                    $this->command->info("✓ Создана общая папка (ID: {$commonFolder->id})");
                }
            }
            
            return $commonFolder;
        } catch (\Exception $e) {
            if ($this->command) {
                $this->command->warn("⚠️ Не удалось получить общую папку: " . $e->getMessage());
            }
            return null;
        }
    }
    /**
     * Зарегистрировать файл в медиа библиотеке
     */
    protected function registerMediaFile(
        string $fullPath,
        string $fileName = null,
        string $category = 'general',
        ?int $width = null,
        ?int $height = null
    ): ?Media {
        try {
            if (!File::exists($fullPath)) {
                $this->command->warn("⚠️ Файл не существует: {$fullPath}");
                return null;
            }

            // Получаем относительный путь от public
            $relativePath = str_replace(public_path(), '', $fullPath);
            $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
            
            if (!$fileName) {
                $fileName = basename($relativePath);
            }
            
            $dirPath = dirname($relativePath);
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            // Определяем тип файла
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            $videoExtensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'];
            $type = 'document';
            
            if (in_array($extension, $imageExtensions)) {
                $type = 'photo';
            } elseif (in_array($extension, $videoExtensions)) {
                $type = 'video';
            }
            
            // Проверяем, не зарегистрирован ли уже
            $existing = Media::where('name', $fileName)
                ->where('disk', $dirPath)
                ->first();

            if ($existing) {
                return $existing;
            }
            
            // Получаем размер файла
            $fileSize = File::size($fullPath);
            
            // Получаем размеры изображения, если еще не известны
            if ($type === 'photo' && ($width === null || $height === null)) {
                if ($extension === 'svg') {
                    // Для SVG не можем получить размеры через getimagesize
                    $width = null;
                    $height = null;
                } else {
                    $imageInfo = @getimagesize($fullPath);
                    if ($imageInfo !== false) {
                        $width = $imageInfo[0];
                        $height = $imageInfo[1];
                    }
                }
            }
            
            // Определяем MIME тип
            $mimeType = null;
            if ($extension === 'svg') {
                $mimeType = 'image/svg+xml';
            } elseif ($extension === 'jpg' || $extension === 'jpeg') {
                $mimeType = 'image/jpeg';
            } elseif ($extension === 'png') {
                $mimeType = 'image/png';
            } elseif ($extension === 'gif') {
                $mimeType = 'image/gif';
            } elseif ($extension === 'webp') {
                $mimeType = 'image/webp';
            } else {
                $mimeType = function_exists('mime_content_type') ? mime_content_type($fullPath) : null;
            }

            // Получаем общую папку
            $commonFolder = $this->getOrCreateCommonFolder();
            
            $media = Media::create([
                'name' => $fileName,
                'original_name' => $fileName,
                'extension' => $extension,
                'disk' => $dirPath,
                'width' => $width,
                'height' => $height,
                'type' => $type,
                'size' => $fileSize,
                'folder_id' => $commonFolder ? $commonFolder->id : null,
                'user_id' => null,
                'temporary' => false,
                'metadata' => json_encode([
                    'path' => $relativePath,
                    'mime_type' => $mimeType,
                    'category' => $category,
                ]),
            ]);
            
            if ($this->command) {
                $this->command->info("✓ Зарегистрировано в media: {$relativePath}");
            }
            
            return $media;
        } catch (\Exception $e) {
            if ($this->command) {
                $this->command->warn("⚠️ Не удалось зарегистрировать в media: {$fileName} - " . $e->getMessage());
            }
            return null;
        }
    }

    /**
     * Зарегистрировать файл по относительному пути от public
     */
    protected function registerMediaByPath(
        string $relativePath,
        string $category = 'general',
        ?int $width = null,
        ?int $height = null
    ): ?Media {
        $fullPath = public_path($relativePath);
        return $this->registerMediaFile($fullPath, null, $category, $width, $height);
    }

    /**
     * Зарегистрировать несколько файлов из директории
     */
    protected function registerMediaFromDirectory(
        string $directory,
        string $category = 'general',
        array $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']
    ): int {
        $count = 0;
        $fullPath = public_path($directory);
        
        if (!File::isDirectory($fullPath)) {
            if ($this->command) {
                $this->command->warn("⚠️ Директория не существует: {$directory}");
            }
            return 0;
        }
        
        $files = File::allFiles($fullPath);
        
        foreach ($files as $file) {
            $extension = strtolower($file->getExtension());
            
            if (in_array($extension, $extensions)) {
                $relativePath = str_replace(public_path(), '', $file->getPathname());
                $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
                
                $media = $this->registerMediaByPath($relativePath, $category);
                
                if ($media) {
                    $count++;
                }
            }
        }
        
        return $count;
    }
}

