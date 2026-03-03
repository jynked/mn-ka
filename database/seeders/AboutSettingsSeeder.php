<?php

namespace Database\Seeders;

use App\Models\AboutSettings;
use App\Models\Media;
use Database\Seeders\Traits\MediaRegistrationTrait;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class AboutSettingsSeeder extends Seeder
{
    use MediaRegistrationTrait;
    protected $oldProjectPath;

    public function __construct()
    {
        $this->oldProjectPath = env('OLD_PROJECT_PATH', 'C:\OSPanel\domains\lagom');
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Начало создания настроек страницы "О нас"...');

        $existing = AboutSettings::first();
        
        if ($existing && $this->hasData($existing)) {
            $this->command->warn('Настройки страницы "О нас" уже существуют и содержат данные.');
            
            // Проверяем и обновляем изображения, если их нет
            $this->updateMissingImages($existing);
            
            return;
        }

        // Создаем или обновляем настройки
        $settings = $existing ?: new AboutSettings();
        
        // Создаем/получаем изображения для статистики
        $statistics = [
            [
                'icon' => $this->copyOrCreateIcon('system/1.svg', '1.svg'),
                'text' => '93% клиентов приходят по рекомендации',
            ],
            [
                'icon' => $this->copyOrCreateIcon('system/2.svg', '2.svg'),
                'text' => '250 + реализованных кейсов',
            ],
            [
                'icon' => $this->copyOrCreateIcon('system/3.svg', '3.svg'),
                'text' => '15+ лет опыта',
            ],
        ];

        // Создаем/получаем изображения для клиентов
        $clients = [
            [
                'title' => 'Девелоперам и застройщикам',
                'description' => 'Помогаем с подбором участков и оформлением всей документации',
                'icon' => $this->copyOrCreateIcon('system/4.svg', '4.svg'),
            ],
            [
                'title' => 'Инвесторам',
                'description' => 'Консультируем по инвестиционной привлекательности участков',
                'icon' => $this->copyOrCreateIcon('system/5.svg', '5.svg'),
            ],
            [
                'title' => 'Частным владельцам участков',
                'description' => 'Оформляем документы для строительства и смены назначения',
                'icon' => $this->copyOrCreateIcon('system/6.svg', '6.svg'),
            ],
            [
                'title' => 'Производственным компаниям',
                'description' => 'Подбираем участки под производство и логистику',
                'icon' => $this->copyOrCreateIcon('system/7.svg', '7.svg'),
            ],
            [
                'title' => 'Сетям и Брендам',
                'description' => 'Находим локации для коммерческой недвижимости',
                'icon' => $this->copyOrCreateIcon('system/8.svg', '8.svg'),
            ],
            [
                'title' => 'Муниципалитетам',
                'description' => 'Консультируем по вопросам земельных отношений',
                'icon' => $this->copyOrCreateIcon('system/9.svg', '9.svg'),
            ],
        ];

        // Создаем/получаем изображения для команды
        $team = [
            [
                'name' => 'Зубенко Михаил Петрович',
                'position' => 'CEO МНКА',
                'photo' => $this->copyOrCreatePhoto('team/1.jpg', '1.jpg'),
            ],
            [
                'name' => 'Иванов Иван Иванович',
                'position' => 'Руководитель',
                'photo' => $this->copyOrCreatePhoto('team/2.jpg', '2.jpg'),
            ],
            [
                'name' => 'Петров Петр Петрович',
                'position' => 'Просто водитель',
                'photo' => $this->copyOrCreatePhoto('team/3.jpg', '3.jpg'),
            ],
            [
                'name' => 'Сидоров Сидор Сидорович',
                'position' => 'Председатель правления',
                'photo' => $this->copyOrCreatePhoto('team/4.jpg', '4.jpg'),
            ],
        ];

        // Создаем преимущества
        $benefits = [
            [
                'title' => '500+ участков в базе',
                'description' => 'Большой выбор готовых предложений',
            ],
            [
                'title' => 'Договора с риелтором и инвесторами',
                'description' => 'Прозрачное сотрудничество',
            ],
            [
                'title' => 'Работаем по всей России',
                'description' => 'Присутствие во всех регионах',
            ],
            [
                'title' => 'Профессиональные кадастровые и юристы',
                'description' => 'Команда экспертов',
            ],
            [
                'title' => 'Гарантия результата в договоре',
                'description' => 'Юридическая защита',
            ],
            [
                'title' => 'Бесплатная консультация',
                'description' => 'Первичный анализ без оплаты',
            ],
        ];

        // Обновляем настройки
        $settings->statistics = $statistics;
        $settings->clients = $clients;
        $settings->team = $team;
        $settings->benefits = $benefits;
        $settings->description = '<p>Мы — команда профессионалов с многолетним опытом работы в сфере земельных отношений. Наша миссия — помочь вам найти идеальный участок и оформить все необходимые документы.</p>';
        
        $settings->save();

        $this->command->info('✅ Настройки страницы "О нас" успешно созданы!');
    }

    /**
     * Проверить, есть ли данные в настройках
     */
    protected function hasData(AboutSettings $settings): bool
    {
        return !empty($settings->statistics) || 
               !empty($settings->clients) || 
               !empty($settings->team) || 
               !empty($settings->benefits);
    }

    /**
     * Обновить отсутствующие изображения
     */
    protected function updateMissingImages(AboutSettings $settings): void
    {
        $this->command->info('Проверка и обновление изображений...');
        
        $updated = false;

        // Проверяем статистику
        if (!empty($settings->statistics)) {
            $statistics = $settings->statistics; // Получаем копию массива
            foreach ($statistics as $key => $stat) {
                if (!empty($stat['icon']) && !$this->fileExists($stat['icon'])) {
                    $statistics[$key]['icon'] = $this->copyOrCreateIcon('system/' . basename($stat['icon']), basename($stat['icon']));
                    $updated = true;
                }
            }
            if ($updated) {
                $settings->statistics = $statistics; // Присваиваем измененный массив
                $updated = true;
            }
        }

        // Проверяем клиентов
        if (!empty($settings->clients)) {
            $clients = $settings->clients; // Получаем копию массива
            $clientsUpdated = false;
            foreach ($clients as $key => $client) {
                if (!empty($client['icon']) && !$this->fileExists($client['icon'])) {
                    $clients[$key]['icon'] = $this->copyOrCreateIcon('system/' . basename($client['icon']), basename($client['icon']));
                    $clientsUpdated = true;
                }
            }
            if ($clientsUpdated) {
                $settings->clients = $clients; // Присваиваем измененный массив
                $updated = true;
            }
        }

        // Проверяем команду
        if (!empty($settings->team)) {
            $team = $settings->team; // Получаем копию массива
            $teamUpdated = false;
            foreach ($team as $key => $member) {
                if (!empty($member['photo']) && !$this->fileExists($member['photo'])) {
                    $team[$key]['photo'] = $this->copyOrCreatePhoto('team/' . basename($member['photo']), basename($member['photo']));
                    $teamUpdated = true;
                }
            }
            if ($teamUpdated) {
                $settings->team = $team; // Присваиваем измененный массив
                $updated = true;
            }
        }

        if ($updated) {
            $settings->save();
            $this->command->info('✓ Изображения обновлены');
        } else {
            $this->command->info('Все изображения на месте');
        }
    }

    /**
     * Скопировать или создать иконку
     */
    protected function copyOrCreateIcon(string $sourcePath, string $fileName): string
    {
        $targetPath = "img/system/{$fileName}";
        $fullTargetPath = public_path($targetPath);

        // Создаем директорию, если её нет
        $dir = dirname($fullTargetPath);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        // Если файл уже существует, возвращаем путь
        if (File::exists($fullTargetPath)) {
            return $targetPath;
        }

        // Пытаемся скопировать из старого проекта
        $oldPath = $this->oldProjectPath . '/public/' . $sourcePath;
        if (File::exists($oldPath)) {
            File::copy($oldPath, $fullTargetPath);
            $this->command->info("✓ Скопирована иконка: {$targetPath}");
            return $targetPath;
        }

        // Создаем placeholder SVG
        $this->createPlaceholderSvg($fullTargetPath, $fileName);
        $this->command->info("✓ Создан placeholder для иконки: {$targetPath}");
        
        return $targetPath;
    }

    /**
     * Скопировать или создать фото
     */
    protected function copyOrCreatePhoto(string $sourcePath, string $fileName): string
    {
        $targetPath = "img/team/{$fileName}";
        $fullTargetPath = public_path($targetPath);

        // Создаем директорию, если её нет
        $dir = dirname($fullTargetPath);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        // Если файл уже существует, возвращаем путь
        if (File::exists($fullTargetPath)) {
            return $targetPath;
        }

        // Пытаемся скопировать из старого проекта
        $oldPath = $this->oldProjectPath . '/public/' . $sourcePath;
        if (File::exists($oldPath)) {
            File::copy($oldPath, $fullTargetPath);
            $this->command->info("✓ Скопировано фото: {$targetPath}");
            return $targetPath;
        }

        // Создаем placeholder изображение
        $this->createPlaceholderImage($fullTargetPath, 245, 272);
        $this->command->info("✓ Создан placeholder для фото: {$targetPath}");
        
        return $targetPath;
    }

    /**
     * Создать placeholder SVG иконку
     */
    protected function createPlaceholderSvg(string $path, string $fileName): void
    {
        $svg = <<<SVG
<svg width="58" height="49" viewBox="0 0 58 49" fill="none" xmlns="http://www.w3.org/2000/svg">
    <rect width="58" height="49" rx="4" fill="#688E67"/>
    <text x="29" y="28" font-family="Arial" font-size="14" fill="white" text-anchor="middle">{$fileName}</text>
</svg>
SVG;
        
        File::put($path, $svg);
        
        // Регистрируем в медиа библиотеке
        $this->registerMedia($path, basename($path), 'svg', 'photo');
    }

    /**
     * Создать placeholder изображение
     */
    protected function createPlaceholderImage(string $path, int $width = 245, int $height = 272): bool
    {
        if (!function_exists('imagecreatetruecolor')) {
            return false;
        }

        $image = imagecreatetruecolor($width, $height);
        $bgColor = imagecolorallocate($image, 244, 246, 252); // #F4F6FC
        $textColor = imagecolorallocate($image, 66, 68, 72); // #424448

        imagefill($image, 0, 0, $bgColor);

        $text = basename($path, '.jpg');
        $fontSize = 5;
        $textX = ($width - imagefontwidth($fontSize) * strlen($text)) / 2;
        $textY = ($height - imagefontheight($fontSize)) / 2;
        imagestring($image, $fontSize, $textX, $textY, $text, $textColor);

        $result = imagejpeg($image, $path, 85);
        imagedestroy($image);

        if ($result) {
            // Регистрируем в медиа библиотеке
            $this->registerMedia($path, basename($path), 'jpg', 'photo', $width, $height);
        }

        return $result;
    }

    /**
     * Проверить существование файла
     */
    protected function fileExists(string $path): bool
    {
        $fullPath = public_path($path);
        return File::exists($fullPath);
    }

    /**
     * Зарегистрировать файл в медиа библиотеке (обертка для совместимости)
     */
    protected function registerMedia(string $fullPath, string $fileName, string $extension, string $type, ?int $width = null, ?int $height = null): void
    {
        // Определяем категорию по пути
        $category = 'about';
        if (strpos($fullPath, 'system') !== false || strpos($fullPath, 'img/system') !== false) {
            $category = 'system';
        } elseif (strpos($fullPath, 'team') !== false || strpos($fullPath, 'img/team') !== false) {
            $category = 'team';
        }
        
        // Используем метод из trait
        $this->registerMediaFile($fullPath, $fileName, $category, $width, $height);
    }
}

