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
     * Импортировать разделы из ZIP архива
     */
    public function importFromZip($zipFile): array
    {
        $this->errors = [];
        $this->successCount = 0;
        $this->skipCount = 0;
        $this->extractPath = null;

        try {
            // Логирование начала импорта
            Log::info('Chapters import started', [
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
            $csvPath = $extractResult['csvPath'];

            // Импортируем из CSV
            $result = $this->importFromCsvPath($csvPath);

            // Логирование завершения импорта
            Log::info('Chapters import completed', [
                'success_count' => $this->successCount,
                'skip_count' => $this->skipCount,
                'errors_count' => count($this->errors),
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Chapters import error: ' . $e->getMessage(), [
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
     * Импортировать разделы из CSV (для обратной совместимости)
     */
    public function importFromCsv($file): array
    {
        return $this->importFromCsvPath($file->getRealPath(), null);
    }

    /**
     * Импортировать разделы из CSV файла по пути
     */
    protected function importFromCsvPath(string $csvPath): array
    {
        $this->errors = [];
        $this->successCount = 0;
        $this->skipCount = 0;

        $handle = fopen($csvPath, 'r');
        
        if (!$handle) {
            return [
                'success' => false,
                'message' => 'Не удалось открыть CSV файл',
            ];
        }

        // Пропускаем первую строку (заголовки)
        $headers = fgetcsv($handle, 0, ';');
        
        // Обрабатываем BOM если есть
        if (!empty($headers[0]) && str_starts_with($headers[0], "\xEF\xBB\xBF")) {
            $headers[0] = substr($headers[0], 3);
        }
        
        // Проверяем формат файла
        $validationResult = $this->validateHeaders($headers);
        if ($validationResult !== true) {
            fclose($handle);
            return [
                'success' => false,
                'message' => 'Неверный формат файла. Ожидаются определенные заголовки.',
                'errors' => [
                    [
                        'row' => 1,
                        'errors' => is_array($validationResult) ? $validationResult : [$validationResult],
                        'data' => [
                            'found_headers' => $headers,
                            'expected_headers' => ['ID', 'Название*', 'Порядок', 'Активен'],
                        ],
                    ],
                ],
            ];
        }

        $rowNumber = 1;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $rowNumber++;
            
            // Пропускаем пустые строки
            if (empty(array_filter($row))) {
                continue;
            }

            // Парсим данные строки
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
                    // Не используем ID при создании нового раздела
                    unset($data['id']);
                    
                    // Определяем order если не указан
                    if (!isset($data['order'])) {
                        $maxOrder = Chapter::max('order') ?? -1;
                        $data['order'] = $maxOrder + 1;
                    }
                    
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
     * Проверить заголовки CSV файла
     * @return bool|array true если валидно, массив ошибок если нет
     */
    protected function validateHeaders(array $headers): bool|array
    {
        // Убираем звездочки из заголовков для проверки
        $cleanHeaders = array_map(function($header) {
            return trim(str_replace('*', '', $header));
        }, $headers);
        
        $requiredHeaders = ['Название'];
        $missingHeaders = [];
        
        foreach ($requiredHeaders as $required) {
            if (!in_array($required, $cleanHeaders)) {
                $missingHeaders[] = $required;
            }
        }
        
        if (!empty($missingHeaders)) {
            return [
                "Отсутствуют обязательные заголовки: " . implode(', ', $missingHeaders),
                "Найденные заголовки: " . implode(', ', $cleanHeaders),
                "Ожидаемые заголовки: ID, Название*, Порядок, Активен",
            ];
        }
        
        return true;
    }

    /**
     * Парсить строку CSV в массив данных
     */
    protected function parseRow(array $row, array $headers): array
    {
        $data = [];
        
        foreach ($headers as $index => $header) {
            $value = $row[$index] ?? '';
            // Убираем звездочку и пробелы из заголовка
            $header = trim(str_replace('*', '', $header));
            
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


