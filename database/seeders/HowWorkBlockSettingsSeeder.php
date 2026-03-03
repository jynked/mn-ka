<?php

namespace Database\Seeders;

use App\Models\HowWorkBlockSettings;
use App\Models\Media;
use Database\Seeders\Traits\MediaRegistrationTrait;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class HowWorkBlockSettingsSeeder extends Seeder
{
    use WithoutModelEvents, MediaRegistrationTrait;

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

        return null;
    }

    /**
     * Создать placeholder изображение
     */
    private function createPlaceholderImage(string $targetPath, int $width = 510, int $height = 290): bool
    {
        try {
            // Создаем директорию если не существует
            $targetDir = dirname($targetPath);
            if (!File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }

            // Создаем изображение
            $image = imagecreatetruecolor($width, $height);
            
            // Цвета
            $bgColor = imagecolorallocate($image, 108, 123, 109); // #6C7B6D
            $textColor = imagecolorallocate($image, 255, 255, 255);
            
            // Заливаем фон
            imagefill($image, 0, 0, $bgColor);
            
            // Добавляем текст
            $text = 'How Work Block';
            $fontSize = 5;
            $textX = ($width - imagefontwidth($fontSize) * strlen($text)) / 2;
            $textY = ($height - imagefontheight($fontSize)) / 2;
            imagestring($image, $fontSize, $textX, $textY, $text, $textColor);
            
            // Сохраняем как PNG
            $result = imagepng($image, $targetPath);
            imagedestroy($image);
            
            return $result;
        } catch (\Exception $e) {
            $this->command->error("Ошибка создания placeholder: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Скопировать изображение из старого проекта или создать placeholder
     */
    private function copyImage(string $sourceFile, string $targetPath): bool
    {
        // Проверяем наличие файла в текущем проекте
        $localPath = public_path($sourceFile);
        if (File::exists($localPath)) {
            $targetDir = dirname(public_path($targetPath));
            if (!File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }
            return File::copy($localPath, public_path($targetPath));
        }

        // Пробуем найти в старом проекте
        $oldProjectPath = $this->getOldProjectPath();
        if ($oldProjectPath) {
            $oldImagePath = $oldProjectPath . '/' . $sourceFile;
            if (File::exists($oldImagePath)) {
                $targetDir = dirname(public_path($targetPath));
                if (!File::exists($targetDir)) {
                    File::makeDirectory($targetDir, 0755, true);
                }
                return File::copy($oldImagePath, public_path($targetPath));
            }
        }

        // Если файл не найден, создаем placeholder
        $this->command->warn("Изображение не найдено: {$sourceFile}. Создаю placeholder...");
        return $this->createPlaceholderImage(public_path($targetPath));
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Начало создания настроек блока HowWork...');

        // Проверяем, существует ли уже запись
        $existing = HowWorkBlockSettings::first();
        if ($existing) {
            $this->command->warn('Настройки блока HowWork уже существуют.');
            
            // Обновляем изображение, если его нет
            if (!$existing->image || !File::exists(public_path($existing->image))) {
                $this->command->info('Обновляю изображение...');
                $imageCopied = $this->copyImage($imageSource, $imageTarget);
                
                if ($imageCopied) {
                    $existing->update(['image' => $imageTarget]);
                    $this->command->info("✓ Изображение обновлено: {$imageTarget}");
                }
            } else {
                $this->command->info('Изображение уже настроено.');
            }
            
            return;
        }

        // Путь к изображению
        $imageSource = 'img/delete/image-11.png'; // Из оригинального settings.js
        $imageTarget = 'upload/how-work/image-11.png';

        // Копируем изображение
        $this->command->info('Копирование изображения...');
        $imageCopied = $this->copyImage($imageSource, $imageTarget);
        
        if ($imageCopied) {
            $this->command->info("✓ Изображение скопировано: {$imageTarget}");
        } else {
            $this->command->error("✗ Не удалось скопировать изображение");
        }

        // Регистрируем изображение в медиа-библиотеке
        $media = null;
        if ($imageCopied && File::exists(public_path($imageTarget))) {
            $media = $this->registerMediaByPath($imageTarget, 'how-work');
            if ($media) {
                $this->command->info("✓ Запись в медиа-библиотеке создана (ID: {$media->id})");
            }
        }

        // Данные для блока HowWork (из модели и оригинального файла)
        $steps = [
            [
                'point' => 'disc',
                'title' => 'Вы оставляете заявку',
                'description' => 'занимает не более <br>1-ой минуты',
            ],
            [
                'point' => 'disc',
                'title' => 'Мы подбираем участок <br>и проверяем документы',
                'description' => null,
            ],
            [
                'point' => 'disc',
                'title' => 'Готовим и согласовываем <br>ИРД, ТУ, ВРИ',
                'description' => null,
            ],
            [
                'point' => 'disc',
                'title' => 'Представляем ваши интересы <br>в госструктурах',
                'description' => null,
            ],
            [
                'point' => 'star',
                'title' => 'Вы получаете участок, готовый <br> к реализации проекта',
                'description' => 'Берём всё на себя — вы получаете<br> результат без бюрократии',
            ],
        ];

        // Создаем настройки блока
        $settings = HowWorkBlockSettings::create([
            'title' => 'Как мы работаем',
            'subtitle' => null,
            'image' => $imageCopied ? $imageTarget : null,
            'image_alt' => 'Как мы работаем',
            'button_text' => 'Заказать обратный звонок',
            'button_type' => 'url',
            'button_value' => '',
            'is_active' => true,
            'steps' => $steps,
            'additional_settings' => null,
        ]);

        $this->command->info("✓ Настройки блока HowWork созданы (ID: {$settings->id})");
        $this->command->info('Готово! Блок HowWork успешно создан.');
    }
}
