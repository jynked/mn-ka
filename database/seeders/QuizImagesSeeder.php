<?php

namespace Database\Seeders;

use App\Models\Folder;
use App\Models\Media;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class QuizImagesSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Путь к старому проекту
     */
    private function getOldProjectPath(): ?string
    {
        // Если задан путь через переменную окружения, используем его
        $envPath = env('OLD_PROJECT_PATH');
        if ($envPath && File::exists($envPath)) {
            return $envPath;
        }

        // Пробуем возможные пути в зависимости от окружения
        $possiblePaths = [
            // Linux/Unix пути
            '/home/d/dsc23ytp/stroy/public_html',
            '/var/www/html',
            '/home/dsc23ytp/stroy/public_html',
            
            // Windows пути (для локальной разработки)
            'C:\OSPanel\domains\lagom',
            'C:\xampp\htdocs\lagom',
        ];

        foreach ($possiblePaths as $path) {
            if (File::exists($path)) {
                return $path;
            }
        }

        // Если ничего не найдено, возвращаем null
        return null;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Массив изображений для квиза с указанием исходных файлов
        $quizImages = [
            [
                'name' => 'interest-1.jpg',
                'original_name' => 'interest-1.jpg',
                'title' => 'Купить/продать',
                'source_file' => 'public/img/delete/image-1.png', // Исходный файл в старом проекте
                'target_path' => 'upload/quiz/interest-1.jpg',
                'disk' => 'upload/quiz',
            ],
            [
                'name' => 'interest-2.jpg',
                'original_name' => 'interest-2.jpg',
                'title' => 'Аренда земли',
                'source_file' => 'public/img/delete/image-1.png', // Используем тот же файл
                'target_path' => 'upload/quiz/interest-2.jpg',
                'disk' => 'upload/quiz',
            ],
            [
                'name' => 'interest-3.jpg',
                'original_name' => 'interest-3.jpg',
                'title' => 'Юридические аспекты',
                'source_file' => 'public/img/delete/image-2.png',
                'target_path' => 'upload/quiz/interest-3.jpg',
                'disk' => 'upload/quiz',
            ],
            [
                'name' => 'interest-4.jpg',
                'original_name' => 'interest-4.jpg',
                'title' => 'Консультация',
                'source_file' => 'public/img/delete/image-3.png',
                'target_path' => 'upload/quiz/interest-4.jpg',
                'disk' => 'upload/quiz',
            ],
        ];

        // Создаем директорию для изображений квиза, если её нет
        $quizDir = public_path('upload/quiz');
        if (!File::exists($quizDir)) {
            File::makeDirectory($quizDir, 0755, true);
            $this->command->info("Создана директория: {$quizDir}");
        }

        // Проверяем наличие директории public/img/delete в текущем проекте
        $localImgDeletePath = public_path('img/delete');
        if (!File::exists($localImgDeletePath)) {
            File::makeDirectory($localImgDeletePath, 0755, true);
            $this->command->info("Создана директория: {$localImgDeletePath}");
        }

        $sourceBasePath = $this->getOldProjectPath();
        
        $this->command->info('🔍 Поиск исходных изображений:');
        $this->command->info('1. Сначала проверяется: public/img/delete/ (в текущем проекте)');
        if ($sourceBasePath) {
            $this->command->info("2. Если не найдено, проверяется: {$sourceBasePath}");
        }
        $this->command->info('3. Если ничего не найдено, создаются placeholder изображения');
        $this->command->newLine();

        foreach ($quizImages as $imageData) {
            $targetPath = public_path($imageData['target_path']);
            $relativePath = $imageData['target_path'];
            $sourcePath = null;

            // Сначала проверяем файл в текущем проекте (public/img/delete/)
            $localSourcePath = public_path(str_replace('public/', '', $imageData['source_file']));
            if (File::exists($localSourcePath)) {
                $sourcePath = $localSourcePath;
                $this->command->info("Найден файл в текущем проекте: {$localSourcePath}");
            } else {
                // Если не найден в текущем проекте, пробуем найти в старом проекте
                if ($sourceBasePath) {
                    $sourcePath = rtrim($sourceBasePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $imageData['source_file']);
                    if (File::exists($sourcePath)) {
                        $this->command->info("Найден файл в старом проекте: {$sourcePath}");
                    } else {
                        $sourcePath = null;
                    }
                }
            }

            // Копируем файл, если найден
            if ($sourcePath && File::exists($sourcePath)) {
                // Если исходный файл PNG, конвертируем в JPG
                if (strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION)) === 'png') {
                    $this->convertPngToJpg($sourcePath, $targetPath);
                    $this->command->info("Скопировано и конвертировано: {$imageData['source_file']} -> {$imageData['target_path']}");
                } else {
                    // Просто копируем файл
                    File::copy($sourcePath, $targetPath);
                    $this->command->info("Скопировано: {$imageData['source_file']} -> {$imageData['target_path']}");
                }
            } else {
                // Если файл не найден ни в текущем проекте, ни в старом, создаем placeholder
                $expectedLocalPath = public_path('img/delete/' . basename($imageData['source_file']));
                $this->command->warn("Файл не найден: {$expectedLocalPath}");
                $this->command->info("Ожидаемый путь: public/img/delete/" . basename($imageData['source_file']));
                
                // Проверяем, не создан ли уже файл (чтобы не перезаписывать существующие)
                if (!File::exists($targetPath)) {
                    $this->createPlaceholderImage($targetPath, $imageData['title']);
                    $this->command->info("Создан placeholder для {$imageData['name']}");
                } else {
                    $this->command->info("Файл {$imageData['name']} уже существует, пропускаем.");
                }
            }

            // Получаем информацию о файле
            $fileSize = File::exists($targetPath) ? File::size($targetPath) : 0;
            $extension = pathinfo($imageData['name'], PATHINFO_EXTENSION);
            
            // Получаем размеры изображения
            $width = null;
            $height = null;
            $mimeType = 'image/jpeg';
            $imageInfo = false;
            
            if (File::exists($targetPath) && in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $imageInfo = @getimagesize($targetPath);
                if ($imageInfo !== false) {
                    $width = $imageInfo[0];
                    $height = $imageInfo[1];
                    $mimeType = $imageInfo['mime'] ?? 'image/jpeg';
                }
            }

            // Получаем общую папку (обходим UserScope, так как это системная папка)
            $commonFolder = Folder::withoutUserScope()->where('slug', 'common')->first();
            
            // Создаем или обновляем запись в медиа-библиотеке
            Media::updateOrCreate(
                [
                    'name' => $imageData['name'],
                    'disk' => $imageData['disk'],
                ],
                [
                    'original_name' => $imageData['original_name'],
                    'extension' => $extension,
                    'width' => $width,
                    'height' => $height,
                    'type' => 'photo',
                    'size' => $fileSize,
                    'folder_id' => $commonFolder ? $commonFolder->id : null,
                    'user_id' => null,
                    'temporary' => false,
                    'metadata' => json_encode([
                        'path' => $relativePath,
                        'mime_type' => $mimeType,
                        'title' => $imageData['title'],
                    ]),
                ]
            );
        }

        $this->command->info('✅ Изображения для квиза успешно добавлены в медиа-библиотеку!');
    }

    /**
     * Конвертировать PNG в JPG
     */
    private function convertPngToJpg(string $sourcePath, string $targetPath): void
    {
        if (!extension_loaded('gd')) {
            // Если GD не загружена, просто копируем файл
            File::copy($sourcePath, $targetPath);
            return;
        }

        // Загружаем PNG изображение
        $image = @imagecreatefrompng($sourcePath);
        if ($image === false) {
            // Если не удалось загрузить, копируем как есть
            File::copy($sourcePath, $targetPath);
            return;
        }

        // Создаем новое изображение с белым фоном (для прозрачности PNG)
        $width = imagesx($image);
        $height = imagesy($image);
        $jpg = imagecreatetruecolor($width, $height);
        
        // Заливаем белым фоном
        $white = imagecolorallocate($jpg, 255, 255, 255);
        imagefill($jpg, 0, 0, $white);
        
        // Копируем PNG на JPG с сохранением прозрачности (как белого)
        imagecopyresampled($jpg, $image, 0, 0, 0, 0, $width, $height, $width, $height);
        
        // Сохраняем как JPG
        imagejpeg($jpg, $targetPath, 90);
        
        // Освобождаем память
        imagedestroy($image);
        imagedestroy($jpg);
    }

    /**
     * Создать placeholder изображение
     */
    private function createPlaceholderImage(string $path, string $title): void
    {
        // Создаем простое изображение через GD
        if (!extension_loaded('gd')) {
            // Если GD не загружена, создаем пустой файл
            File::put($path, '');
            return;
        }

        $width = 245;
        $height = 140;
        $image = imagecreatetruecolor($width, $height);

        // Цвета
        $bgColor = imagecolorallocate($image, 101, 124, 108); // #657C6C
        $textColor = imagecolorallocate($image, 255, 255, 255);

        // Заливка фона
        imagefill($image, 0, 0, $bgColor);

        // Добавляем текст (если возможно)
        if (function_exists('imagestring')) {
            $fontSize = 3;
            $textX = ($width - strlen($title) * imagefontwidth($fontSize)) / 2;
            $textY = ($height - imagefontheight($fontSize)) / 2;
            imagestring($image, $fontSize, $textX, $textY, substr($title, 0, 20), $textColor);
        }

        // Сохраняем изображение
        imagejpeg($image, $path, 80);
        imagedestroy($image);
    }
}
