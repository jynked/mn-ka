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
                // Логирование начала экспорта
                Log::info('Chapters export started', [
                    'count' => $chapters->count(),
                    'temp_dir' => $tempDir,
                ]);

                // Создаем CSV файл
                $csvPath = $tempDir . '/chapters.csv';
                $csvFile = fopen($csvPath, 'w');

                // BOM для правильного отображения кириллицы в Excel
                fprintf($csvFile, chr(0xEF).chr(0xBB).chr(0xBF));

                // Заголовки (* - обязательные поля)
                fputcsv($csvFile, [
                    'ID',
                    'Название*',
                    'Порядок',
                    'Активен',
                ], ';');

                // Обрабатываем разделы
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
                    // Добавляем CSV файл
                    $zip->addFile($csvPath, 'chapters.csv');

                    $zip->close();

                    // Логирование успешного экспорта
                    Log::info('Chapters export completed', [
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
                Log::error('Chapters export error: ' . $e->getMessage(), [
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
     * Экспортировать разделы в CSV (без ZIP)
     */
    public function exportToCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $chapters = Chapter::orderBy('order')->get();

        $filename = 'chapters_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($chapters) {
            $file = fopen('php://output', 'w');

            // BOM для правильного отображения кириллицы в Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Заголовки (* - обязательные поля)
            fputcsv($file, [
                'ID',
                'Название*',
                'Порядок',
                'Активен',
            ], ';');

            // Данные
            foreach ($chapters as $chapter) {
                fputcsv($file, [
                    $chapter->id,
                    $chapter->name,
                    $chapter->order ?? 0,
                    $chapter->is_active ? '1' : '0',
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

