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
     * Экспортировать все разделы решений в один ZIP архив
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

        $callback = function () use ($filename) {
            $tempDir = storage_path('app/temp/export_decisions_' . uniqid());
            File::makeDirectory($tempDir, 0755, true);

            try {
                // Логирование начала экспорта
                Log::info('Full decisions export started', [
                    'temp_dir' => $tempDir,
                ]);

                // Создаем структуру папок для изображений
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

                    // Логирование успешного экспорта
                    Log::info('Full decisions export completed', [
                        'file' => $filename,
                        'size' => filesize($zipPath),
                    ]);

                    // Отправляем ZIP файл
                    readfile($zipPath);

                    // Удаляем временные файлы
                    File::deleteDirectory($tempDir);
                    if (file_exists($zipPath)) {
                        @unlink($zipPath);
                    }
                } else {
                    throw new \Exception('Не удалось создать ZIP архив');
                }
            } catch (\Exception $e) {
                Log::error('Full decisions export error: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
                
                // Очищаем временные файлы
                if (File::exists($tempDir)) {
                    File::deleteDirectory($tempDir);
                }
                if (file_exists($tempDir . '.zip')) {
                    @unlink($tempDir . '.zip');
                }
                
                echo "Ошибка при создании архива: " . $e->getMessage();
            }
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Экспортировать разделы
     */
    protected function exportChapters(string $tempDir): void
    {
        $chapters = Chapter::orderBy('order')->get();
        $csvPath = $tempDir . '/chapters.csv';
        $csvFile = fopen($csvPath, 'w');

        // BOM для кириллицы
        fprintf($csvFile, chr(0xEF).chr(0xBB).chr(0xBF));

        // Заголовки (* - обязательные поля)
        fputcsv($csvFile, [
            'ID',
            'Название*',
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
    }

    /**
     * Экспортировать продукты
     */
    protected function exportProducts(string $tempDir, string $imagesDir): void
    {
        $products = Product::with(['chapter', 'image', 'icon', 'services'])->orderBy('order')->get();
        
        $csvPath = $tempDir . '/products.csv';
        $csvFile = fopen($csvPath, 'w');

        // BOM для кириллицы
        fprintf($csvFile, chr(0xEF).chr(0xBB).chr(0xBF));

        // Заголовки (* - обязательные поля)
        fputcsv($csvFile, [
            'ID',
            'Название*',
            'Slug',
            'Описание',
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
            'Услуги (ID через запятую)',
            'Порядок',
            'Активен',
        ], ';');

        // Обрабатываем продукты
        foreach ($products as $product) {
            $imagePath = '';
            $iconPath = '';
            $imageCounter = 1;
            $iconCounter = 1;

            // Копируем изображение продукта
            if ($product->image) {
                $sourcePath = $product->image->full_path;
                if (file_exists($sourcePath)) {
                    $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
                    $originalName = $product->image->original_name ?? ($product->image->name . '.' . $extension);
                    $safeName = $this->sanitizeFileName($originalName);
                    $targetPath = $imagesDir . '/products/' . $safeName;

                    // Если файл уже существует, добавляем число
                    while (file_exists($targetPath)) {
                        $nameWithoutExt = pathinfo($safeName, PATHINFO_FILENAME);
                        $ext = pathinfo($safeName, PATHINFO_EXTENSION);
                        $safeName = $nameWithoutExt . '_' . $imageCounter . '.' . $ext;
                        $targetPath = $imagesDir . '/products/' . $safeName;
                        $imageCounter++;
                    }

                    if (copy($sourcePath, $targetPath)) {
                        $imagePath = 'images/products/' . $safeName;
                    }
                }
            }

            // Копируем иконку
            if ($product->icon) {
                $sourcePath = $product->icon->full_path;
                if (file_exists($sourcePath)) {
                    $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
                    $originalName = $product->icon->original_name ?? ($product->icon->name . '.' . $extension);
                    $safeName = $this->sanitizeFileName($originalName);
                    $targetPath = $imagesDir . '/icons/' . $safeName;

                    // Если файл уже существует, добавляем число
                    while (file_exists($targetPath)) {
                        $nameWithoutExt = pathinfo($safeName, PATHINFO_FILENAME);
                        $ext = pathinfo($safeName, PATHINFO_EXTENSION);
                        $safeName = $nameWithoutExt . '_' . $iconCounter . '.' . $ext;
                        $targetPath = $imagesDir . '/icons/' . $safeName;
                        $iconCounter++;
                    }

                    if (copy($sourcePath, $targetPath)) {
                        $iconPath = 'images/icons/' . $safeName;
                    }
                }
            }

            // Получаем ID услуг
            $servicesIds = $product->services ? $product->services->pluck('id')->implode(',') : '';

            // Записываем строку в CSV
            fputcsv($csvFile, [
                $product->id,
                $product->name,
                $product->slug,
                is_array($product->description) ? json_encode($product->description, JSON_UNESCAPED_UNICODE) : ($product->description ?? ''),
                $product->chapter_id ?? '',
                $product->chapter?->name ?? '',
                $product->seo_title ?? '',
                $product->seo_description ?? '',
                $product->seo_keywords ?? '',
                $product->image_id ?? '',
                $imagePath,
                $product->image?->url ?? '',
                $product->icon_id ?? '',
                $iconPath,
                $product->icon?->url ?? '',
                $servicesIds,
                $product->order ?? 0,
                $product->is_active ? '1' : '0',
            ], ';');
        }

        fclose($csvFile);
    }

    /**
     * Экспортировать услуги
     */
    protected function exportServices(string $tempDir, string $imagesDir): void
    {
        $services = Service::with(['chapter', 'image', 'icon', 'products', 'options', 'optionTrees', 'instances'])
            ->orderBy('order')
            ->get();
        
        $csvPath = $tempDir . '/services.csv';
        $csvFile = fopen($csvPath, 'w');

        // BOM для кириллицы
        fprintf($csvFile, chr(0xEF).chr(0xBB).chr(0xBF));

        // Заголовки (* - обязательные поля)
        fputcsv($csvFile, [
            'ID',
            'Название*',
            'Slug',
            'Описание',
            'HTML контент',
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
            'Продукты (ID через запятую)',
            'Опции (ID через запятую)',
            'Деревья опций (ID через запятую)',
            'Экземпляры (ID через запятую)',
            'Порядок',
            'Активен',
        ], ';');

        // Обрабатываем услуги
        foreach ($services as $service) {
            $imagePath = '';
            $iconPath = '';
            $imageCounter = 1;
            $iconCounter = 1;

            // Копируем изображение услуги
            if ($service->image) {
                $sourcePath = $service->image->full_path;
                if (file_exists($sourcePath)) {
                    $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
                    $originalName = $service->image->original_name ?? ($service->image->name . '.' . $extension);
                    $safeName = $this->sanitizeFileName($originalName);
                    $targetPath = $imagesDir . '/services/' . $safeName;

                    // Если файл уже существует, добавляем число
                    while (file_exists($targetPath)) {
                        $nameWithoutExt = pathinfo($safeName, PATHINFO_FILENAME);
                        $ext = pathinfo($safeName, PATHINFO_EXTENSION);
                        $safeName = $nameWithoutExt . '_' . $imageCounter . '.' . $ext;
                        $targetPath = $imagesDir . '/services/' . $safeName;
                        $imageCounter++;
                    }

                    if (copy($sourcePath, $targetPath)) {
                        $imagePath = 'images/services/' . $safeName;
                    }
                }
            }

            // Копируем иконку
            if ($service->icon) {
                $sourcePath = $service->icon->full_path;
                if (file_exists($sourcePath)) {
                    $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
                    $originalName = $service->icon->original_name ?? ($service->icon->name . '.' . $extension);
                    $safeName = $this->sanitizeFileName($originalName);
                    $targetPath = $imagesDir . '/icons/' . $safeName;

                    // Если файл уже существует, добавляем число
                    while (file_exists($targetPath)) {
                        $nameWithoutExt = pathinfo($safeName, PATHINFO_FILENAME);
                        $ext = pathinfo($safeName, PATHINFO_EXTENSION);
                        $safeName = $nameWithoutExt . '_' . $iconCounter . '.' . $ext;
                        $targetPath = $imagesDir . '/icons/' . $safeName;
                        $iconCounter++;
                    }

                    if (copy($sourcePath, $targetPath)) {
                        $iconPath = 'images/icons/' . $safeName;
                    }
                }
            }

            // Получаем ID связей
            $productsIds = $service->products ? $service->products->pluck('id')->implode(',') : '';
            $optionsIds = $service->options ? $service->options->pluck('id')->implode(',') : '';
            $optionTreesIds = $service->optionTrees ? $service->optionTrees->pluck('id')->implode(',') : '';
            $instancesIds = $service->instances ? $service->instances->pluck('id')->implode(',') : '';

            // Записываем строку в CSV
            fputcsv($csvFile, [
                $service->id,
                $service->name,
                $service->slug,
                is_array($service->description) ? json_encode($service->description, JSON_UNESCAPED_UNICODE) : ($service->description ?? ''),
                $service->html_content ?? '',
                $service->chapter_id ?? '',
                $service->chapter?->name ?? '',
                $service->seo_title ?? '',
                $service->seo_description ?? '',
                $service->seo_keywords ?? '',
                $service->image_id ?? '',
                $imagePath,
                $service->image?->url ?? '',
                $service->icon_id ?? '',
                $iconPath,
                $service->icon?->url ?? '',
                $productsIds,
                $optionsIds,
                $optionTreesIds,
                $instancesIds,
                $service->order ?? 0,
                $service->is_active ? '1' : '0',
            ], ';');
        }

        fclose($csvFile);
    }

    /**
     * Экспортировать случаи
     */
    protected function exportCases(string $tempDir, string $imagesDir): void
    {
        $cases = ProjectCase::with(['chapter', 'image', 'icon', 'images', 'services', 'products'])
            ->orderBy('order')
            ->get();
        
        $csvPath = $tempDir . '/cases.csv';
        $csvFile = fopen($csvPath, 'w');

        // BOM для кириллицы
        fprintf($csvFile, chr(0xEF).chr(0xBB).chr(0xBF));

        // Заголовки (* - обязательные поля)
        fputcsv($csvFile, [
            'ID',
            'Название*',
            'Slug*',
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

        // Обрабатываем случаи
        foreach ($cases as $case) {
            $imagePath = '';
            $iconPath = '';
            $galleryPaths = [];
            $imageCounter = 1;
            $iconCounter = 1;
            $galleryCounter = 1;

            // Копируем главное изображение
            if ($case->image) {
                $sourcePath = $case->image->full_path;
                if (file_exists($sourcePath)) {
                    $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
                    $originalName = $case->image->original_name ?? ($case->image->name . '.' . $extension);
                    $safeName = $this->sanitizeFileName($originalName);
                    $targetPath = $imagesDir . '/cases/' . $safeName;

                    // Если файл уже существует, добавляем число
                    while (file_exists($targetPath)) {
                        $nameWithoutExt = pathinfo($safeName, PATHINFO_FILENAME);
                        $ext = pathinfo($safeName, PATHINFO_EXTENSION);
                        $safeName = $nameWithoutExt . '_' . $imageCounter . '.' . $ext;
                        $targetPath = $imagesDir . '/cases/' . $safeName;
                        $imageCounter++;
                    }

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
                    $targetPath = $imagesDir . '/icons/' . $safeName;

                    // Если файл уже существует, добавляем число
                    while (file_exists($targetPath)) {
                        $nameWithoutExt = pathinfo($safeName, PATHINFO_FILENAME);
                        $ext = pathinfo($safeName, PATHINFO_EXTENSION);
                        $safeName = $nameWithoutExt . '_' . $iconCounter . '.' . $ext;
                        $targetPath = $imagesDir . '/icons/' . $safeName;
                        $iconCounter++;
                    }

                    if (copy($sourcePath, $targetPath)) {
                        $iconPath = 'images/icons/' . $safeName;
                    }
                }
            }

            // Копируем галерею изображений
            if ($case->images && $case->images->count() > 0) {
                foreach ($case->images as $galleryImage) {
                    $sourcePath = $galleryImage->full_path;
                    if (file_exists($sourcePath)) {
                        $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
                        $originalName = $galleryImage->original_name ?? ($galleryImage->name . '.' . $extension);
                        $safeName = $this->sanitizeFileName($originalName);
                        $targetPath = $imagesDir . '/gallery/' . $safeName;

                        // Если файл уже существует, добавляем число
                        while (file_exists($targetPath)) {
                            $nameWithoutExt = pathinfo($safeName, PATHINFO_FILENAME);
                            $ext = pathinfo($safeName, PATHINFO_EXTENSION);
                            $safeName = $nameWithoutExt . '_' . $galleryCounter . '.' . $ext;
                            $targetPath = $imagesDir . '/gallery/' . $safeName;
                            $galleryCounter++;
                        }

                        if (copy($sourcePath, $targetPath)) {
                            $galleryPaths[] = 'images/gallery/' . $safeName;
                        }
                    }
                }
            }

            // Получаем ID связей
            $servicesIds = $case->services ? $case->services->pluck('id')->implode(',') : '';
            $productsIds = $case->products ? $case->products->pluck('id')->implode(',') : '';
            $galleryIds = $case->images ? $case->images->pluck('id')->implode(',') : '';

            // Записываем строку в CSV
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
    }

    /**
     * Добавить директорию в ZIP архив
     */
    protected function addDirectoryToZip(ZipArchive $zip, string $dir, string $zipPath): void
    {
        if (!File::exists($dir)) {
            return;
        }

        $files = File::allFiles($dir);
        foreach ($files as $file) {
            $relativePath = $zipPath . '/' . str_replace($dir . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $relativePath = str_replace('\\', '/', $relativePath);
            $zip->addFile($file->getPathname(), $relativePath);
        }
    }

    /**
     * Очистить имя файла от недопустимых символов
     */
    protected function sanitizeFileName(string $filename): string
    {
        // Заменяем недопустимые символы
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        // Убираем множественные подчеркивания
        $filename = preg_replace('/_+/', '_', $filename);
        return $filename;
    }
}

