<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Кеширование для публичных запросов
        $cacheKey = 'products_' . md5(json_encode($request->all()));
        $cacheTime = 60 * 5; // 5 минут
        
        if ($request->has('slug')) {
            // Для одного продукта используем отдельный кеш
            $slug = $request->slug;
            $cacheKey = "product_slug_{$slug}";
            
            return Cache::remember($cacheKey, $cacheTime, function () use ($request, $slug) {
                $query = Product::with(['image', 'icon', 'cardPreviewImage', 'services.chapter', 'chapter'])->ordered();
                
                // Очищаем slug от слэшей
                $cleanSlug = trim($slug, '/');
                
                $product = $query->where('is_active', true)
                    ->where(function($q) use ($cleanSlug) {
                        $q->where('slug', $cleanSlug)
                          ->orWhere('slug', '/' . $cleanSlug);
                    })
                    ->first();
                    
                if ($product) {
                    return response()->json([
                        'data' => new ProductResource($product),
                    ]);
                }
                return response()->json(['message' => 'Продукт не найден'], 404);
            });
        }

        // Для списка продуктов
        return Cache::remember($cacheKey, $cacheTime, function () use ($request) {
            $query = Product::with(['image', 'icon', 'cardPreviewImage', 'chapter'])->ordered();

            if ($request->has('chapter_id')) {
                $query->where('chapter_id', $request->chapter_id);
            }

            if ($request->has('active')) {
                $query->where('is_active', $request->boolean('active'));
            } else {
                $query->active();
            }

            // Ограничение количества для оптимизации
            $limit = $request->get('limit', 100);
            if ($limit > 0) {
                $query->limit($limit);
            }

            $products = $query->get();

            return response()->json([
                'data' => ProductResource::collection($products),
            ]);
        });
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'description' => 'nullable|array',
            'html_content' => 'nullable|string',
            'image_id' => 'nullable|exists:media,id',
            'icon_id' => 'nullable|exists:media,id',
            'card_preview_image_id' => 'nullable|exists:media,id',
            'short_description' => 'nullable|string|max:500',
            'page_title' => 'nullable|string|max:255',
            'page_subtitle' => 'nullable|string|max:500',
            'cta_text' => 'nullable|string|max:255',
            'cta_link' => 'nullable|string|max:500',
            'chapter_id' => 'nullable|exists:chapters,id',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'services' => 'nullable|array',
            'services.*' => 'exists:services,id',
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
            'card_preview_image_id',
            'short_description',
            'page_title',
            'page_subtitle',
            'cta_text',
            'cta_link',
            'chapter_id',
            'order',
            'is_active',
        ]);

        // Генерируем slug если не указан
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
            // Проверяем уникальность
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

        $product = Product::create($data);

        // Синхронизируем услуги
        if ($request->has('services')) {
            $product->services()->sync($request->services);
        }

        return response()->json([
            'message' => 'Продукт успешно создан',
            'data' => new ProductResource($product->load(['image', 'icon', 'cardPreviewImage', 'services', 'chapter'])),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with(['image', 'icon', 'cardPreviewImage', 'services', 'chapter'])->findOrFail($id);
        
        return response()->json([
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $id,
            'description' => 'nullable|array',
            'html_content' => 'nullable|string',
            'image_id' => 'nullable|exists:media,id',
            'icon_id' => 'nullable|exists:media,id',
            'card_preview_image_id' => 'nullable|exists:media,id',
            'short_description' => 'nullable|string|max:500',
            'page_title' => 'nullable|string|max:255',
            'page_subtitle' => 'nullable|string|max:500',
            'cta_text' => 'nullable|string|max:255',
            'cta_link' => 'nullable|string|max:500',
            'chapter_id' => 'nullable|exists:chapters,id',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'services' => 'nullable|array',
            'services.*' => 'exists:services,id',
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
            'card_preview_image_id',
            'short_description',
            'page_title',
            'page_subtitle',
            'cta_text',
            'cta_link',
            'chapter_id',
            'order',
            'is_active',
        ]);

        // Генерируем slug если не указан и имя изменилось
        if (empty($data['slug']) && isset($data['name']) && $data['name'] !== $product->name) {
            $data['slug'] = Str::slug($data['name']);
            $counter = 1;
            $originalSlug = $data['slug'];
            while (Product::where('slug', $data['slug'])->where('id', '!=', $id)->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $product->update($data);

        // Синхронизируем услуги
        if ($request->has('services')) {
            $product->services()->sync($request->services);
        }

        return response()->json([
            'message' => 'Продукт успешно обновлен',
            'data' => new ProductResource($product->load(['image', 'icon', 'cardPreviewImage', 'services', 'chapter'])),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'message' => 'Продукт успешно удален',
        ]);
    }

    /**
     * Экспортировать продукты в CSV или ZIP
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv'); // По умолчанию CSV
        $export = new ProductsExport();
        
        if ($format === 'zip') {
            return $export->exportToZip();
        }
        
        return $export->exportToCsv();
    }

    /**
     * Показать продукт по slug (публичный метод)
     */
    public function showBySlug(Request $request, string $slug)
    {
        // Убираем слэш из начала и конца, если есть
        $cleanSlug = trim($slug, '/');
        $cacheKey = "product_slug_{$cleanSlug}";
        $cacheTime = 60 * 5; // 5 минут
        
        return Cache::remember($cacheKey, $cacheTime, function () use ($cleanSlug, $slug) {
            // Оптимизированный запрос с eager loading только необходимых полей
            $product = Product::with([
                'image:id,name,disk,metadata,width,height',
                'icon:id,name,disk,metadata',
                'cardPreviewImage:id,name,disk,metadata,width,height',
                'services:id,name,slug',
                'chapter:id,name',
            ])
                ->where('is_active', true)
                ->where(function($query) use ($cleanSlug) {
                    $query->where('slug', $cleanSlug)
                          ->orWhere('slug', '/' . $cleanSlug);
                })
                ->first();
            
            if (!$product) {
                Log::warning("Product not found for slug: {$slug} (cleaned: {$cleanSlug})");
                return response()->json(['message' => 'Продукт не найден'], 404);
            }
            
            return response()->json([
                'data' => new ProductResource($product),
            ]);
        });
    }

    /**
     * Отправить заявку на продукт (публичный метод)
     */
    public function submitRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'comment' => 'nullable|string|max:1000',
            'services' => 'nullable|array',
            'services.*.id' => 'exists:services,id',
            'services.*.active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Получаем продукт
            $product = Product::findOrFail($request->product_id);

            // Создаем заявку
            $productRequest = \App\Models\ProductRequest::create([
                'product_id' => $request->product_id,
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email ?? null,
                'comment' => $request->comment,
                'services' => $request->services ?? [],
                'status' => \App\Models\ProductRequest::STATUS_NEW,
            ]);

            // Добавляем запись в историю
            $productRequest->addHistory(
                \App\Models\RequestHistory::ACTION_CREATED,
                null,
                'Заявка создана через форму на сайте'
            );

            // Получаем всех администраторов и менеджеров для отправки уведомлений
            $adminUsers = \App\Models\User::whereHas('roles', function ($query) {
                $query->whereIn('slug', ['admin', 'manager']);
            })->get();

            // Формируем сообщение для уведомления
            $servicesText = '';
            if (!empty($request->services)) {
                $serviceIds = array_column($request->services, 'id');
                $services = \App\Models\Service::whereIn('id', $serviceIds)->get();
                if ($services->isNotEmpty()) {
                    $servicesText = "\n\nВыбранные услуги:\n" . $services->pluck('name')->implode("\n");
                }
            }

            $notificationTitle = "Новая заявка на продукт";
            $notificationMessage = "👤 <b>Клиент:</b> {$request->name}\n📞 <b>Телефон:</b> {$request->phone}" . 
                ($request->comment ? "\n💬 <b>Комментарий:</b> {$request->comment}" : '') . 
                ($servicesText ? "\n\n{$servicesText}" : '');

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
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'type' => 'product_request',
                    ],
                    true // Отправлять в Telegram
                );
            }

            return response()->json([
                'message' => 'Заявка успешно отправлена',
                'success' => true,
                'data' => [
                    'request_id' => $productRequest->id,
                ],
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error submitting product request', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Ошибка при отправке заявки',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 500);
        }
    }

    /**
     * Импортировать продукты из ZIP архива или CSV
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
        $import = new ProductsImport();
        
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
}
