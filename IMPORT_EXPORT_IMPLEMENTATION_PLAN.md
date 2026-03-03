# План реализации системы импорта/экспорта для административной панели

## Обзор

Система импорта/экспорта позволит администраторам экспортировать и импортировать данные из разделов:
- `/admin/decisions/chapters` - Разделы
- `/admin/decisions/products` - Продукты
- `/admin/decisions/services` - Услуги
- `/admin/decisions/cases` - Случаи

## Текущее состояние

### Уже реализовано:
- ✅ `ProductsExport` - экспорт продуктов в ZIP
- ✅ `ProductsImport` - импорт продуктов из ZIP
- ✅ `ServicesExport` - экспорт услуг
- ✅ `ServicesImport` - импорт услуг
- ✅ `ZipImportService` - сервис для работы с ZIP архивами
- ✅ `MediaImportService` - сервис для импорта медиа файлов

### Требуется реализовать:
1. **Экспорт/Импорт для Chapters (Разделы)**
2. **Экспорт/Импорт для Cases (Случаи)**
3. **Единый экспорт/импорт всех разделов**
4. **UI кнопки в header админ панели**
5. **Улучшенная обработка ошибок с детальными уведомлениями**

---

## Структура ZIP архива

### Для отдельных разделов

#### 1. Chapters (Разделы)
```
chapters_YYYY-MM-DD_HHMMSS.zip
├── chapters.csv
└── images/
    └── (нет изображений для chapters)
```

**Структура chapters.csv:**
```csv
ID;Название;Порядок;Активен
1;Раздел 1;1;1
2;Раздел 2;2;1
```

#### 2. Products (Продукты)
```
products_YYYY-MM-DD_HHMMSS.zip
├── products.csv
└── images/
    ├── products/
    │   ├── image1.jpg
    │   └── image2.png
    └── icons/
        ├── icon1.svg
        └── icon2.png
```

**Структура products.csv:**
```csv
ID;Название;Slug;Описание;Раздел ID;Название раздела;SEO Title;SEO Description;SEO Keywords;ID изображения;Путь изображения;URL изображения;ID иконки;Путь иконки;URL иконки;Услуги (ID через запятую);Порядок;Активен
1;Продукт 1;product-1;{"blocks":[...]};;Раздел 1;Title;Description;Keywords;123;images/products/image1.jpg;/upload/image1.jpg;124;images/icons/icon1.svg;/upload/icon1.svg;1,2,3;1;1
```

#### 3. Services (Услуги)
```
services_YYYY-MM-DD_HHMMSS.zip
├── services.csv
└── images/
    ├── services/
    │   ├── service_image1.jpg
    │   └── service_image2.png
    └── icons/
        ├── service_icon1.svg
        └── service_icon2.png
```

**Структура services.csv:**
```csv
ID;Название;Slug;Описание;HTML контент;Раздел ID;Название раздела;SEO Title;SEO Description;SEO Keywords;ID изображения;Путь изображения;URL изображения;ID иконки;Путь иконки;URL иконки;Продукты (ID через запятую);Опции (ID через запятую);Деревья опций (ID через запятую);Экземпляры (ID через запятую);Порядок;Активен
1;Услуга 1;service-1;{"blocks":[...]};<div>...</div>;1;Раздел 1;Title;Description;Keywords;125;images/services/service1.jpg;/upload/service1.jpg;126;images/icons/icon1.svg;/upload/icon1.svg;1,2;3,4;5,6;7,8;1;1
```

#### 4. Cases (Случаи)
```
cases_YYYY-MM-DD_HHMMSS.zip
├── cases.csv
└── images/
    ├── cases/
    │   ├── case_main1.jpg
    │   └── case_main2.png
    ├── icons/
    │   ├── case_icon1.svg
    │   └── case_icon2.png
    └── gallery/
        ├── case1_gallery1.jpg
        ├── case1_gallery2.jpg
        └── case2_gallery1.jpg
```

**Структура cases.csv:**
```csv
ID;Название;Slug;Описание;HTML;Раздел ID;Название раздела;SEO Title;SEO Description;SEO Keywords;ID изображения;Путь изображения;URL изображения;ID иконки;Путь иконки;URL иконки;Галерея (ID через запятую);Пути галереи (через запятую);Услуги (ID через запятую);Продукты (ID через запятую);Порядок;Активен
1;Кейс 1;case-1;{"blocks":[...]};{"blocks":[...]};1;Раздел 1;Title;Description;Keywords;127;images/cases/case1.jpg;/upload/case1.jpg;128;images/icons/icon1.svg;/upload/icon1.svg;129,130;images/gallery/case1_1.jpg,images/gallery/case1_2.jpg;1,2;3,4;1;1
```

### Для полного экспорта всех разделов

```
decisions_full_YYYY-MM-DD_HHMMSS.zip
├── chapters.csv
├── products.csv
├── services.csv
├── cases.csv
└── images/
    ├── products/
    │   └── ...
    ├── services/
    │   └── ...
    ├── cases/
    │   └── ...
    ├── icons/
    │   └── ...
    └── gallery/
        └── ...
```

---

## Детальная реализация

### 1. ChaptersExport (app/Exports/ChaptersExport.php)

