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

        $callback = function () use ($cases, $filename) {
            $tempDir = storage_path('app/temp/export_cases_' . uniqid());
            File::makeDirectory($tempDir, 0755, true);

            try {
                // Логирование начала экспорта
                Log::info('Cases export started', [
                    'count' => $cases->count(),
                    'temp_dir' => $tempDir,
                ]);

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

                // Обрабатываем кейсы
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
                            $targetPath = $casesImagesDir . '/' . $safeName;

                            // Если файл уже существует, добавляем число
                            while (file_exists($targetPath)) {
                                $nameWithoutExt = pathinfo($safeName, PATHINFO_FILENAME);
                                $ext = pathinfo($safeName, PATHINFO_EXTENSION);
                                $safeName = $nameWithoutExt . '_' . $imageCounter . '.' . $ext;
                                $targetPath = $casesImagesDir . '/' . $safeName;
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
                            $targetPath = $iconsDir . '/' . $safeName;

                            // Если файл уже существует, добавляем число
                            while (file_exists($targetPath)) {
                                $nameWithoutExt = pathinfo($safeName, PATHINFO_FILENAME);
                                $ext = pathinfo($safeName, PATHINFO_EXTENSION);
                                $safeName = $nameWithoutExt . '_' . $iconCounter . '.' . $ext;
                                $targetPath = $iconsDir . '/' . $safeName;
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
                                $targetPath = $galleryDir . '/' . $safeName;

                                // Если файл уже существует, добавляем число
                                while (file_exists($targetPath)) {
                                    $nameWithoutExt = pathinfo($safeName, PATHINFO_FILENAME);
                                    $ext = pathinfo($safeName, PATHINFO_EXTENSION);
                                    $safeName = $nameWithoutExt . '_' . $galleryCounter . '.' . $ext;
                                    $targetPath = $galleryDir . '/' . $safeName;
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

                // Создаем ZIP архив
                $zipPath = $tempDir . '.zip';
                $zip = new ZipArchive();
                if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                    // Добавляем CSV файл
                    $zip->addFile($csvPath, 'cases.csv');

                    // Добавляем изображения
                    $this->addDirectoryToZip($zip, $casesImagesDir, 'images/cases');
                    $this->addDirectoryToZip($zip, $iconsDir, 'images/icons');
                    $this->addDirectoryToZip($zip, $galleryDir, 'images/gallery');

                    $zip->close();

                    // Логирование успешного экспорта
                    Log::info('Cases export completed', [
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
                Log::error('Cases export error: ' . $e->getMessage(), [
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
     * Добавить директорию в ZIP архив
     */
    protected function addDirectoryToZip(ZipArchive $zip, string $dir, string $zipPath): void
    {
        if (!File::exists($dir)) {
            return;
        }

        $files = File::allFiles($dir);
        foreach ($files as $file) {
            $relativePath = $zipPath . '/' . $file->getRelativePathname();
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

    /**
     * Экспортировать случаи в CSV (без ZIP)
     */
    public function exportToCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $cases = ProjectCase::with(['chapter', 'image', 'icon', 'images', 'services', 'products'])
            ->orderBy('order')
            ->get();

        $filename = 'cases_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($cases) {
            $file = fopen('php://output', 'w');

            // BOM для правильного отображения кириллицы в Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Заголовки (* - обязательные поля)
            fputcsv($file, [
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

            // Данные
            foreach ($cases as $case) {
                // Извлекаем имена файлов для пути (для использования в ZIP)
                $imagePath = '';
                $iconPath = '';
                $galleryPaths = [];

                if ($case->image) {
                    $imagePath = basename($case->image->original_name ?? $case->image->name);
                    $imagePath = 'images/cases/' . $imagePath;
                }
                if ($case->icon) {
                    $iconPath = basename($case->icon->original_name ?? $case->icon->name);
                    $iconPath = 'images/icons/' . $iconPath;
                }
                if ($case->images) {
                    foreach ($case->images as $galleryImage) {
                        $galleryPaths[] = 'images/gallery/' . basename($galleryImage->original_name ?? $galleryImage->name);
                    }
                }
                
                // Получаем ID связей
                $servicesIds = $case->services ? $case->services->pluck('id')->implode(',') : '';
                $productsIds = $case->products ? $case->products->pluck('id')->implode(',') : '';
                $galleryIds = $case->images ? $case->images->pluck('id')->implode(',') : '';

                fputcsv($file, [
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

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

