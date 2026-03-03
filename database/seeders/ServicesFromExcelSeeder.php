<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Service;
use App\Models\Chapter;
use App\Models\ProjectCase;
use App\Models\Media;
use App\Models\Folder;
use App\Services\MediaImportService;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ServicesFromExcelSeeder extends Seeder
{
    private MediaImportService $mediaService;
    private ?int $servicesFolderId = null;
    private ?int $iconsFolderId = null;
    private array $importedServiceSlugs = [];

    public function __construct()
    {
        $this->mediaService = new MediaImportService();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Очистка всех существующих услуг перед импортом
        $this->command->warn("Очистка всех существующих услуг...");
        $this->clearExistingServices();
        
        // Получаем путь к файлу из .env или используем стандартные пути
        $excelPath = env('SERVICES_EXCEL_PATH') ?? $this->findExcelFile();
        
        // Для локальной разработки - проверяем стандартный путь Windows
        if (!$excelPath || !file_exists($excelPath)) {
            $windowsPath = 'C:\Users\dsc-2\Downloads\Telegram Desktop\feed.xlsx';
            if (file_exists($windowsPath)) {
                $excelPath = $windowsPath;
            }
        }
        
        if (!$excelPath || !file_exists($excelPath)) {
            $this->command->error("Файл не найден!");
            $this->command->info("Укажите путь к файлу через .env (SERVICES_EXCEL_PATH)");
            $this->command->info("Или поместите файл feed.xlsx в одну из стандартных директорий:");
            $this->command->info("  - " . base_path('feed.xlsx'));
            $this->command->info("  - " . base_path('storage/app/feed.xlsx'));
            $this->command->info("  - C:\\Users\\dsc-2\\Downloads\\Telegram Desktop\\feed.xlsx (локально)");
            return;
        }
        
        $this->command->info("Используется файл: {$excelPath}");

        // Подготовка папок для медиа
        $this->prepareMediaFolders();
        
        // Находим резервные изображения
        $this->command->info("Поиск резервных изображений в медиа-библиотеке...");
        $fallbackImage = Media::where('type', 'photo')->first();
        $fallbackIcon = Media::where('type', 'photo')->first();
        
        if ($fallbackImage) {
            $this->command->info("  Резервное изображение: {$fallbackImage->original_name}");
        }
        if ($fallbackIcon) {
            $this->command->info("  Резервная иконка: {$fallbackIcon->original_name}");
        }

        $this->command->info("Чтение Excel файла...");
        
        try {
            $spreadsheet = IOFactory::load($excelPath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Пропускаем заголовок (первая строка)
            array_shift($rows);
            
            $this->command->info("Найдено строк: " . count($rows));
            
            $currentService = null;
            $currentChapter = null;
            $currentChapterId = null;
            $order = 0;
            $chapterOrder = 0;
            $caseOrder = 0;
            
            // Храним связи: услуга -> разделы -> случаи
            $serviceChaptersMap = []; // [service_slug => [chapter_ids]]
            $chapterCasesMap = []; // [chapter_id => [case_ids]]
            
            $stats = [
                'services' => 0,
                'chapters' => 0,
                'cases' => 0,
                'service_case_links' => 0,
            ];
            
            foreach ($rows as $rowIndex => $row) {
                // Обрабатываем значения, заменяя NaN и пустые строки
                $serviceName = $this->cleanValue($row[0] ?? '');
                $chapterName = $this->cleanValue($row[1] ?? '');
                $caseName = $this->cleanValue($row[2] ?? '');
                $description = $this->cleanValue($row[3] ?? '');
                $htmlText = $this->cleanValue($row[4] ?? '');
                $detailedText = $this->cleanValue($row[5] ?? '');
                
                // Если есть название услуги - создаем новую услугу
                if (!empty($serviceName) && $serviceName !== 'NaN') {
                    $currentService = $this->createOrUpdateService(
                        $serviceName, 
                        $description, 
                        $htmlText, 
                        $detailedText, 
                        $order,
                        $fallbackImage?->id,
                        $fallbackIcon?->id
                    );
                    $order++;
                    $stats['services']++;
                    $this->command->info("✓ Услуга: {$serviceName}");
                }
                
                // Если есть название раздела - создаем/находим раздел
                if (!empty($chapterName) && $chapterName !== 'NaN') {
                    $currentChapter = $this->createOrUpdateChapter($chapterName, $chapterOrder);
                    $currentChapterId = $currentChapter->id;
                    $chapterOrder++;
                    $caseOrder = 0; // Сбрасываем порядок случаев для нового раздела
                    $stats['chapters']++;
                    $this->command->info("  → Раздел: {$chapterName}");
                    
                    // Связываем текущую услугу с разделом
                    if ($currentService) {
                        $serviceSlug = $currentService->slug;
                        if (!isset($serviceChaptersMap[$serviceSlug])) {
                            $serviceChaptersMap[$serviceSlug] = [];
                        }
                        if (!in_array($currentChapterId, $serviceChaptersMap[$serviceSlug])) {
                            $serviceChaptersMap[$serviceSlug][] = $currentChapterId;
                        }
                        
                        // Обновляем chapter_id услуги, если он еще не установлен
                        if (!$currentService->chapter_id) {
                            $currentService->chapter_id = $currentChapterId;
                            $currentService->save();
                        }
                    }
                }
                
                // Если есть название случая - создаем случай и связываем с разделом
                if (!empty($caseName) && $caseName !== 'NaN' && $currentChapterId) {
                    $projectCase = $this->createOrUpdateCase($caseName, $currentChapterId, $description, $htmlText, $detailedText, $caseOrder);
                    $caseOrder++;
                    $stats['cases']++;
                    $this->command->info("    • Случай: {$caseName}");
                    
                    // Сохраняем связь раздела с случаем
                    if (!isset($chapterCasesMap[$currentChapterId])) {
                        $chapterCasesMap[$currentChapterId] = [];
                    }
                    $chapterCasesMap[$currentChapterId][] = $projectCase->id;
                }
            }
            
            // Связываем услуги со случаями через разделы
            $this->command->info("\nСвязывание услуг со случаями через разделы...");
            foreach ($serviceChaptersMap as $serviceSlug => $chapterIds) {
                $service = Service::where('slug', $serviceSlug)->first();
                if (!$service) {
                    continue;
                }
                
                $caseIds = [];
                foreach ($chapterIds as $chapterId) {
                    if (isset($chapterCasesMap[$chapterId])) {
                        $caseIds = array_merge($caseIds, $chapterCasesMap[$chapterId]);
                    }
                }
                
                if (!empty($caseIds)) {
                    // Удаляем старые связи
                    DB::table('cases_service')->where('service_id', $service->id)->delete();
                    
                    // Создаем новые связи
                    foreach (array_unique($caseIds) as $caseId) {
                        DB::table('cases_service')->insert([
                            'service_id' => $service->id,
                            'cases_id' => $caseId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $stats['service_case_links']++;
                    }
                    $this->command->info("  ✓ Услуга '{$service->name}' связана с " . count(array_unique($caseIds)) . " случаями");
                }
            }
            
            // Удаляем все услуги, которых нет в Excel
            $this->command->info("\nУдаление услуг, отсутствующих в Excel...");
            $deletedCount = Service::whereNotIn('slug', $this->importedServiceSlugs)->delete();
            $this->command->info("  Удалено услуг: {$deletedCount}");
            
            $this->command->info("\n📊 Статистика импорта:");
            $this->command->info("  Услуг: {$stats['services']}");
            $this->command->info("  Разделов: {$stats['chapters']}");
            $this->command->info("  Случаев: {$stats['cases']}");
            $this->command->info("  Связей услуга-случай: {$stats['service_case_links']}");
            
            $this->command->info("\n✅ Импорт завершен успешно!");
            
        } catch (\Exception $e) {
            $this->command->error("Ошибка при импорте: " . $e->getMessage());
            $this->command->error($e->getTraceAsString());
        }
    }
    
    /**
     * Очистить все существующие услуги
     */
    private function clearExistingServices(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Удаляем связи
        DB::table('product_service')->truncate();
        DB::table('cases_service')->truncate();
        
        // Удаляем случаи
        ProjectCase::truncate();
        
        // Удаляем услуги
        Service::truncate();
        
        // Удаляем разделы
        Chapter::truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info("✅ Все услуги, разделы и случаи очищены");
    }
    
    /**
     * Подготовить папки для медиа
     */
    private function prepareMediaFolders(): void
    {
        // Находим или создаем папку "Услуги"
        $servicesFolder = Folder::firstOrCreate(
            ['slug' => 'services'],
            [
                'name' => 'Услуги',
                'slug' => 'services',
                'protected' => false,
            ]
        );
        $this->servicesFolderId = $servicesFolder->id;

        // Находим или создаем папку "Иконки"
        $iconsFolder = Folder::firstOrCreate(
            ['slug' => 'icons'],
            [
                'name' => 'Иконки',
                'slug' => 'icons',
                'protected' => false,
            ]
        );
        $this->iconsFolderId = $iconsFolder->id;
    }
    
    private function createOrUpdateService($name, $description, $htmlText, $detailedText, $order, $imageId = null, $iconId = null)
    {
        // Обрезаем название до 255 символов
        $name = mb_substr($name, 0, 255);
        
        $slug = Str::slug($name);
        
        // Обрезаем slug до 255 символов
        if (mb_strlen($slug) > 255) {
            $slug = mb_substr($slug, 0, 252) . '-' . substr(md5($name), 0, 2);
        }
        
        // Проверяем уникальность slug
        $counter = 1;
        $originalSlug = $slug;
        while (Service::where('slug', $slug)->exists()) {
            $slug = mb_substr($originalSlug, 0, 250) . '-' . $counter;
            $counter++;
        }
        
        // Сохраняем slug для последующего удаления лишних услуг
        $this->importedServiceSlugs[] = $slug;
        
        $descriptionData = [];
        if (!empty($description)) {
            $descriptionData['ru'] = $description;
        }
        if (!empty($detailedText)) {
            $descriptionData['detailed'] = $detailedText;
        }
        
        $service = Service::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'slug' => $slug,
                'description' => !empty($descriptionData) ? $descriptionData : null,
                'image_id' => $imageId,
                'icon_id' => $iconId,
                'order' => $order,
                'is_active' => true,
            ]
        );
        
        // Всегда обновляем html_content, если оно указано в Excel
        // Это гарантирует, что данные из Excel будут применены
        if ($htmlText !== null && $htmlText !== '') {
            $service->html_content = $htmlText;
            $service->save();
        }
        
        return $service;
    }
    
    private function createOrUpdateChapter($name, $order)
    {
        return Chapter::firstOrCreate(
            ['name' => $name],
            [
                'name' => $name,
                'order' => $order,
                'is_active' => true,
            ]
        );
    }
    
    private function createOrUpdateCase($name, $chapterId, $description, $htmlText, $detailedText, $order)
    {
        // Обрезаем название до 255 символов (стандартный размер VARCHAR)
        $name = mb_substr($name, 0, 255);
        
        $slug = Str::slug($name);
        
        // Обрезаем slug до 255 символов
        if (mb_strlen($slug) > 255) {
            $slug = mb_substr($slug, 0, 252) . '-' . substr(md5($name), 0, 2);
        }
        
        // Проверяем уникальность slug
        $counter = 1;
        $originalSlug = $slug;
        while (ProjectCase::where('slug', $slug)->exists()) {
            $slug = mb_substr($originalSlug, 0, 250) . '-' . $counter;
            $counter++;
        }
        
        $descriptionData = [];
        if (!empty($description)) {
            $descriptionData['ru'] = $description;
        }
        if (!empty($detailedText)) {
            $descriptionData['detailed'] = $detailedText;
        }
        
        $htmlData = null;
        if (!empty($htmlText)) {
            $htmlData = ['content' => $htmlText];
        }
        
        return ProjectCase::updateOrCreate(
            [
                'name' => $name,
                'chapter_id' => $chapterId,
            ],
            [
                'name' => $name,
                'slug' => $slug,
                'description' => !empty($descriptionData) ? $descriptionData : null,
                'html' => $htmlData,
                'chapter_id' => $chapterId,
                'order' => $order,
                'is_active' => true,
            ]
        );
    }
    
    /**
     * Найти файл Excel в стандартных местах
     */
    private function findExcelFile(): ?string
    {
        $possiblePaths = [
            base_path('feed.xlsx'),
            base_path('storage/app/feed.xlsx'),
            base_path('database/seeders/feed.xlsx'),
            '/home/d/dsc23ytp/stroy/public_html/feed.xlsx',
            '/home/d/dsc23ytp/stroy/public_html/storage/app/feed.xlsx',
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        return null;
    }
    
    /**
     * Очистить значение от NaN и пустых строк
     */
    private function cleanValue($value): string
    {
        if (is_null($value) || $value === 'NaN' || $value === 'nan' || (is_string($value) && strtolower(trim($value)) === 'nan')) {
            return '';
        }
        
        if (is_float($value) && is_nan($value)) {
            return '';
        }
        
        return trim((string)$value);
    }
}