```php
<?php

namespace App\Exports;

use App\Models\Chapter;
use ZipArchive;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ChaptersExport
{
    /**
     * Экспортировать разделы в ZIP архив с CSV
     */
    public function exportToZip(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $chapters = Chapter::orderBy('order')->get();
        $filename = 'chapters_' . date('Y-m-d_His') . '.zip';

        $headers = [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($chapters) {
            // Создаем временную директорию
            $tempDir = storage_path('app/temp/export_chapters_' . uniqid());
            File::makeDirectory($tempDir, 0755, true);

            try {
                // Создаем CSV файл
                $csvPath = $tempDir . '/chapters.csv';
                $csvFile = fopen($csvPath, 'w');

                // BOM для правильного отображения кириллицы
                fprintf($csvFile, chr(0xEF).chr(0xBB).chr(0xBF));

                // Заголовки
                fputcsv($csvFile, [
                    'ID',
                    'Название',
                    'Порядок',
                    'Активен',
                ], ';');

                // Данные
                foreach ($chapters as $chapter) {
                    fputcsv($csvFile, [
                        $chapter->id,
                        $chapter->name,
                        $chapter->order ?? 0,
                        $chapter->is_active ? '1' : '0',
                    ], ';');
                }

                fclose($csvFile);

                // Создаем ZIP архив
                $zipPath = $tempDir . '.zip';
                $zip = new ZipArchive();
                if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                    $zip->addFile($csvPath, 'chapters.csv');
                    $zip->close();

                    // Отправляем ZIP файл
                    readfile($zipPath);

                    // Очистка
                    File::deleteDirectory($tempDir);
                    @unlink($zipPath);
                } else {
                    throw new \Exception('Не удалось создать ZIP архив');
                }
            } catch (\Exception $e) {
                Log::error('Chapters export error: ' . $e->getMessage());
                if (File::exists($tempDir)) {
                    File::deleteDirectory($tempDir);
                }
                echo "Ошибка при создании архива: " . $e->getMessage();
            }
        };

        return response()->stream($callback, 200, $headers);
    }
}
```

### 2. ChaptersImport (app/Imports/ChaptersImport.php)

```php
<?php

namespace App\Imports;

use App\Models\Chapter;
use App\Services\ZipImportService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ChaptersImport
{
    protected $errors = [];
    protected $successCount = 0;
    protected $skipCount = 0;
    protected $zipImportService;
    protected $extractPath = null;

    public function __construct()
    {
        $this->zipImportService = new ZipImportService();
    }

    /**
     * Импортировать разделы из ZIP или CSV
     */
    public function importFromZip($zipFile): array
    {
        $this->errors = [];
        $this->successCount = 0;
        $this->skipCount = 0;

        try {
            // Распаковываем ZIP
            $extractResult = $this->zipImportService->extractZip($zipFile->getRealPath());
            
            if (!$extractResult) {
                return [
                    'success' => false,
                    'message' => 'Не удалось распаковать ZIP архив',
                    'errors' => ['Ошибка распаковки архива'],
                ];
            }

            $this->extractPath = $extractResult['extractPath'];
            $csvPath = $extractResult['csvPath'];

            // Импортируем из CSV
            $result = $this->importFromCsvPath($csvPath);
            return $result;

        } catch (\Exception $e) {
            Log::error('Chapters import error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Ошибка импорта: ' . $e->getMessage(),
                'errors' => [$e->getMessage()],
            ];
        } finally {
            if ($this->extractPath) {
                $this->zipImportService->cleanup($this->extractPath);
            }
        }
    }

    /**
     * Импортировать из CSV файла
     */
    protected function importFromCsvPath(string $csvPath): array
    {
        $handle = fopen($csvPath, 'r');
        
        if (!$handle) {
            return [
                'success' => false,
                'message' => 'Не удалось открыть CSV файл',
            ];
        }

        // Читаем заголовки
        $headers = fgetcsv($handle, 0, ';');
        
        // Обрабатываем BOM
        if (!empty($headers[0]) && str_starts_with($headers[0], "\xEF\xBB\xBF")) {
            $headers[0] = substr($headers[0], 3);
        }

        $rowNumber = 1;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $rowNumber++;
            
            if (empty(array_filter($row))) {
                continue;
            }

            $data = $this->parseRow($row, $headers);
            
            // Валидация
            $validator = Validator::make($data, [
                'name' => 'required|string|max:255',
                'order' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                $this->errors[] = [
                    'row' => $rowNumber,
                    'errors' => $validator->errors()->all(),
                    'data' => $data,
                ];
                $this->skipCount++;
                continue;
            }

            try {
                // Создаем или обновляем раздел
                if (!empty($data['id']) && Chapter::find($data['id'])) {
                    $chapter = Chapter::find($data['id']);
                    $chapter->update($data);
                } else {
                    unset($data['id']); // Не используем ID при создании нового
                    Chapter::create($data);
                }

                $this->successCount++;
            } catch (\Exception $e) {
                $this->errors[] = [
                    'row' => $rowNumber,
                    'errors' => [$e->getMessage()],
                    'data' => $data,
                ];
                $this->skipCount++;
            }
        }

        fclose($handle);

        return [
            'success' => true,
            'message' => "Импорт завершен. Успешно: {$this->successCount}, Пропущено: {$this->skipCount}",
            'success_count' => $this->successCount,
            'skip_count' => $this->skipCount,
            'errors' => $this->errors,
        ];
    }

    /**
     * Парсить строку CSV
     */
    protected function parseRow(array $row, array $headers): array
    {
        $data = [];
        
        foreach ($headers as $index => $header) {
            $value = $row[$index] ?? '';
            $header = trim($header);
            
            switch ($header) {
                case 'ID':
                    $data['id'] = !empty($value) ? (int)$value : null;
                    break;
                case 'Название':
                    $data['name'] = trim($value);
                    break;
                case 'Порядок':
                    $data['order'] = !empty($value) ? (int)$value : 0;
                    break;
                case 'Активен':
                    $data['is_active'] = ($value === '1' || $value === 'true' || $value === 'да');
                    break;
            }
        }

        return $data;
    }
}
```

### 3. CasesExport (app/Exports/CasesExport.php)

