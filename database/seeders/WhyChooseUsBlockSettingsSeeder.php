<?php

namespace Database\Seeders;

use App\Models\Folder;
use App\Models\Media;
use App\Models\WhyChooseUsBlockSettings;
use Database\Seeders\Traits\MediaRegistrationTrait;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class WhyChooseUsBlockSettingsSeeder extends Seeder
{
    use WithoutModelEvents, MediaRegistrationTrait;

    /**
     * Скопировать изображение из локальных ресурсов
     */
    private function copyImage(string $sourceFileName, string $targetPath): bool
    {
        // Проверяем наличие файла в текущем проекте
        $localPath = public_path("img/delete/{$sourceFileName}");
        if (File::exists($localPath)) {
            $targetDir = dirname(public_path($targetPath));
            if (!File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }
            $copied = File::copy($localPath, public_path($targetPath));
            if ($copied) {
                $this->command->info("  ✓ Скопировано из локальных ресурсов: {$sourceFileName}");
            }
            return $copied;
        }

        // Если файл не найден, создаем директорию и выводим предупреждение
        $targetDir = dirname(public_path($targetPath));
        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }
        
        $this->command->warn("⚠️ Изображение не найдено в локальных ресурсах: {$sourceFileName}");
        return false;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Начало создания настроек блока "Почему выбирают нас"...');

        // Проверяем, существует ли уже запись
        $existing = WhyChooseUsBlockSettings::first();
        
        if ($existing) {
            $existingItems = $existing->items ?? [];
            // Проверяем, есть ли уже карточки с данными
            $hasRealItems = false;
            if (is_array($existingItems) && !empty($existingItems)) {
                // Проверяем, есть ли хотя бы одна карточка с текстом
                foreach ($existingItems as $item) {
                    if (isset($item['text']) && !empty(trim(strip_tags($item['text'])))) {
                        $hasRealItems = true;
                        break;
                    }
                }
            }
            
            if ($hasRealItems) {
                $this->command->warn('⚠️ Настройки блока "Почему выбирают нас" уже существуют с карточками.');
                $this->command->info('ℹ️ Для обновления карточек удалите существующую запись из базы данных.');
                $this->command->info('   Или выполните: DELETE FROM why_choose_us_block_settings;');
                return;
            } else {
                $this->command->info('ℹ️ Найдена запись без карточек. Добавляем карточки...');
            }
        }

        // Данные карточек из исходного файла (сохраняем HTML теги для форматирования)
        $itemsData = [
            [
                'text' => '500+ участков <br>в базе',
                'source_image' => 'ch-1.png',
                'col' => 3, // Bootstrap col-md-3
                'bg' => 'card-blue',
            ],
            [
                'text' => 'Договора с ритейлом <br>и инвесторами',
                'source_image' => 'ch-2.png',
                'col' => 6, // Bootstrap col-md-6
                'bg' => 'card-blue',
            ],
            [
                'text' => 'Работаем по <br>всей России',
                'source_image' => 'ch-3.png',
                'col' => 3, // Bootstrap col-md-3
                'bg' => 'card-blue',
            ],
            [
                'text' => 'Профессиональные <br>кадастровые и юристы',
                'source_image' => 'ch-4.png',
                'col' => 6, // Bootstrap col-md-6
                'bg' => 'card-blue',
            ],
            [
                'text' => 'Гарантия результата <br>в договоре',
                'source_image' => 'ch-5.png',
                'col' => 6, // Bootstrap col-md-6
                'bg' => 'card-blue',
            ],
            [
                'text' => 'Аккредитивная форма расчета',
                'source_image' => 'ch-6.png',
                'col' => 12, // Bootstrap col-md-12
                'bg' => 'card-green',
            ],
        ];

        // Создаем директорию для изображений блока
        $targetDir = public_path('upload/why-choose-us');
        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
            $this->command->info("✓ Создана директория: upload/why-choose-us");
        }

        $items = [];
        $mediaIds = [];

        // Копируем и регистрируем изображения
        foreach ($itemsData as $index => $itemData) {
            $sourceImage = $itemData['source_image'];
            $targetImage = "upload/why-choose-us/{$sourceImage}";
            
            $this->command->info("📋 Обработка карточки " . ($index + 1) . ": {$itemData['text']}");
            
            // Копируем изображение
            $imageCopied = $this->copyImage($sourceImage, $targetImage);
            
            if ($imageCopied) {
                $this->command->info("  ✓ Изображение скопировано: {$targetImage}");
                
                // Регистрируем в media библиотеке
                $media = $this->registerMediaByPath($targetImage, 'why-choose-us');
                
                if ($media) {
                    $this->command->info("  ✓ Зарегистрировано в media (ID: {$media->id})");
                    $mediaIds[] = $media->id;
                    
                    // Добавляем карточку с icon_id и дополнительными свойствами
                    $items[] = [
                        'text' => $itemData['text'],
                        'icon_id' => $media->id,
                        'col' => $itemData['col'] ?? 3,
                        'bg' => $itemData['bg'] ?? 'card-blue',
                    ];
                } else {
                    $this->command->warn("  ⚠️ Не удалось зарегистрировать в media");
                    // Добавляем карточку без icon_id, но с другими свойствами
                    $items[] = [
                        'text' => $itemData['text'],
                        'icon_id' => null,
                        'col' => $itemData['col'] ?? 3,
                        'bg' => $itemData['bg'] ?? 'card-blue',
                    ];
                }
            } else {
                $this->command->warn("  ⚠️ Не удалось скопировать изображение");
                // Добавляем карточку без icon_id, но с другими свойствами
                $items[] = [
                    'text' => $itemData['text'],
                    'icon_id' => null,
                    'col' => $itemData['col'] ?? 3,
                    'bg' => $itemData['bg'] ?? 'card-blue',
                ];
            }
        }

        // Создаем или обновляем настройки блока
        if ($existing) {
            // Обновляем существующую запись
            $existing->update([
                'items' => $items,
            ]);
            $settings = $existing;
            $this->command->info("✅ Настройки блока 'Почему выбирают нас' обновлены (ID: {$settings->id})");
        } else {
            // Создаем новую запись
            $settings = WhyChooseUsBlockSettings::create([
                'title' => 'Почему выбирают нас',
                'is_active' => true,
                'items' => $items,
                'additional_settings' => [],
            ]);
            $this->command->info("✅ Настройки блока 'Почему выбирают нас' созданы (ID: {$settings->id})");
        }

        $this->command->info("✅ Карточек добавлено: " . count($items));
        $this->command->info("✅ Зарегистрировано изображений в media: " . count($mediaIds));
        $this->command->info('🎉 Блок "Почему выбирают нас" успешно создан!');
    }
}

