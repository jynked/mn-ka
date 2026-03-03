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

    /**
     * Импортировать случаи из ZIP архива
     */
    public function importFromZip($zipFile): array
    {
        $this->errors = [];
        $this->successCount = 0;
        $this->skipCount = 0;
        $this->extractPath = null;

        try {
            // Логирование начала импорта
            Log::info('Cases import started', [
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
            $result = $this->importFromCsvPath($csvPath, $extractResult['extractPath']);

            // Логирование завершения импорта
            Log::info('Cases import completed', [
                'success_count' => $this->successCount,
                'skip_count' => $this->skipCount,
                'errors_count' => count($this->errors),
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Cases import error: ' . $e->getMessage(), [
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
     * Импортировать случаи из CSV (для обратной совместимости)
     */
    public function importFromCsv($file): array
    {
        return $this->importFromCsvPath($file->getRealPath(), null);
    }

    /**
     * Импортировать случаи из CSV файла по пути
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
                            'expected_headers' => ['ID', 'Название*', 'Slug*', 'Описание', 'HTML', 'Раздел ID', 'Название раздела', 'SEO Title', 'SEO Description', 'SEO Keywords', 'ID изображения', 'Путь изображения', 'URL изображения', 'ID иконки', 'Путь иконки', 'URL иконки', 'Галерея (ID через запятую)', 'Пути галереи (через запятую)', 'Услуги (ID через запятую)', 'Продукты (ID через запятую)', 'Порядок', 'Активен'],
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
                'html' => 'nullable',
                'chapter_id' => 'nullable|exists:chapters,id',
                'seo_title' => 'nullable|string|max:255',
                'seo_description' => 'nullable|string|max:500',
                'seo_keywords' => 'nullable|string|max:255',
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
                    while (ProjectCase::where('slug', $data['slug'])->exists()) {
                        $data['slug'] = $originalSlug . '-' . $counter;
                        $counter++;
                    }
                }

                // Определяем order если не указан
                if (!isset($data['order'])) {
                    $maxOrder = ProjectCase::where('chapter_id', $data['chapter_id'] ?? null)->max('order') ?? -1;
                    $data['order'] = $maxOrder + 1;
                }

                // Сохраняем галерею и связи для последующей обработки
                $galleryImages = $data['gallery_images'] ?? [];
                $servicesIds = $data['services'] ?? null;
                $productsIds = $data['products'] ?? null;
                unset($data['gallery_images'], $data['services'], $data['products']);

                // Создаем или обновляем случай
                if (!empty($data['id']) && ProjectCase::find($data['id'])) {
                    $case = ProjectCase::find($data['id']);
                    $case->update($data);
                } else {
                    // Не используем ID при создании нового
                    unset($data['id']);
                    $case = ProjectCase::create($data);
                }

                // Синхронизируем галерею изображений
                if (!empty($galleryImages)) {
                    $case->images()->sync($galleryImages);
                }

                // Синхронизируем услуги
                if ($servicesIds !== null) {
                    $ids = array_filter(array_map('intval', explode(',', $servicesIds)));
                    $case->services()->sync($ids);
                }

                // Синхронизируем продукты
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
        
        $requiredHeaders = ['Название', 'Slug'];
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
                "Ожидаемые заголовки: ID, Название*, Slug*, Описание, HTML, Раздел ID, Название раздела, SEO Title, SEO Description, SEO Keywords, ID изображения, Путь изображения, URL изображения, ID иконки, Путь иконки, URL иконки, Галерея (ID через запятую), Пути галереи (через запятую), Услуги (ID через запятую), Продукты (ID через запятую), Порядок, Активен",
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
        $galleryImages = [];
        
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
                                    'data' => ['image_path' => $value],
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
                                    'data' => ['icon_path' => $value],
                                ];
                            }
                        }
                    }
                    break;
                case 'Пути галереи (через запятую)':
                case 'Gallery Paths':
                    // Если указаны пути к изображениям галереи и есть extractPath, загружаем их
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
                                } else {
                                    $this->errors[] = [
                                        'row' => $rowNumber,
                                        'errors' => ["Не удалось загрузить изображение галереи: {$path}"],
                                        'data' => ['gallery_path' => $path],
                                    ];
                                }
                            }
                        }
                    }
                    break;
                case 'Услуги (ID через запятую)':
                case 'Services':
                    $data['services'] = !empty($value) ? trim($value) : null;
                    break;
                case 'Продукты (ID через запятую)':
                case 'Products':
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


