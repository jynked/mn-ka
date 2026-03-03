<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $banners = Banner::ordered()->get();
        
        return response()->json([
            'data' => $banners,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:banners,slug',
            'background_image' => 'nullable|string|max:500',
            'heading_1' => 'nullable|string|max:255',
            'heading_2' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'button_text' => 'nullable|string|max:255',
            'button_type' => 'required|in:url,method',
            'button_value' => 'nullable|string|max:500',
            'height_desktop' => 'nullable|integer|min:100|max:2000',
            'height_mobile' => 'nullable|integer|min:100|max:2000',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Определяем order если не указан
        if (!isset($request->order)) {
            $maxOrder = Banner::max('order') ?? -1;
            $request->merge(['order' => $maxOrder + 1]);
        }

        $banner = Banner::create($request->only([
            'title',
            'slug',
            'background_image',
            'heading_1',
            'heading_2',
            'description',
            'button_text',
            'button_type',
            'button_value',
            'height_desktop',
            'height_mobile',
            'is_active',
            'order',
        ]));

        return response()->json([
            'message' => 'Баннер успешно создан',
            'data' => $banner,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $banner = Banner::findOrFail($id);
        
        return response()->json([
            'data' => $banner,
        ]);
    }

    /**
     * Получить баннер по slug
     */
    public function getBySlug(string $slug)
    {
        $banner = Banner::bySlug($slug)->active()->first();
        
        if (!$banner) {
            return response()->json([
                'message' => 'Баннер не найден',
            ], 404);
        }
        
        return response()->json([
            'data' => $banner,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $banner = Banner::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:banners,slug,' . $id,
            'background_image' => 'nullable|string|max:500',
            'heading_1' => 'nullable|string|max:255',
            'heading_2' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'button_text' => 'nullable|string|max:255',
            'button_type' => 'required|in:url,method',
            'button_value' => 'nullable|string|max:500',
            'height_desktop' => 'nullable|integer|min:100|max:2000',
            'height_mobile' => 'nullable|integer|min:100|max:2000',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        $banner->update($request->only([
            'title',
            'slug',
            'background_image',
            'heading_1',
            'heading_2',
            'description',
            'button_text',
            'button_type',
            'button_value',
            'height_desktop',
            'height_mobile',
            'is_active',
            'order',
        ]));

        return response()->json([
            'message' => 'Баннер успешно обновлен',
            'data' => $banner,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $banner = Banner::findOrFail($id);
        $banner->delete();

        return response()->json([
            'message' => 'Баннер успешно удален',
        ]);
    }
}
