<?php

namespace Database\Seeders;

use App\Models\Folder;
use App\Models\Media;
use App\Models\ProjectCase;
use Database\Seeders\Traits\MediaRegistrationTrait;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CasesSeeder extends Seeder
{
    use WithoutModelEvents, MediaRegistrationTrait;

    /**
     * Скопировать изображение из локальных ресурсов
     */
    private function copyImage(string $sourcePath, string $targetPath): bool
    {
        // Сначала проверяем локальные файлы в текущем проекте
        $localSourcePath = public_path($sourcePath);
        if (File::exists($localSourcePath)) {
            $targetDir = dirname(public_path($targetPath));
            if (!File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }
            $copied = File::copy($localSourcePath, public_path($targetPath));
            if ($copied) {
                $this->command->info("  ✓ Скопировано из локальных ресурсов: {$sourcePath}");
            }
            return $copied;
        }

        $this->command->warn("  ⚠️ Изображение не найдено в локальных ресурсах: {$sourcePath}");
        return false;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Начало создания кейсов...');

        // Создаем директорию для изображений кейсов
        $targetDir = public_path('upload/cases');
        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
            $this->command->info("✓ Создана директория: upload/cases");
        }

        // Данные кейсов из старого проекта
        $casesData = [
            [
                'name' => 'Запуск индустриального парка «Арктика»',
                'slug' => 'zapusk-industrialnogo-parka-arktika',
                'description' => [
                    'short' => 'Комплексное сопровождение от изменения ВРИ до выведения резидентов.',
                    'full' => 'Полное сопровождение проекта по запуску индустриального парка: от изменения вида разрешенного использования земельного участка до привлечения резидентов и запуска производства.',
                ],
                'html' => [
                    'lead' => '<p>Комплексная работа команды MNKA по созданию индустриального парка федерального значения.</p>',
                    'content' => '<p>Проект включал согласование документации, изменение ВРИ, получение всех необходимых разрешений и привлечение инвесторов.</p>',
                ],
                'images' => [
                    'main' => 'img/services/5.png',
                    'gallery' => [
                        'img/services/5.png',
                        'img/services/6.png',
                        'img/services/1.png',
                    ],
                ],
                'icon' => 'img/system/2.svg',
                'order' => 1,
            ],
            [
                'name' => 'Логистический хаб федерального уровня',
                'slug' => 'logisticheskij-hab-federalnogo-urovnya',
                'description' => [
                    'short' => 'Создание мультимодального узла с подключением к железной дороге.',
                    'full' => 'Разработка и реализация проекта логистического хаба с интеграцией в транспортную инфраструктуру региона.',
                ],
                'html' => [
                    'lead' => '<p>Масштабный проект по созданию мультимодального логистического центра.</p>',
                    'content' => '<p>Проект включал подключение к железнодорожной инфраструктуре, разработку схемы движения, получение всех необходимых согласований.</p>',
                ],
                'images' => [
                    'main' => 'img/services/6.png',
                    'gallery' => [
                        'img/services/6.png',
                        'img/services/5.png',
                        'img/services/2.png',
                    ],
                ],
                'icon' => 'img/system/3.svg',
                'order' => 2,
            ],
        ];

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($casesData as $caseData) {
            $this->command->info("📋 Обработка кейса: {$caseData['name']}");

            // Копируем и регистрируем основное изображение
            $mainImagePath = $caseData['images']['main'];
            $mainImageFileName = basename($mainImagePath);
            $targetMainImagePath = "upload/cases/{$mainImageFileName}";
            
            $mainImageCopied = $this->copyImage($mainImagePath, $targetMainImagePath);
            $mainImageMedia = null;
            
            if ($mainImageCopied) {
                $mainImageMedia = $this->registerMediaByPath($targetMainImagePath, 'cases');
                if ($mainImageMedia) {
                    $this->command->info("  ✓ Основное изображение зарегистрировано (ID: {$mainImageMedia->id})");
                }
            }

            // Копируем и регистрируем иконку
            $iconPath = $caseData['icon'];
            $iconFileName = basename($iconPath);
            $targetIconPath = "upload/cases/{$iconFileName}";
            
            $iconCopied = $this->copyImage($iconPath, $targetIconPath);
            $iconMedia = null;
            
            if ($iconCopied) {
                $iconMedia = $this->registerMediaByPath($targetIconPath, 'cases');
                if ($iconMedia) {
                    $this->command->info("  ✓ Иконка зарегистрирована (ID: {$iconMedia->id})");
                }
            }

            // Копируем и регистрируем изображения галереи
            $galleryMediaIds = [];
            $galleryIndex = 0;
            foreach ($caseData['images']['gallery'] as $galleryImagePath) {
                $galleryImageFileName = basename($galleryImagePath);
                
                // Если это основное изображение, используем его напрямую
                if ($galleryImagePath === $mainImagePath && $mainImageMedia) {
                    $galleryMediaIds[] = $mainImageMedia->id;
                    continue;
                }
                
                // Создаем уникальное имя файла для галереи, если нужно
                $uniqueFileName = $galleryImageFileName;
                if ($galleryImagePath === $mainImagePath) {
                    $pathInfo = pathinfo($galleryImageFileName);
                    $uniqueFileName = $pathInfo['filename'] . '_gallery_' . ($galleryIndex + 1) . '.' . $pathInfo['extension'];
                }
                
                $targetGalleryImagePath = "upload/cases/{$uniqueFileName}";
                
                $galleryImageCopied = $this->copyImage($galleryImagePath, $targetGalleryImagePath);
                if ($galleryImageCopied) {
                    $galleryMedia = $this->registerMediaByPath($targetGalleryImagePath, 'cases');
                    if ($galleryMedia) {
                        $galleryMediaIds[] = $galleryMedia->id;
                        $galleryIndex++;
                        $this->command->info("  ✓ Изображение галереи зарегистрировано (ID: {$galleryMedia->id})");
                    }
                }
            }

            // Создаем или обновляем кейс
            $case = ProjectCase::updateOrCreate(
                ['slug' => $caseData['slug']],
                [
                    'name' => $caseData['name'],
                    'description' => $caseData['description'],
                    'html' => $caseData['html'],
                    'image_id' => $mainImageMedia ? $mainImageMedia->id : null,
                    'icon_id' => $iconMedia ? $iconMedia->id : null,
                    'is_active' => true,
                    'order' => $caseData['order'],
                ]
            );

            // Синхронизируем изображения галереи
            if (!empty($galleryMediaIds)) {
                $pivotData = [];
                foreach ($galleryMediaIds as $index => $mediaId) {
                    $pivotData[$mediaId] = ['order' => $index + 1];
                }
                $case->images()->sync($pivotData);
            }

            if ($case->wasRecentlyCreated) {
                $createdCount++;
                $this->command->info("  ✅ Кейс создан (ID: {$case->id})");
            } else {
                $updatedCount++;
                $this->command->info("  ✅ Кейс обновлен (ID: {$case->id})");
            }
        }

        $this->command->info("✅ Создано кейсов: {$createdCount}");
        $this->command->info("✅ Обновлено кейсов: {$updatedCount}");
        $this->command->info('🎉 Кейсы успешно созданы!');
    }
}

