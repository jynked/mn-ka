<?php

namespace App\Exports;

use App\Models\Service;
use ZipArchive;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ServicesExport
{
    /**
     * Экспортировать услуги в ZIP архив с CSV и изображениями
     */
    public function exportToZip(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $services = Service::with(['chapter', 'image', 'icon'])->orderBy('order')->get();

        $filename = 'services_' . date('Y-m-d_His') . '.zip';

        $headers = [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($services) {
            // Создаем временную директорию
            $tempDir = storage_path('app/temp/export_' . uniqid());
            File::makeDirectory($tempDir, 0755, true);

            try {
                // Создаем структуру папок
                $imagesDir = $tempDir . '/images/services';
                $iconsDir = $tempDir . '/images/icons';
                File::makeDirectory($imagesDir, 0755, true);
                File::makeDirectory($iconsDir, 0755, true);

                // Создаем CSV файл
                $csvPath = $tempDir . '/services.csv';
                $csvFile = fopen($csvPath, 'w');

                // BOM для правильного отображения кириллицы в Excel
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
                    'ID изображения',
                    'Путь изображения',
                    'URL изображения',
                    'ID иконки',
                    'Путь иконки',
                    'URL иконки',
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
                            $targetPath = $imagesDir . '/' . $safeName;

                            // Если файл уже существует, добавляем число
                            while (file_exists($targetPath)) {
                                $nameWithoutExt = pathinfo($safeName, PATHINFO_FILENAME);
                                $ext = pathinfo($safeName, PATHINFO_EXTENSION);
                                $safeName = $nameWithoutExt . '_' . $imageCounter . '.' . $ext;
                                $targetPath = $imagesDir . '/' . $safeName;
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

                    // Преобразуем description из JSON массива в читаемый текст
                    $descriptionText = $this->formatDescriptionForExport($service->description);
                    
                    // Записываем строку в CSV
                    fputcsv($csvFile, [
                        $service->id,
                        $service->name,
                        $service->slug,
                        $descriptionText,
                        $service->html_content ?? '',
                        $service->chapter_id ?? '',
                        $service->chapter?->name ?? '',
                        $service->image_id ?? '',
                        $imagePath,
                        $service->image?->url ?? '',
                        $service->icon_id ?? '',
                        $iconPath,
                        $service->icon?->url ?? '',
                        $service->order ?? 0,
                        $service->is_active ? '1' : '0',
                    ], ';');
                }

                fclose($csvFile);

                // Создаем ZIP архив
                $zipPath = $tempDir . '.zip';
                $zip = new ZipArchive();
                if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                    // Добавляем CSV файл
                    $zip->addFile($csvPath, 'services.csv');

                    // Добавляем изображения
                    $this->addDirectoryToZip($zip, $imagesDir, 'images/services');
                    $this->addDirectoryToZip($zip, $iconsDir, 'images/icons');

                    $zip->close();

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
                Log::error('ZIP export error: ' . $e->getMessage(), [
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
     * Преобразовать description из массива/JSON в читаемый текст для экспорта
     */
    protected function formatDescriptionForExport($description): string
    {
        if (empty($description)) {
            return '';
        }

        // Если это уже строка (не массив), возвращаем как есть
        if (is_string($description)) {
            // Проверяем, не является ли это JSON строкой
            $decoded = json_decode($description, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->formatDescriptionArray($decoded);
            }
            return $description;
        }

        // Если это массив, форматируем его
        if (is_array($description)) {
            return $this->formatDescriptionArray($description);
        }

        return '';
    }

    /**
     * Форматировать массив description в текст
     */
    protected function formatDescriptionArray(array $description): string
    {
        $parts = [];
        
        // Добавляем краткое описание (ru)
        if (!empty($description['ru'])) {
            $parts[] = $description['ru'];
        }
        
        // Добавляем подробное описание (detailed), если есть
        if (!empty($description['detailed'])) {
            // Разделяем краткое и подробное описание пустой строкой для читаемости
            if (!empty($parts)) {
                $parts[] = ''; // Пустая строка как разделитель
            }
            $parts[] = $description['detailed'];
        }
        
        return implode("\n\n", $parts);
    }

    /**
     * Экспортировать услуги в CSV
     */
    public function exportToCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $services = Service::with(['chapter', 'image', 'icon'])->orderBy('order')->get();

        $filename = 'services_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($services) {
            $file = fopen('php://output', 'w');

            // BOM для правильного отображения кириллицы в Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Заголовки
            fputcsv($file, [
                'ID',
                'Название',
                'Slug',
                'Описание',
                'HTML контент',
                'Раздел ID',
                'Название раздела',
                'ID изображения',
                'Путь изображения',
                'URL изображения',
                'ID иконки',
                'Путь иконки',
                'URL иконки',
                'Порядок',
                'Активен',
            ], ';');

            // Данные
            foreach ($services as $service) {
                // Извлекаем имя файла для пути (для использования в ZIP)
                $imagePath = '';
                $iconPath = '';
                if ($service->image) {
                    $imagePath = basename($service->image->original_name ?? $service->image->name);
                    $imagePath = 'images/services/' . $imagePath;
                }
                if ($service->icon) {
                    $iconPath = basename($service->icon->original_name ?? $service->icon->name);
                    $iconPath = 'images/icons/' . $iconPath;
                }
                
                // Преобразуем description из JSON массива в читаемый текст
                $descriptionText = $this->formatDescriptionForExport($service->description);
                
                fputcsv($file, [
                    $service->id,
                    $service->name,
                    $service->slug,
                    $descriptionText,
                    $service->html_content ?? '',
                    $service->chapter_id ?? '',
                    $service->chapter?->name ?? '',
                    $service->image_id ?? '',
                    $imagePath,
                    $service->image?->url ?? '',
                    $service->icon_id ?? '',
                    $iconPath,
                    $service->icon?->url ?? '',
                    $service->order ?? 0,
                    $service->is_active ? '1' : '0',
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