```php
<?php

namespace App\Exports;

use App\Models\ProjectCase;
use ZipArchive;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CasesExport
{
    /**
     * Экспортировать случаи в ZIP архив с CSV и изображениями
     */
    public function exportToZip(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $cases = ProjectCase::with(['chapter', 'image', 'icon', 'images', 'services', 'products'])
            ->orderBy('order')
            ->get();

        $filename = 'cases_' . date('Y-m-d_His') . '.zip';

        $headers = [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($cases) {
            $tempDir = storage_path('app/temp/export_cases_' . uniqid());
            File::makeDirectory($tempDir, 0755, true);

            try {
                // Создаем структуру папок
                $casesImagesDir = $tempDir . '/images/cases';
                $iconsDir = $tempDir . '/images/icons';
                $galleryDir = $tempDir . '/images/gallery';
                File::makeDirectory($casesImagesDir, 0755, true);
                File::makeDirectory($iconsDir, 0755, true);
                File::makeDirectory($galleryDir, 0755, true);

                // Создаем CSV файл
                $csvPath = $tempDir . '/cases.csv';
                $csvFile = fopen($csvPath, 'w');

                // BOM для кириллицы
                fprintf($csvFile, chr(0xEF).chr(0xBB).chr(0xBF));

                // Заголовки
                fputcsv($csvFile, [
                    'ID',
                    'Название',
                    'Slug',
                    'Описание',
                    'HTML',
                    'Раздел ID',
                    'Название раздела',
                    'SEO Title',
                    'SEO Description',
                    'SEO Keywords',
                    'ID изображения',
                    'Путь изображения',
                    'URL изображения',
                    'ID иконки',
                    'Путь иконки',
                    'URL иконки',
                    'Галерея (ID через запятую)',
                    'Пути галереи (через запятую)',
                    'Услуги (ID через запятую)',
                    'Продукты (ID через запятую)',
                    'Порядок',
                    'Активен',
                ], ';');

                // Обрабатываем кейсы
                foreach ($cases as $case) {
                    $imagePath = '';
                    $iconPath = '';
                    $galleryPaths = [];

                    // Копируем главное изображение
                    if ($case->image) {
                        $sourcePath = $case->image->full_path;
                        if (file_exists($sourcePath)) {
                            $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
                            $originalName = $case->image->original_name ?? ($case->image->name . '.' . $extension);
                            $safeName = $this->sanitizeFileName($originalName);
                            $targetPath = $casesImagesDir . '/' . $safeName;

                            if (copy($sourcePath, $targetPath)) {
                                $imagePath = 'images/cases/' . $safeName;
                            }
                        }
                    }

                    // Копируем иконку
                    if ($case->icon) {
                        $sourcePath = $case->icon->full_path;
                        if (file_exists($sourcePath)) {
                            $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
                            $originalName = $case->icon->original_name ?? ($case->icon->name . '.' . $extension);
                            $safeName = $this->sanitizeFileName($originalName);
                            $targetPath = $iconsDir . '/' . $safeName;

                            if (copy($sourcePath, $targetPath)) {
                                $iconPath = 'images/icons/' . $safeName;
                            }
                        }
                    }

                    // Копируем галерею
                    if ($case->images) {
                        foreach ($case->images as $galleryImage) {
                            $sourcePath = $galleryImage->full_path;
                            if (file_exists($sourcePath)) {
                                $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
                                $originalName = $galleryImage->original_name ?? ($galleryImage->name . '.' . $extension);
                                $safeName = $this->sanitizeFileName($originalName);
                                $targetPath = $galleryDir . '/' . $safeName;

                                if (copy($sourcePath, $targetPath)) {
                                    $galleryPaths[] = 'images/gallery/' . $safeName;
                                }
                            }
                        }
                    }

                    // ID связей
                    $servicesIds = $case->services ? $case->services->pluck('id')->implode(',') : '';
                    $productsIds = $case->products ? $case->products->pluck('id')->implode(',') : '';
                    $galleryIds = $case->images ? $case->images->pluck('id')->implode(',') : '';

                    // Записываем строку
                    fputcsv($csvFile, [
                        $case->id,
                        $case->name,
                        $case->slug,
                        is_array($case->description) ? json_encode($case->description, JSON_UNESCAPED_UNICODE) : ($case->description ?? ''),
                        is_array($case->html) ? json_encode($case->html, JSON_UNESCAPED_UNICODE) : ($case->html ?? ''),
                        $case->chapter_id ?? '',
                        $case->chapter?->name ?? '',
                        $case->seo_title ?? '',
                        $case->seo_description ?? '',
                        $case->seo_keywords ?? '',
                        $case->image_id ?? '',
                        $imagePath,
                        $case->image?->url ?? '',
                        $case->icon_id ?? '',
                        $iconPath,
                        $case->icon?->url ?? '',
                        $galleryIds,
                        implode(',', $galleryPaths),
                        $servicesIds,
                        $productsIds,
                        $case->order ?? 0,
                        $case->is_active ? '1' : '0',
                    ], ';');
                }

                fclose($csvFile);

                // Создаем ZIP
                $zipPath = $tempDir . '.zip';
                $zip = new ZipArchive();
                if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                    $zip->addFile($csvPath, 'cases.csv');
                    $this->addDirectoryToZip($zip, $casesImagesDir, 'images/cases');
                    $this->addDirectoryToZip($zip, $iconsDir, 'images/icons');
                    $this->addDirectoryToZip($zip, $galleryDir, 'images/gallery');
                    $zip->close();

                    readfile($zipPath);

                    // Очистка
                    File::deleteDirectory($tempDir);
                    @unlink($zipPath);
                } else {
                    throw new \Exception('Не удалось создать ZIP архив');
                }
            } catch (\Exception $e) {
                Log::error('Cases export error: ' . $e->getMessage());
                if (File::exists($tempDir)) {
                    File::deleteDirectory($tempDir);
                }
                echo "Ошибка: " . $e->getMessage();
            }
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function addDirectoryToZip(ZipArchive $zip, string $dir, string $zipPath): void
    {
        $files = File::allFiles($dir);
        foreach ($files as $file) {
            $relativePath = $zipPath . '/' . $file->getRelativePathname();
            $zip->addFile($file->getPathname(), $relativePath);
        }
    }

    protected function sanitizeFileName(string $filename): string
    {
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        $filename = preg_replace('/_+/', '_', $filename);
        return $filename;
    }
}
```

