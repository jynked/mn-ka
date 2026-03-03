<?php

namespace App\Imports;

use App\Imports\ChaptersImport;
use App\Imports\ProductsImport;
use App\Imports\ServicesImport;
use App\Imports\CasesImport;
use App\Services\ZipImportService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class DecisionsImport
{
    protected $errors = [];
    protected $successCount = 0;
    protected $skipCount = 0;
    protected $zipImportService;
    protected $extractPath = null;
    protected $results = [];

    public function __construct()
    {
        $this->zipImportService = new ZipImportService();
    }

    /**
     * Импортировать все разделы решений из одного ZIP архива
     */
    public function importAllFromZip($zipFile): array
    {
        $this->errors = [];
        $this->successCount = 0;
        $this->skipCount = 0;
        $this->results = [];
        $this->extractPath = null;

        try {
            // Логирование начала импорта
            Log::info('Full decisions import started', [
                'file' => $zipFile->getClientOriginalName(),
                'size' => $zipFile->getSize(),
            ]);

            // Распаковываем ZIP архив
            $extractResult = $this->zipImportService->extractZip($zipFile->getRealPath());
            
            if (!$extractResult) {
                return [
                    'success' => false,
                    'message' => 'Не удалось распаковать ZIP архив',
                    'errors' => ['Ошибка распаковки архива'],
                ];
            }

            $this->extractPath = $extractResult['extractPath'];

            // Ищем CSV файлы в распакованном архиве
            $chaptersCsvPath = $this->findCsvFile($this->extractPath, 'chapters');
            $productsCsvPath = $this->findCsvFile($this->extractPath, 'products');
            $servicesCsvPath = $this->findCsvFile($this->extractPath, 'services');
            $casesCsvPath = $this->findCsvFile($this->extractPath, 'cases');

            // Импортируем каждый раздел в правильном порядке
            // 1. Сначала разделы (chapters), так как от них зависят остальные
            if ($chaptersCsvPath) {
                $chaptersImporter = new ChaptersImport();
                $result = $chaptersImporter->importFromCsv(new \Illuminate\Http\UploadedFile($chaptersCsvPath, 'chapters.csv'));
                $this->results['chapters'] = $result;
                $this->successCount += $result['success_count'] ?? 0;
                $this->skipCount += $result['skip_count'] ?? 0;
                if (!empty($result['errors'])) {
                    $this->errors = array_merge($this->errors, $this->prefixErrors($result['errors'], 'Chapters'));
                }
            }

            // 2. Продукты и услуги (они связаны друг с другом)
            if ($productsCsvPath) {
                $productsImporter = new ProductsImport();
                // Передаем путь к извлеченному архиву для работы с изображениями
                $result = $this->importFromCsvPath($productsImporter, $productsCsvPath, $this->extractPath);
                $this->results['products'] = $result;
                $this->successCount += $result['success_count'] ?? 0;
                $this->skipCount += $result['skip_count'] ?? 0;
                if (!empty($result['errors'])) {
                    $this->errors = array_merge($this->errors, $this->prefixErrors($result['errors'], 'Products'));
                }
            }

            if ($servicesCsvPath) {
                $servicesImporter = new ServicesImport();
                $result = $this->importFromCsvPath($servicesImporter, $servicesCsvPath, $this->extractPath);
                $this->results['services'] = $result;
                $this->successCount += $result['success_count'] ?? 0;
                $this->skipCount += $result['skip_count'] ?? 0;
                if (!empty($result['errors'])) {
                    $this->errors = array_merge($this->errors, $this->prefixErrors($result['errors'], 'Services'));
                }
            }

            // 3. Случаи (cases) в последнюю очередь, так как они связаны с продуктами и услугами
            if ($casesCsvPath) {
                $casesImporter = new CasesImport();
                $result = $this->importFromCsvPath($casesImporter, $casesCsvPath, $this->extractPath);
                $this->results['cases'] = $result;
                $this->successCount += $result['success_count'] ?? 0;
                $this->skipCount += $result['skip_count'] ?? 0;
                if (!empty($result['errors'])) {
                    $this->errors = array_merge($this->errors, $this->prefixErrors($result['errors'], 'Cases'));
                }
            }

            // Логирование завершения импорта
            Log::info('Full decisions import completed', [
                'success_count' => $this->successCount,
                'skip_count' => $this->skipCount,
                'errors_count' => count($this->errors),
            ]);

            return [
                'success' => true,
                'message' => "Полный импорт завершен. Успешно: {$this->successCount}, Пропущено: {$this->skipCount}",
                'success_count' => $this->successCount,
                'skip_count' => $this->skipCount,
                'errors' => $this->errors,
                'results' => $this->results,
            ];

        } catch (\Exception $e) {
            Log::error('Full decisions import error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Ошибка импорта: ' . $e->getMessage(),
                'errors' => [$e->getMessage()],
            ];
        } finally {
            // Очищаем временную директорию
            if ($this->extractPath) {
                $this->zipImportService->cleanup($this->extractPath);
            }
        }
    }

    /**
     * Найти CSV файл в извлеченной директории
     */
    protected function findCsvFile(string $extractPath, string $name): ?string
    {
        $possiblePaths = [
            $extractPath . '/' . $name . '.csv',
            $extractPath . '/' . ucfirst($name) . '.csv',
            $extractPath . '/csv/' . $name . '.csv',
            $extractPath . '/CSV/' . $name . '.csv',
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // Ищем рекурсивно
        $files = File::allFiles($extractPath);
        foreach ($files as $file) {
            $filename = strtolower($file->getFilename());
            if ($filename === $name . '.csv' || $filename === strtolower($name) . '.csv') {
                return $file->getPathname();
            }
        }

        return null;
    }

    /**
     * Импортировать из CSV с использованием соответствующего импортера
     */
    protected function importFromCsvPath($importer, string $csvPath, string $extractPath): array
    {
        // Используем рефлексию для вызова защищенного метода importFromCsvPath
        $reflection = new \ReflectionClass($importer);
        
        if ($reflection->hasMethod('importFromCsvPath')) {
            $method = $reflection->getMethod('importFromCsvPath');
            $method->setAccessible(true);
            return $method->invoke($importer, $csvPath, $extractPath);
        }

        // Если метод не найден, пробуем стандартный метод
        return $importer->importFromCsv(new \Illuminate\Http\UploadedFile($csvPath, basename($csvPath)));
    }

    /**
     * Добавить префикс к ошибкам для идентификации раздела
     */
    protected function prefixErrors(array $errors, string $prefix): array
    {
        return array_map(function ($error) use ($prefix) {
            if (isset($error['errors']) && is_array($error['errors'])) {
                $error['errors'] = array_map(function ($msg) use ($prefix) {
                    return "[{$prefix}] {$msg}";
                }, $error['errors']);
            }
            if (isset($error['row'])) {
                $error['section'] = $prefix;
            }
            return $error;
        }, $errors);
    }
}

