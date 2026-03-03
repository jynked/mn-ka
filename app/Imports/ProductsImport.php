<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Chapter;
use App\Models\Media;
use App\Services\ZipImportService;
use App\Services\MediaImportService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ProductsImport
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

    /**
     * Импортировать продукты из ZIP архива или CSV
     */
    public function importFromZip($zipFile): array
    {
        $this->errors = [];
        $this->successCount = 0;
        $this->skipCount = 0;
        $this->extractPath = null;

        try {
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
            $result = $this->importFromCsvPath($csvPath, $extractResult['extractPath']);

            return $result;

        } catch (\Exception $e) {
            Log::error('ZIP import error: ' . $e->getMessage(), [
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
     * Импортировать продукты из CSV (для обратной совместимости)
     */
    public function importFromCsv($file): array
    {
        return $this->importFromCsvPath($file->getRealPath(), null);
    }

    /**
     * Импортировать продукты из CSV файла по пути
     */
    protected function importFromCsvPath(string $csvPath, ?string $extractPath = null): array
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
                            'expected_headers' => ['ID', 'Название*', 'Slug', 'Описание', 'Раздел ID', 'Название раздела', 'SEO Title', 'SEO Description', 'SEO Keywords', 'ID изображения', 'Путь изображения', 'URL изображения', 'ID иконки', 'Путь иконки', 'URL иконки', 'Услуги (ID через запятую)', 'Порядок', 'Активен'],
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
            $data = $this->parseRow($row, $headers, $extractPath, $rowNumber);
            
            // Валидация
            $validator = Validator::make($data, [
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255',
                'description' => 'nullable',
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
                // Генерируем slug если не указан
                if (empty($data['slug']) && !empty($data['name'])) {
                    $data['slug'] = Str::slug($data['name']);
                    $counter = 1;
                    $originalSlug = $data['slug'];
                    while (Product::where('slug', $data['slug'])->exists()) {
                        $data['slug'] = $originalSlug . '-' . $counter;
                        $counter++;
                    }
                }

                // Определяем order если не указан
                if (!isset($data['order'])) {
                    $maxOrder = Product::where('chapter_id', $data['chapter_id'] ?? null)->max('order') ?? -1;
                    $data['order'] = $maxOrder + 1;
                }

                // Создаем или обновляем продукт
                $servicesIds = $data['services'] ?? null;
                unset($data['services']); // Убираем из данных для создания/обновления

                if (!empty($data['id']) && Product::find($data['id'])) {
                    $product = Product::find($data['id']);
                    $product->update($data);
                } else {
                    $product = Product::create($data);
                }

                // Синхронизируем услуги
                if ($servicesIds !== null) {
                    $ids = array_filter(array_map('intval', explode(',', $servicesIds)));
                    $product->services()->sync($ids);
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
     */
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
                "Ожидаемые заголовки: ID, Название*, Slug, Описание, Раздел ID, Название раздела, SEO Title, SEO Description, SEO Keywords, ID изображения, Путь изображения, URL изображения, ID иконки, Путь иконки, URL иконки, Услуги (ID через запятую), Порядок, Активен",
            ];
        }
        
        return true;
    }

    /**
     * Парсить строку CSV в массив данных
     */
    protected function parseRow(array $row, array $headers, ?string $extractPath = null, int $rowNumber = 0): array
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
                case 'Slug':
                    $data['slug'] = !empty($value) ? trim($value) : null;
                    break;
                case 'Описание':
                    $data['description'] = !empty($value) ? $value : null;
                    break;
                case 'ID раздела':
                case 'Раздел ID':
                    $data['chapter_id'] = !empty($value) ? (int)$value : null;
                    break;
                case 'ID изображения':
                    // Если указан ID, используем его
                    if (!empty($value) && is_numeric($value)) {
                        $data['image_id'] = (int)$value;
                    }
                    break;
                case 'Путь изображения':
                case 'Image Path':
                case 'Image':
                    // Если указан путь к изображению и есть extractPath, загружаем его
                    if (!empty($value) && $extractPath) {
                        $imagePath = $this->zipImportService->resolveImagePath($extractPath, trim($value));
                        if ($imagePath) {
                            $media = $this->mediaImportService->uploadImageFromPath($imagePath);
                            if ($media) {
                                $data['image_id'] = $media->id;
                            } else {
                                $this->errors[] = [
                                    'row' => $rowNumber,
                                    'errors' => ["Не удалось загрузить изображение: {$value}"],
                                    'data' => $data,
                                ];
                            }
                        }
                    }
                    break;
                case 'ID иконки':
                    // Если указан ID, используем его
                    if (!empty($value) && is_numeric($value)) {
                        $data['icon_id'] = (int)$value;
                    }
                    break;
                case 'Путь иконки':
                case 'Icon Path':
                case 'Icon':
                    // Если указан путь к иконке и есть extractPath, загружаем её
                    if (!empty($value) && $extractPath) {
                        $iconPath = $this->zipImportService->resolveImagePath($extractPath, trim($value));
                        if ($iconPath) {
                            $media = $this->mediaImportService->uploadImageFromPath($iconPath);
                            if ($media) {
                                $data['icon_id'] = $media->id;
                            } else {
                                $this->errors[] = [
                                    'row' => $rowNumber,
                                    'errors' => ["Не удалось загрузить иконку: {$value}"],
                                    'data' => $data,
                                ];
                            }
                        }
                    }
                    break;
                case 'Порядок':
                    $data['order'] = !empty($value) ? (int)$value : 0;
                    break;
                case 'Активен':
                    $data['is_active'] = ($value === '1' || $value === 'true' || $value === 'да');
                    break;
                case 'Услуги (ID через запятую)':
                case 'Услуги':
                case 'Services':
                    $data['services'] = !empty($value) ? trim($value) : null;
                    break;
            }
        }

        return $data;
    }
}