### 4. CasesImport (app/Imports/CasesImport.php)

```php
<?php

namespace App\Imports;

use App\Models\ProjectCase;
use App\Models\Chapter;
use App\Models\Media;
use App\Services\ZipImportService;
use App\Services\MediaImportService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CasesImport
{
    protected $errors = [];
    protected $successCount = 0;
    protected $skipCount = 0;
    protected $zipImportService;
    protected $mediaImportService;
    protected $extractPath = null;

    public function __construct()
    {
        $this->zipImportService = new ZipImportService();
        $this->mediaImportService = new MediaImportService();
    }

    public function importFromZip($zipFile): array
    {
        $this->errors = [];
        $this->successCount = 0;
        $this->skipCount = 0;

        try {
            $extractResult = $this->zipImportService->extractZip($zipFile->getRealPath());
            
            if (!$extractResult) {
                return [
                    'success' => false,
                    'message' => 'Не удалось распаковать ZIP архив',
                    'errors' => ['Ошибка распаковки архива'],
                ];
            }

            $this->extractPath = $extractResult['extractPath'];
            $csvPath = $extractResult['csvPath'];

            $result = $this->importFromCsvPath($csvPath, $extractResult['extractPath']);
            return $result;

        } catch (\Exception $e) {
            Log::error('Cases import error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Ошибка импорта: ' . $e->getMessage(),
                'errors' => [$e->getMessage()],
            ];
        } finally {
            if ($this->extractPath) {
                $this->zipImportService->cleanup($this->extractPath);
            }
        }
    }

    protected function importFromCsvPath(string $csvPath, ?string $extractPath = null): array
    {
        $handle = fopen($csvPath, 'r');
        
        if (!$handle) {
            return [
                'success' => false,
                'message' => 'Не удалось открыть CSV файл',
            ];
        }

        $headers = fgetcsv($handle, 0, ';');
        
        if (!empty($headers[0]) && str_starts_with($headers[0], "\xEF\xBB\xBF")) {
            $headers[0] = substr($headers[0], 3);
        }

        $rowNumber = 1;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $rowNumber++;
            
            if (empty(array_filter($row))) {
                continue;
            }

            $data = $this->parseRow($row, $headers, $extractPath, $rowNumber);
            
            $validator = Validator::make($data, [
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255',
                'description' => 'nullable',
                'html' => 'nullable',
                'chapter_id' => 'nullable|exists:chapters,id',
                'image_id' => 'nullable|exists:media,id',
                'icon_id' => 'nullable|exists:media,id',
                'order' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                $this->errors[] = [
                    'row' => $rowNumber,
                    'errors' => $validator->errors()->all(),
                    'data' => $data,
                ];
                $this->skipCount++;
                continue;
            }

            try {
                // Генерация slug
                if (empty($data['slug']) && !empty($data['name'])) {
                    $data['slug'] = Str::slug($data['name']);
                }

                // Order
                if (!isset($data['order'])) {
                    $maxOrder = ProjectCase::where('chapter_id', $data['chapter_id'] ?? null)->max('order') ?? -1;
                    $data['order'] = $maxOrder + 1;
                }

                // Сохранение галереи и связей
                $galleryImages = $data['gallery_images'] ?? [];
                $servicesIds = $data['services'] ?? null;
                $productsIds = $data['products'] ?? null;
                unset($data['gallery_images'], $data['services'], $data['products']);

                if (!empty($data['id']) && ProjectCase::find($data['id'])) {
                    $case = ProjectCase::find($data['id']);
                    $case->update($data);
                } else {
                    $case = ProjectCase::create($data);
                }

                // Синхронизация галереи
                if (!empty($galleryImages)) {
                    $case->images()->sync($galleryImages);
                }

                // Синхронизация услуг
                if ($servicesIds !== null) {
                    $ids = array_filter(array_map('intval', explode(',', $servicesIds)));
                    $case->services()->sync($ids);
                }

                // Синхронизация продуктов
                if ($productsIds !== null) {
                    $ids = array_filter(array_map('intval', explode(',', $productsIds)));
                    $case->products()->sync($ids);
                }

                $this->successCount++;
            } catch (\Exception $e) {
                $this->errors[] = [
                    'row' => $rowNumber,
                    'errors' => [$e->getMessage()],
                    'data' => $data,
                ];
                $this->skipCount++;
            }
        }

        fclose($handle);

        return [
            'success' => true,
            'message' => "Импорт завершен. Успешно: {$this->successCount}, Пропущено: {$this->skipCount}",
            'success_count' => $this->successCount,
            'skip_count' => $this->skipCount,
            'errors' => $this->errors,
        ];
    }

    protected function parseRow(array $row, array $headers, ?string $extractPath = null, int $rowNumber = 0): array
    {
        $data = [];
        $galleryImages = [];
        
        foreach ($headers as $index => $header) {
            $value = $row[$index] ?? '';
            $header = trim($header);
            
            switch ($header) {
                case 'ID':
                    $data['id'] = !empty($value) ? (int)$value : null;
                    break;
                case 'Название':
                    $data['name'] = trim($value);
                    break;
                case 'Slug':
                    $data['slug'] = !empty($value) ? trim($value) : null;
                    break;
                case 'Описание':
                    $data['description'] = !empty($value) ? $value : null;
                    break;
                case 'HTML':
                    $data['html'] = !empty($value) ? $value : null;
                    break;
                case 'Раздел ID':
                    $data['chapter_id'] = !empty($value) ? (int)$value : null;
                    break;
                case 'SEO Title':
                    $data['seo_title'] = !empty($value) ? trim($value) : null;
                    break;
                case 'SEO Description':
                    $data['seo_description'] = !empty($value) ? trim($value) : null;
                    break;
                case 'SEO Keywords':
                    $data['seo_keywords'] = !empty($value) ? trim($value) : null;
                    break;
                case 'ID изображения':
                    if (!empty($value) && is_numeric($value)) {
                        $data['image_id'] = (int)$value;
                    }
                    break;
                case 'Путь изображения':
                    if (!empty($value) && $extractPath) {
                        $imagePath = $this->zipImportService->resolveImagePath($extractPath, trim($value));
                        if ($imagePath) {
                            $media = $this->mediaImportService->uploadImageFromPath($imagePath);
                            if ($media) {
                                $data['image_id'] = $media->id;
                            }
                        }
                    }
                    break;
                case 'ID иконки':
                    if (!empty($value) && is_numeric($value)) {
                        $data['icon_id'] = (int)$value;
                    }
                    break;
                case 'Путь иконки':
                    if (!empty($value) && $extractPath) {
                        $iconPath = $this->zipImportService->resolveImagePath($extractPath, trim($value));
                        if ($iconPath) {
                            $media = $this->mediaImportService->uploadImageFromPath($iconPath);
                            if ($media) {
                                $data['icon_id'] = $media->id;
                            }
                        }
                    }
                    break;
                case 'Пути галереи (через запятую)':
                    if (!empty($value) && $extractPath) {
                        $paths = explode(',', $value);
                        foreach ($paths as $path) {
                            $path = trim($path);
                            if (empty($path)) continue;
                            
                            $galleryImagePath = $this->zipImportService->resolveImagePath($extractPath, $path);
                            if ($galleryImagePath) {
                                $media = $this->mediaImportService->uploadImageFromPath($galleryImagePath);
                                if ($media) {
                                    $galleryImages[] = $media->id;
                                }
                            }
                        }
                    }
                    break;
                case 'Услуги (ID через запятую)':
                    $data['services'] = !empty($value) ? trim($value) : null;
                    break;
                case 'Продукты (ID через запятую)':
                    $data['products'] = !empty($value) ? trim($value) : null;
                    break;
                case 'Порядок':
                    $data['order'] = !empty($value) ? (int)$value : 0;
                    break;
                case 'Активен':
                    $data['is_active'] = ($value === '1' || $value === 'true' || $value === 'да');
                    break;
            }
        }

        if (!empty($galleryImages)) {
            $data['gallery_images'] = $galleryImages;
        }

        return $data;
    }
}
```

