<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $pages = Page::ordered()->get();
            
            return response()->json([
                'data' => $pages,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching pages', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Ошибка получения страниц',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug',
            'content' => 'nullable|string',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'seo_keywords' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
        ], [
            'slug.unique' => 'Страница с таким URL уже существует',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $slug = $request->slug ?: Str::slug($request->title);
            
            // Проверяем конфликты URL
            if (Page::slugExists($slug)) {
                $slug = Page::generateUniqueSlug($slug);
            }

            // Проверяем конфликты с существующими маршрутами
            $conflictRoutes = ['/', '/home', '/products', '/services', '/cases', '/about', '/contact', '/admin', '/login', '/register'];
            if (in_array('/' . $slug, $conflictRoutes)) {
                return response()->json([
                    'message' => 'Ошибка валидации',
                    'errors' => [
                        'slug' => ['Этот URL зарезервирован системой. Используйте другой URL.'],
                    ],
                ], 422);
            }

            $page = Page::create([
                'title' => $request->title,
                'slug' => $slug,
                'content' => $request->content,
                'seo_title' => $request->seo_title,
                'seo_description' => $request->seo_description,
                'seo_keywords' => $request->seo_keywords,
                'is_active' => $request->is_active ?? true,
                'order' => $request->order ?? 0,
            ]);

            return response()->json([
                'message' => 'Страница успешно создана',
                'data' => $page,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating page', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Ошибка создания страницы',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $page = Page::findOrFail($id);
            
            return response()->json([
                'data' => $page,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching page', [
                'error' => $e->getMessage(),
                'page_id' => $id,
            ]);

            return response()->json([
                'message' => 'Страница не найдена',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $page = Page::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug,' . $id,
            'content' => 'nullable|string',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'seo_keywords' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
        ], [
            'slug.unique' => 'Страница с таким URL уже существует',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $slug = $request->slug;
            
            // Если slug изменен, проверяем конфликты
            if ($slug && $slug !== $page->slug) {
                // Проверяем конфликты с существующими страницами
                if (Page::slugExists($slug, $id)) {
                    $slug = Page::generateUniqueSlug($slug, $id);
                }

                // Проверяем конфликты с существующими маршрутами
                $conflictRoutes = ['/', '/home', '/products', '/services', '/cases', '/about', '/contact', '/admin', '/login', '/register'];
                if (in_array('/' . $slug, $conflictRoutes)) {
                    return response()->json([
                        'message' => 'Ошибка валидации',
                        'errors' => [
                            'slug' => ['Этот URL зарезервирован системой. Используйте другой URL.'],
                        ],
                    ], 422);
                }
            }

            $page->update($request->only([
                'title',
                'content',
                'seo_title',
                'seo_description',
                'seo_keywords',
                'is_active',
                'order',
            ]));

            if ($slug) {
                $page->slug = $slug;
                $page->save();
            }

            return response()->json([
                'message' => 'Страница успешно обновлена',
                'data' => $page,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating page', [
                'error' => $e->getMessage(),
                'page_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Ошибка обновления страницы',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $page = Page::findOrFail($id);
            $page->delete();

            return response()->json([
                'message' => 'Страница успешно удалена',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting page', [
                'error' => $e->getMessage(),
                'page_id' => $id,
            ]);

            return response()->json([
                'message' => 'Ошибка удаления страницы',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 500);
        }
    }

    /**
     * Получить страницу по slug (публичный доступ)
     */
    public function getBySlug($slug)
    {
        try {
            $page = Page::active()->bySlug($slug)->first();
            
            if (!$page) {
                return response()->json([
                    'message' => 'Страница не найдена',
                ], 404);
            }
            
            return response()->json([
                'data' => $page,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching page by slug', [
                'error' => $e->getMessage(),
                'slug' => $slug,
            ]);

            return response()->json([
                'message' => 'Ошибка получения страницы',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 500);
        }
    }

    /**
     * Проверить доступность slug
     */
    public function checkSlug(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required|string|max:255',
            'exclude_id' => 'nullable|integer|exists:pages,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        $slug = $request->slug;
        $excludeId = $request->exclude_id;

        // Проверяем конфликты с существующими страницами
        $exists = Page::slugExists($slug, $excludeId);
        
        // Проверяем конфликты с существующими маршрутами
        $conflictRoutes = ['/', '/home', '/products', '/services', '/cases', '/about', '/contact', '/admin', '/login', '/register'];
        $isReserved = in_array('/' . $slug, $conflictRoutes);

        if ($exists || $isReserved) {
            $suggestedSlug = Page::generateUniqueSlug($slug, $excludeId);
            
            return response()->json([
                'available' => false,
                'message' => $exists 
                    ? 'Страница с таким URL уже существует' 
                    : 'Этот URL зарезервирован системой',
                'suggested_slug' => $suggestedSlug,
            ]);
        }

        return response()->json([
            'available' => true,
            'message' => 'URL доступен',
        ]);
    }
}
