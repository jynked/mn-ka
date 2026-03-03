<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Exports\ServicesExport;
use App\Imports\ServicesImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Кеширование для публичных запросов
        $cacheKey = 'services_' . md5(json_encode($request->all()));
        $cacheTime = 60 * 5; // 5 минут
        
        if ($request->has('slug')) {
            $slug = $request->slug;
            $cacheKey = "service_slug_{$slug}";
            
            return Cache::remember($cacheKey, $cacheTime, function () use ($request, $slug) {
                $query = Service::with(['image', 'icon', 'chapter'])->ordered();
                
                $cleanSlug = trim($slug, '/');
                
                $service = $query->where('is_active', true)
                    ->where(function($q) use ($cleanSlug) {
                        $q->where('slug', $cleanSlug)
                          ->orWhere('slug', '/' . $cleanSlug);
                    })
                    ->first();
                    
                if ($service) {
                    return response()->json([
                        'data' => new ServiceResource($service),
                    ]);
                }
                return response()->json(['message' => 'Услуга не найдена'], 404);
            });
        }

        // Для списка услуг - оптимизированный запрос без лишних связей
        // Не используем кеш для списка услуг, так как данные могут быть слишком большими для таблицы cache
        try {
            // Для списка услуг загружаем только необходимые связи
            // Если minimal=1, загружаем только icon (для карточек)
            if ($request->boolean('minimal', false)) {
                $query = Service::with(['icon:id,name,disk,metadata'])->ordered();
            } else {
                $query = Service::with(['image:id,name,disk,metadata,width,height', 'icon:id,name,disk,metadata'])->ordered();
            }

            if ($request->has('chapter_id')) {
                $query->where('chapter_id', $request->chapter_id);
            }

            if ($request->has('active')) {
                $query->where('is_active', $request->boolean('active'));
            } else {
                $query->active();
            }

            // Убираем лимит для списка услуг или делаем его большим
            // Для страницы /services нужно показать все услуги
            $limit = $request->get('limit', 0);
            if ($limit > 0 && $limit < 10000) {
                $query->limit($limit);
            }

            $services = $query->get();

            // Проверяем, нужен ли минимальный набор данных (для карточек)
            $minimal = $request->boolean('minimal', false);

            // Используем минимальный набор данных для списка (оптимизировано)
            $data = $services->map(function($service) use ($minimal) {
                if ($minimal) {
                    // Минимальный набор для карточек: только необходимые поля
                    $iconData = null;
                    if ($service->relationLoaded('icon') && $service->icon) {
                        $iconData = [
                            'url' => $service->icon->url ?? null,
                        ];
                    }
                    
                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                        'slug' => $service->slug,
                        'icon' => $iconData,
                        'order' => $service->order ?? 0,
                    ];
                } else {
                    // Полный набор данных (для обратной совместимости)
                    $imageData = null;
                    if ($service->relationLoaded('image') && $service->image) {
                        $imageData = [
                            'id' => $service->image->id,
                            'url' => $service->image->url ?? null,
                            'alt' => $service->image->alt ?? $service->name,
                        ];
                    }
                    
                    $iconData = null;
                    if ($service->relationLoaded('icon') && $service->icon) {
                        $iconData = [
                            'id' => $service->icon->id,
                            'url' => $service->icon->url ?? null,
                            'alt' => $service->icon->alt ?? $service->name,
                        ];
                    }
                    
                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                        'slug' => $service->slug,
                        'description' => $service->description,
                        'image' => $imageData,
                        'icon' => $iconData,
                        'chapter_id' => $service->chapter_id,
                        'order' => $service->order ?? 0,
                        'is_active' => $service->is_active,
                        'category' => 'services',
                    ];
                }
            });

            return response()->json([
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Ошибка при загрузке услуг: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Ошибка при загрузке услуг',
                'message' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:services,slug',
            'description' => 'nullable|array',
            'html_content' => 'nullable|string',
            'image_id' => 'nullable|exists:media,id',
            'icon_id' => 'nullable|exists:media,id',
            'chapter_id' => 'nullable|exists:chapters,id',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $request->only([
            'name',
            'description',
            'html_content',
            'image_id',
            'icon_id',
            'chapter_id',
            'order',
            'is_active',
        ]);

        // Генерируем slug если не указан
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
            $counter = 1;
            $originalSlug = $data['slug'];
            while (Service::where('slug', $data['slug'])->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        // Определяем order если не указан
        if (!isset($data['order'])) {
            $maxOrder = Service::where('chapter_id', $data['chapter_id'] ?? null)->max('order') ?? -1;
            $data['order'] = $maxOrder + 1;
        }

        $service = Service::create($data);

        return response()->json([
            'message' => 'Услуга успешно создана',
            'data' => new ServiceResource($service->load(['image', 'icon', 'chapter'])),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $service = Service::with(['image', 'icon', 'chapter'])->findOrFail($id);
        
        return response()->json([
            'data' => new ServiceResource($service),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $service = Service::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:services,slug,' . $id,
            'description' => 'nullable|array',
            'html_content' => 'nullable|string',
            'image_id' => 'nullable|exists:media,id',
            'icon_id' => 'nullable|exists:media,id',
            'chapter_id' => 'nullable|exists:chapters,id',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $request->only([
            'name',
            'slug',
            'description',
            'html_content',
            'image_id',
            'icon_id',
            'chapter_id',
            'order',
            'is_active',
        ]);

        // Генерируем slug если не указан и имя изменилось
        if (empty($data['slug']) && isset($data['name']) && $data['name'] !== $service->name) {
            $data['slug'] = Str::slug($data['name']);
            $counter = 1;
            $originalSlug = $data['slug'];
            while (Service::where('slug', $data['slug'])->where('id', '!=', $id)->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $service->update($data);

        return response()->json([
            'message' => 'Услуга успешно обновлена',
            'data' => new ServiceResource($service->load(['image', 'icon', 'chapter'])),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return response()->json([
            'message' => 'Услуга успешно удалена',
        ]);
    }

    /**
     * Показать услугу по slug (публичный метод)
     */
    public function showBySlug(Request $request, string $slug)
    {
        // Убираем слэш из начала и конца, если есть
        $cleanSlug = trim($slug, '/');
        $cacheKey = "service_slug_{$cleanSlug}";
        $cacheTime = 60 * 5; // 5 минут
        
        return Cache::remember($cacheKey, $cacheTime, function () use ($cleanSlug, $slug) {
            // Оптимизированный запрос с eager loading всех необходимых связей
            $service = Service::with([
                'image:id,name,disk,metadata,width,height',
                'icon:id,name,disk,metadata',
                'chapter:id,name',
                'cases.chapter', // Загружаем случаи с их разделами
            ])
                ->where('is_active', true)
                ->where(function($query) use ($cleanSlug) {
                    $query->where('slug', $cleanSlug)
                          ->orWhere('slug', '/' . $cleanSlug);
                })
                ->first();
            
            if (!$service) {
                Log::warning("Service not found for slug: {$slug} (cleaned: {$cleanSlug})");
                return response()->json(['message' => 'Услуга не найдена'], 404);
            }
            
            return response()->json([
                'data' => new ServiceResource($service),
            ]);
        });
    }

    /**
     * Отправить заявку на услугу (публичный метод)
     */
    public function submitRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'comment' => 'nullable|string|max:1000',
            'app_category' => 'nullable|exists:app_categories,id',
            'chapter' => 'nullable|exists:chapters,id',
            'case' => 'nullable|exists:cases,id',
            // Поддержка старых полей для обратной совместимости
            'option_tree' => 'nullable|exists:option_trees,id',
            'instance' => 'nullable|exists:instances,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Получаем услугу
            $service = Service::findOrFail($request->service_id);

            // Формируем сообщение с параметрами
            $message = "Заявка на услугу: {$service->name}\n\n";
            $message .= "Параметры:\n";
            
            if ($request->app_category) {
                $appCategory = \App\Models\AppCategory::find($request->app_category);
                if ($appCategory) {
                    $message .= "Категория заявителя: {$appCategory->name}\n";
                }
            }
            
            // Новая структура: chapter и case
            if ($request->chapter) {
                $chapter = \App\Models\Chapter::find($request->chapter);
                if ($chapter) {
                    $message .= "Цель обращения: {$chapter->name}\n";
                }
            }
            
            if ($request->case) {
                $case = \App\Models\ProjectCase::find($request->case);
                if ($case) {
                    $message .= "Подходящий случай: {$case->name}\n";
                }
            }
            
            // Поддержка старых полей для обратной совместимости
            if ($request->option_tree) {
                $optionTree = \App\Models\OptionTree::find($request->option_tree);
                if ($optionTree) {
                    $message .= "Цель обращения: {$optionTree->name}\n";
                }
            }
            
            if ($request->instance) {
                $instance = \App\Models\Instance::find($request->instance);
                if ($instance) {
                    $message .= "Подходящий случай: {$instance->name}\n";
                }
            }
            
            if ($request->comment) {
                $message .= "\nКомментарий: {$request->comment}";
            }

            // Создаем заявку в product_requests (для отображения в админке /admin/product-requests)
            $productRequest = \App\Models\ProductRequest::create([
                'product_id' => null, // Заявка на услугу, не привязана к продукту
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => null,
                'comment' => $message, // message сохраняем в comment
                'status' => \App\Models\ProductRequest::STATUS_NEW,
            ]);
            
            // Добавляем запись в историю
            $productRequest->addHistory(
                \App\Models\RequestHistory::ACTION_CREATED,
                null,
                'Заявка на услугу создана через форму на сайте'
            );

            // Получаем всех администраторов и менеджеров для отправки уведомлений
            $adminUsers = \App\Models\User::whereHas('roles', function ($query) {
                $query->whereIn('slug', ['admin', 'manager']);
            })->get();

            $notificationTitle = "Новая заявка на услугу";
            $notificationMessage = "👤 <b>Клиент:</b> {$request->name}\n📞 <b>Телефон:</b> {$request->phone}\n\n📋 <b>Услуга:</b> {$service->name}";

            if ($request->option || $request->option_tree || $request->instance || $request->chapter || $request->case) {
                $notificationMessage .= "\n\n<b>Параметры:</b>";
                if ($request->option) {
                    $option = \App\Models\Option::find($request->option);
                    if ($option) {
                        $notificationMessage .= "\n• Категория заявителя: {$option->name}";
                    }
                }
                // Новая структура
                if ($request->chapter) {
                    $chapter = \App\Models\Chapter::find($request->chapter);
                    if ($chapter) {
                        $notificationMessage .= "\n• Цель обращения: {$chapter->name}";
                    }
                }
                if ($request->case) {
                    $case = \App\Models\ProjectCase::find($request->case);
                    if ($case) {
                        $notificationMessage .= "\n• Подходящий случай: {$case->name}";
                    }
                }
                // Старая структура для обратной совместимости
                if ($request->option_tree) {
                    $optionTree = \App\Models\OptionTree::find($request->option_tree);
                    if ($optionTree) {
                        $notificationMessage .= "\n• Цель обращения: {$optionTree->name}";
                    }
                }
                if ($request->instance) {
                    $instance = \App\Models\Instance::find($request->instance);
                    if ($instance) {
                        $notificationMessage .= "\n• Подходящий случай: {$instance->name}";
                    }
                }
            }

            if ($request->comment) {
                $notificationMessage .= "\n\n💬 <b>Комментарий:</b> {$request->comment}";
            }

            // Создаем уведомления для всех администраторов и менеджеров
            $notificationTool = new \App\Services\NotificationTool();
            foreach ($adminUsers as $adminUser) {
                $notificationTool->addNotification(
                    $adminUser,
                    $notificationTitle,
                    $notificationMessage,
                    'info',
                    [
                        'request_id' => $productRequest->id,
                        'service_id' => $service->id,
                        'service_name' => $service->name,
                        'type' => 'service_request',
                    ],
                    true // Отправлять в Telegram
                );
            }

            return response()->json([
                'message' => 'Заявка успешно отправлена',
                'success' => true,
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error submitting service request: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Ошибка при отправке заявки',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 500);
        }
    }

    /**
     * Экспортировать услуги в CSV или ZIP
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv'); // По умолчанию CSV
        $export = new ServicesExport();
        
        if ($format === 'zip') {
            return $export->exportToZip();
        }
        
        return $export->exportToCsv();
    }

    /**
     * Импортировать услуги из ZIP архива или CSV
     */
    public function import(Request $request)
    {
        // Проверяем наличие файла
        if (!$request->hasFile('file')) {
            return response()->json([
                'message' => 'Файл не был загружен. Возможно, файл слишком большой.',
                'errors' => ['Максимальный размер файла: 100MB. Проверьте настройки PHP (upload_max_filesize, post_max_size) и веб-сервера.'],
            ], 422);
        }

        $request->validate([
            'file' => 'required|file|mimes:zip,csv,txt|max:102400', // 100MB для ZIP
        ], [
            'file.max' => 'Размер файла не должен превышать 100MB. Текущий размер: :max KB',
            'file.mimes' => 'Поддерживаются только файлы: zip, csv, txt',
        ]);

        $file = $request->file('file');
        
        // Проверяем размер файла
        $fileSize = $file->getSize();
        $maxSize = 102400 * 1024; // 100MB в байтах
        
        if ($fileSize > $maxSize) {
            return response()->json([
                'message' => 'Файл слишком большой',
                'errors' => [
                    'Размер файла: ' . round($fileSize / 1024 / 1024, 2) . ' MB',
                    'Максимальный размер: 100 MB',
                ],
            ], 422);
        }
        $import = new ServicesImport();
        
        // Определяем тип файла
        $mimeType = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());
        
        // Если это ZIP архив
        if ($extension === 'zip' || in_array($mimeType, ['application/zip', 'application/x-zip-compressed'])) {
            $result = $import->importFromZip($file);
        } else {
            // Если это CSV
            $result = $import->importFromCsv($file);
        }

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
                'errors' => $result['errors'] ?? [],
            ], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'success_count' => $result['success_count'],
            'skip_count' => $result['skip_count'],
            'errors' => $result['errors'],
        ]);
    }

    /**
     * Обновить порядок услуг
     */
    public function updateOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'services' => 'required|array',
            'services.*.id' => 'required|exists:services,id',
            'services.*.order' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        foreach ($request->services as $item) {
            Service::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json([
            'message' => 'Порядок услуг успешно обновлен',
        ]);
    }
}