### 5. DecisionsExport - Полный экспорт всех разделов (app/Exports/DecisionsExport.php)

```php
<?php

namespace App\Exports;

use App\Models\Chapter;
use App\Models\Product;
use App\Models\Service;
use App\Models\ProjectCase;
use ZipArchive;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class DecisionsExport
{
    /**
     * Экспортировать все разделы в один ZIP архив
     */
    public function exportAllToZip(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'decisions_full_' . date('Y-m-d_His') . '.zip';

        $headers = [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () {
            $tempDir = storage_path('app/temp/export_decisions_' . uniqid());
            File::makeDirectory($tempDir, 0755, true);

            try {
                // Структура папок для изображений
                $imagesDir = $tempDir . '/images';
                File::makeDirectory($imagesDir . '/products', 0755, true);
                File::makeDirectory($imagesDir . '/services', 0755, true);
                File::makeDirectory($imagesDir . '/cases', 0755, true);
                File::makeDirectory($imagesDir . '/icons', 0755, true);
                File::makeDirectory($imagesDir . '/gallery', 0755, true);

                // Экспортируем каждый раздел
                $this->exportChapters($tempDir);
                $this->exportProducts($tempDir, $imagesDir);
                $this->exportServices($tempDir, $imagesDir);
                $this->exportCases($tempDir, $imagesDir);

                // Создаем ZIP архив
                $zipPath = $tempDir . '.zip';
                $zip = new ZipArchive();
                if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                    // Добавляем CSV файлы
                    $zip->addFile($tempDir . '/chapters.csv', 'chapters.csv');
                    $zip->addFile($tempDir . '/products.csv', 'products.csv');
                    $zip->addFile($tempDir . '/services.csv', 'services.csv');
                    $zip->addFile($tempDir . '/cases.csv', 'cases.csv');

                    // Добавляем изображения
                    $this->addDirectoryToZip($zip, $imagesDir, 'images');

                    $zip->close();

                    readfile($zipPath);

                    // Очистка
                    File::deleteDirectory($tempDir);
                    @unlink($zipPath);
                } else {
                    throw new \Exception('Не удалось создать ZIP архив');
                }
            } catch (\Exception $e) {
                Log::error('Full decisions export error: ' . $e->getMessage());
                if (File::exists($tempDir)) {
                    File::deleteDirectory($tempDir);
                }
                echo "Ошибка: " . $e->getMessage();
            }
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function exportChapters(string $tempDir): void
    {
        $chapters = Chapter::orderBy('order')->get();
        $csvPath = $tempDir . '/chapters.csv';
        $csvFile = fopen($csvPath, 'w');

        fprintf($csvFile, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($csvFile, ['ID', 'Название', 'Порядок', 'Активен'], ';');

        foreach ($chapters as $chapter) {
            fputcsv($csvFile, [
                $chapter->id,
                $chapter->name,
                $chapter->order ?? 0,
                $chapter->is_active ? '1' : '0',
            ], ';');
        }

        fclose($csvFile);
    }

    protected function exportProducts(string $tempDir, string $imagesDir): void
    {
        // Аналогично ProductsExport, но с сохранением в $tempDir
        // ... (копируем логику из ProductsExport)
    }

    protected function exportServices(string $tempDir, string $imagesDir): void
    {
        // Аналогично ServicesExport
        // ... (копируем логику из ServicesExport)
    }

    protected function exportCases(string $tempDir, string $imagesDir): void
    {
        // Аналогично CasesExport
        // ... (копируем логику из CasesExport)
    }

    protected function addDirectoryToZip(ZipArchive $zip, string $dir, string $zipPath): void
    {
        $files = File::allFiles($dir);
        foreach ($files as $file) {
            $relativePath = $zipPath . '/' . $file->getRelativePathname();
            $zip->addFile($file->getPathname(), $relativePath);
        }
    }
}
```

