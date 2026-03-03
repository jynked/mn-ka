<?php

namespace App\Exports;

use App\Models\Product;
use ZipArchive;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ProductsExport
{
    /**
     * Экспортировать продукты в ZIP архив с CSV и изображениями
     */
    public function exportToZip(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $products = Product::with(['chapter', 'image', 'icon', 'services'])->orderBy('order')->get();

        $filename = 'products_' . date('Y-m-d_His') . '.zip';

        $headers = [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($products) {
            // Создаем временную директорию
            $tempDir = storage_path('app/temp/export_' . uniqid());
            File::makeDirectory($tempDir, 0755, true);

            try {
                // Создаем структуру папок
                $imagesDir = $tempDir . '/images/products';
                $iconsDir = $tempDir . '/images/icons';
                File::makeDirectory($imagesDir, 0755, true);
                File::makeDirectory($iconsDir, 0755, true);

                // Создаем CSV файл
                $csvPath = $tempDir . '/products.csv';
                $csvFile = fopen($csvPath, 'w');

                // BOM для правильного отображения кириллицы в Excel
                fprintf($csvFile, chr(0xEF).chr(0xBB).chr(0xBF));

                // Заголовки (* - обязательные поля)
                fputcsv($csvFile, [
                    'ID',
                    'Название*',
                    'Slug',
                    'Описание',
                    'Раздел ID',
                    'Название раздела',
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

                // Создаем ZIP архив
                $zipPath = $tempDir . '.zip';
                $zip = new ZipArchive();
                if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                    // Добавляем CSV файл
                    $zip->addFile($csvPath, 'products.csv');

                    // Добавляем изображения
                    $this->addDirectoryToZip($zip, $imagesDir, 'images/products');
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
     * Экспортировать продукты в CSV
     */
    public function exportToCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $products = Product::with(['chapter', 'image', 'icon', 'services'])->orderBy('order')->get();

        $filename = 'products_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');

            // BOM для правильного отображения кириллицы в Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Заголовки (* - обязательные поля)
            fputcsv($file, [
                'ID',
                'Название*',
                'Slug',
                'Описание',
                'Раздел ID',
                'Название раздела',
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

            // Данные
            foreach ($products as $product) {
                // Извлекаем имя файла для пути (для использования в ZIP)
                $imagePath = '';
                $iconPath = '';
                if ($product->image) {
                    $imagePath = basename($product->image->original_name ?? $product->image->name);
                    // Можно использовать структуру images/products/filename.jpg
                    $imagePath = 'images/products/' . $imagePath;
                }
                if ($product->icon) {
                    $iconPath = basename($product->icon->original_name ?? $product->icon->name);
                    $iconPath = 'images/icons/' . $iconPath;
                }
                
                // Получаем ID услуг
                $servicesIds = $product->services ? $product->services->pluck('id')->implode(',') : '';

                fputcsv($file, [
                    $product->id,
                    $product->name,
                    $product->slug,
                    is_array($product->description) ? json_encode($product->description, JSON_UNESCAPED_UNICODE) : ($product->description ?? ''),
                    $product->chapter_id ?? '',
                    $product->chapter?->name ?? '',
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

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