### 6. API Routes (routes/api.php)

Добавить новые роуты:

```php
// В секции admin middleware:

// Chapters import/export
Route::get('chapters/export', [ChapterController::class, 'export']);
Route::post('chapters/import', [ChapterController::class, 'import']);

// Cases import/export
Route::get('cases/export', [CaseController::class, 'export']);
Route::post('cases/import', [CaseController::class, 'import']);

// Full decisions export/import
Route::get('decisions/export', [DecisionController::class, 'exportAll']);
Route::post('decisions/import', [DecisionController::class, 'importAll']);
```

### 7. Controllers

#### ChapterController - добавить методы:

```php
public function export()
{
    $exporter = new ChaptersExport();
    return $exporter->exportToZip();
}

public function import(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:zip,csv|max:102400',
    ]);

    $importer = new ChaptersImport();
    $result = $importer->importFromZip($request->file('file'));

    return response()->json($result);
}
```

#### CaseController - добавить методы:

```php
public function export()
{
    $exporter = new CasesExport();
    return $exporter->exportToZip();
}

public function import(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:zip,csv|max:102400',
    ]);

    $importer = new CasesImport();
    $result = $importer->importFromZip($request->file('file'));

    return response()->json($result);
}
```

#### DecisionController - новый контроллер:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\DecisionsExport;
use App\Imports\DecisionsImport;
use Illuminate\Http\Request;

class DecisionController extends Controller
{
    public function exportAll()
    {
        $exporter = new DecisionsExport();
        return $exporter->exportAllToZip();
    }

    public function importAll(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:zip|max:204800',
        ]);

        $importer = new DecisionsImport();
        $result = $importer->importAllFromZip($request->file('file'));

        return response()->json($result);
    }
}
```

### 8. Frontend - Компонент кнопок импорт/экспорт

**resources/js/components/admin/ImportExportButtons.vue:**

```vue
<template>
    <div class="flex items-center gap-2">
        <!-- Кнопка экспорта -->
        <button
            @click="showExportMenu = !showExportMenu"
            class="h-11 px-4 flex items-center gap-2 rounded-md bg-accent/10 hover:bg-accent/20 transition-colors text-sm font-medium relative"
            type="button"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span class="hidden sm:inline">Экспорт</span>
        </button>

        <!-- Меню экспорта -->
        <div
            v-if="showExportMenu"
            v-click-outside="() => showExportMenu = false"
            class="absolute top-full right-0 mt-2 w-56 bg-card border border-border rounded-lg shadow-lg z-50"
        >
            <button
                v-for="option in exportOptions"
                :key="option.value"
                @click="handleExport(option.value)"
                class="w-full px-4 py-3 text-left text-sm hover:bg-accent/10 transition-colors first:rounded-t-lg last:rounded-b-lg"
            >
                {{ option.label }}
            </button>
        </div>

        <!-- Кнопка импорта -->
        <button
            @click="showImportMenu = !showImportMenu"
            class="h-11 px-4 flex items-center gap-2 rounded-md bg-accent/10 hover:bg-accent/20 transition-colors text-sm font-medium relative"
            type="button"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
            </svg>
            <span class="hidden sm:inline">Импорт</span>
        </button>

        <!-- Меню импорта -->
        <div
            v-if="showImportMenu"
            v-click-outside="() => showImportMenu = false"
            class="absolute top-full right-0 mt-2 w-56 bg-card border border-border rounded-lg shadow-lg z-50"
        >
            <button
                v-for="option in importOptions"
                :key="option.value"
                @click="handleImportSelect(option.value)"
                class="w-full px-4 py-3 text-left text-sm hover:bg-accent/10 transition-colors first:rounded-t-lg last:rounded-b-lg"
            >
                {{ option.label }}
            </button>
        </div>

        <!-- Скрытый input для файлов -->
        <input
            ref="fileInput"
            type="file"
            accept=".zip,.csv"
            @change="handleFileChange"
            class="hidden"
        />

        <!-- Модальное окно с результатами импорта -->
        <div
            v-if="showImportResults"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            @click.self="showImportResults = false"
        >
            <div class="bg-card rounded-lg shadow-xl max-w-2xl w-full max-h-[80vh] overflow-hidden flex flex-col">
                <div class="p-6 border-b border-border">
                    <h3 class="text-lg font-semibold">Результаты импорта</h3>
                </div>
                <div class="p-6 overflow-y-auto flex-1">
                    <div class="mb-4">
                        <p class="text-sm mb-2">
                            <span class="font-medium">Успешно импортировано:</span>
                            <span class="text-green-600 dark:text-green-400 ml-2">{{ importResults.success_count }}</span>
                        </p>
                        <p class="text-sm">
                            <span class="font-medium">Пропущено:</span>
                            <span class="text-orange-600 dark:text-orange-400 ml-2">{{ importResults.skip_count }}</span>
                        </p>
                    </div>

                    <!-- Ошибки -->
                    <div v-if="importResults.errors && importResults.errors.length > 0" class="mt-4">
                        <h4 class="font-medium text-sm mb-2">Ошибки импорта:</h4>
                        <div class="space-y-2 max-h-96 overflow-y-auto">
                            <div
                                v-for="(error, index) in importResults.errors"
                                :key="index"
                                class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded text-sm"
                            >
                                <p class="font-medium text-red-800 dark:text-red-400">
                                    Строка {{ error.row }}:
                                </p>
                                <ul class="list-disc list-inside mt-1 text-red-700 dark:text-red-300">
                                    <li v-for="(err, i) in error.errors" :key="i">{{ err }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-6 border-t border-border flex justify-end">
                    <button
                        @click="showImportResults = false"
                        class="px-4 py-2 bg-accent text-accent-foreground rounded-md hover:bg-accent/90 transition-colors"
                    >
                        Закрыть
                    </button>
                </div>
            </div>
        </div>

        <!-- Loader -->
        <div
            v-if="loading"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
        >
            <div class="bg-card rounded-lg p-6 flex items-center gap-4">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-accent"></div>
                <span class="text-sm font-medium">{{ loadingMessage }}</span>
            </div>
        </div>
    </div>
</template>

<script>
import { ref } from 'vue';
import axios from 'axios';

export default {
    name: 'ImportExportButtons',
    directives: {
        clickOutside: {
            mounted(el, binding) {
                el.clickOutsideEvent = (event) => {
                    if (!(el === event.target || el.contains(event.target))) {
                        binding.value();
                    }
                };
                document.addEventListener('click', el.clickOutsideEvent);
            },
            unmounted(el) {
                document.removeEventListener('click', el.clickOutsideEvent);
            },
        },
    },
    setup() {
        const showExportMenu = ref(false);
        const showImportMenu = ref(false);
        const showImportResults = ref(false);
        const loading = ref(false);
        const loadingMessage = ref('');
        const importResults = ref({});
        const fileInput = ref(null);
        const selectedImportType = ref(null);

        const exportOptions = [
            { label: 'Экспорт всех разделов', value: 'all' },
            { label: 'Экспорт разделов', value: 'chapters' },
            { label: 'Экспорт продуктов', value: 'products' },
            { label: 'Экспорт услуг', value: 'services' },
            { label: 'Экспорт случаев', value: 'cases' },
        ];

        const importOptions = [
            { label: 'Импорт всех разделов', value: 'all' },
            { label: 'Импорт разделов', value: 'chapters' },
            { label: 'Импорт продуктов', value: 'products' },
            { label: 'Импорт услуг', value: 'services' },
            { label: 'Импорт случаев', value: 'cases' },
        ];

        const handleExport = async (type) => {
            showExportMenu.value = false;
            loading.value = true;
            loadingMessage.value = 'Подготовка экспорта...';

            try {
                const endpoints = {
                    all: '/api/v1/admin/decisions/export',
                    chapters: '/api/v1/admin/chapters/export',
                    products: '/api/v1/admin/products/export',
                    services: '/api/v1/admin/services/export',
                    cases: '/api/v1/admin/cases/export',
                };

                const response = await axios.get(endpoints[type], {
                    responseType: 'blob',
                });

                // Создаем ссылку для скачивания
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                
                // Получаем имя файла из заголовка
                const contentDisposition = response.headers['content-disposition'];
                let filename = `${type}_export_${new Date().toISOString().split('T')[0]}.zip`;
                if (contentDisposition) {
                    const filenameMatch = contentDisposition.match(/filename="(.+)"/);
                    if (filenameMatch) {
                        filename = filenameMatch[1];
                    }
                }
                
                link.setAttribute('download', filename);
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(url);
            } catch (error) {
                console.error('Export error:', error);
                alert('Ошибка при экспорте: ' + (error.response?.data?.message || error.message));
            } finally {
                loading.value = false;
            }
        };

        const handleImportSelect = (type) => {
            selectedImportType.value = type;
            showImportMenu.value = false;
            fileInput.value.click();
        };

        const handleFileChange = async (event) => {
            const file = event.target.files[0];
            if (!file) return;

            loading.value = true;
            loadingMessage.value = 'Импортирование данных...';

            try {
                const formData = new FormData();
                formData.append('file', file);

                const endpoints = {
                    all: '/api/v1/admin/decisions/import',
                    chapters: '/api/v1/admin/chapters/import',
                    products: '/api/v1/admin/products/import',
                    services: '/api/v1/admin/services/import',
                    cases: '/api/v1/admin/cases/import',
                };

                const response = await axios.post(endpoints[selectedImportType.value], formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });

                importResults.value = response.data;
                showImportResults.value = true;

                // Очищаем input
                event.target.value = '';
            } catch (error) {
                console.error('Import error:', error);
                alert('Ошибка при импорте: ' + (error.response?.data?.message || error.message));
            } finally {
                loading.value = false;
            }
        };

        return {
            showExportMenu,
            showImportMenu,
            showImportResults,
            loading,
            loadingMessage,
            importResults,
            fileInput,
            exportOptions,
            importOptions,
            handleExport,
            handleImportSelect,
            handleFileChange,
        };
    },
};
</script>
```

### 9. Интеграция в Header

**resources/js/components/admin/Header.vue:**

Добавить компонент кнопок импорт/экспорт в header:

```vue
<template>
    <header class="relative flex h-16 items-center justify-between border-b border-border bg-card backdrop-blur-xl px-4 sm:px-6 gap-2 sm:gap-4 z-30">
        <!-- ... существующий код ... -->
        <div class="flex items-center gap-2 sm:gap-3">
            <!-- Добавляем кнопки импорт/экспорт -->
            <ImportExportButtons v-if="showImportExport" />
            
            <!-- ... остальные кнопки ... -->
        </div>
    </header>
</template>

<script>
import { computed } from 'vue';
import { useStore } from 'vuex';
import { useRoute } from 'vue-router';
import NotificationDropdown from './NotificationDropdown.vue';
import ImportExportButtons from './ImportExportButtons.vue';

export default {
    name: 'Header',
    components: {
        NotificationDropdown,
        ImportExportButtons,
    },
    setup() {
        // ... существующий код ...
        
        const route = useRoute();
        
        // Показывать кнопки только в разделах решений
        const showImportExport = computed(() => {
            const path = route.path;
            return path.startsWith('/admin/decisions');
        });

        return {
            // ... существующие returns ...
            showImportExport,
        };
    },
};
</script>
```

---

## Порядок реализации

### Этап 1: Backend (1-2 дня)
1. ✅ Создать `ChaptersExport.php` и `ChaptersImport.php`
2. ✅ Создать `CasesExport.php` и `CasesImport.php`
3. ✅ Создать `DecisionsExport.php` и `DecisionsImport.php` (полный экспорт)
4. ✅ Добавить методы в контроллеры (ChapterController, CaseController)
5. ✅ Создать DecisionController
6. ✅ Добавить роуты в `routes/api.php`
7. ✅ Обновить `MediaImportService` для работы с папками разделов

### Этап 2: Frontend (1 день)
1. ✅ Создать компонент `ImportExportButtons.vue`
2. ✅ Интегрировать компонент в `Header.vue`
3. ✅ Добавить стили и адаптивность
4. ✅ Добавить обработку ошибок с показом номеров строк

### Этап 3: Тестирование (1 день)
1. Тестирование экспорта каждого раздела
2. Тестирование импорта каждого раздела
3. Тестирование полного экспорта/импорта
4. Тестирование обработки ошибок
5. Тестирование работы с изображениями

---

## Дополнительные улучшения

### 1. Регистрация медиа файлов в папках разделов

Обновить `MediaImportService::uploadImageFromPath()`:

```php
public function uploadImageFromPath(string $filePath, ?string $folderName = null): Media|false
{
    // ... существующий код ...

    // Если указано имя папки, ищем или создаем папку
    if ($folderName) {
        $folder = Folder::firstOrCreate(
            ['slug' => Str::slug($folderName)],
            ['name' => $folderName, 'parent_id' => null]
        );
        $folderId = $folder->id;
    }

    // ... остальной код ...
}
```

Использовать при импорте:

```php
// При импорте продуктов
$media = $this->mediaImportService->uploadImageFromPath($imagePath, 'products');

// При импорте услуг
$media = $this->mediaImportService->uploadImageFromPath($imagePath, 'services');

// При импорте случаев
$media = $this->mediaImportService->uploadImageFromPath($imagePath, 'cases');
```

### 2. Логирование импорта/экспорта

Добавить логирование в каждый Import/Export класс:

```php
Log::info('Export started', ['type' => 'products', 'count' => $products->count()]);
Log::info('Export completed', ['type' => 'products', 'file' => $filename]);

Log::info('Import started', ['type' => 'products', 'file' => $zipFile->getClientOriginalName()]);
Log::info('Import completed', [
    'type' => 'products',
    'success_count' => $this->successCount,
    'skip_count' => $this->skipCount,
    'errors_count' => count($this->errors),
]);
```

### 3. Валидация размера файлов

Добавить в `.env`:

```env
UPLOAD_MAX_FILESIZE=100M
MAX_FILE_UPLOADS=200
```

Обновить `php.ini` или `.user.ini`:

```ini
upload_max_filesize = 100M
post_max_size = 100M
max_file_uploads = 200
```

---

## Структура файлов проекта после реализации

```
app/
├── Exports/
│   ├── ChaptersExport.php          ← Новый
│   ├── ProductsExport.php          ✅ Существует
│   ├── ServicesExport.php          ✅ Существует
│   ├── CasesExport.php             ← Новый
│   └── DecisionsExport.php         ← Новый (полный экспорт)
├── Imports/
│   ├── ChaptersImport.php          ← Новый
│   ├── ProductsImport.php          ✅ Существует
│   ├── ServicesImport.php          ✅ Существует
│   ├── CasesImport.php             ← Новый
│   └── DecisionsImport.php         ← Новый (полный импорт)
├── Http/
│   └── Controllers/
│       └── Api/
│           ├── ChapterController.php   ← Обновить
│           ├── ProductController.php   ✅ Существует
│           ├── ServiceController.php   ✅ Существует
│           ├── CaseController.php      ← Обновить
│           └── DecisionController.php  ← Новый
└── Services/
    ├── ZipImportService.php        ✅ Существует
    └── MediaImportService.php      ← Обновить

resources/
└── js/
    └── components/
        └── admin/
            ├── Header.vue              ← Обновить
            └── ImportExportButtons.vue ← Новый

routes/
└── api.php                         ← Обновить
```

---

## Итоговый чеклист

- [ ] Создать ChaptersExport и ChaptersImport
- [ ] Создать CasesExport и CasesImport
- [ ] Создать DecisionsExport и DecisionsImport (полный)
- [ ] Обновить ChapterController и CaseController
- [ ] Создать DecisionController
- [ ] Добавить роуты в api.php
- [ ] Обновить MediaImportService для папок разделов
- [ ] Создать ImportExportButtons.vue
- [ ] Интегрировать кнопки в Header.vue
- [ ] Протестировать экспорт всех разделов
- [ ] Протестировать импорт всех разделов
- [ ] Протестировать обработку ошибок
- [ ] Протестировать работу с изображениями
- [ ] Добавить логирование
- [ ] Обновить документацию

---

## Примечания

1. **Безопасность**: Все операции импорта/экспорта доступны только администраторам (middleware 'admin')
2. **Производительность**: Для больших объемов данных используется streaming response
3. **Память**: Временные файлы автоматически удаляются после завершения операций
4. **Кодировка**: Используется BOM для корректного отображения кириллицы в Excel
5. **Валидация**: Все импортируемые данные проходят валидацию перед сохранением
6. **Ошибки**: Детальная информация об ошибках с номерами строк и описанием проблем

