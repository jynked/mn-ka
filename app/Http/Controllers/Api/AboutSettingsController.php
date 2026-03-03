<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AboutSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AboutSettingsController extends Controller
{
    /**
     * Получить настройки страницы "О нас"
     */
    public function show()
    {
        $settings = AboutSettings::getSettings();
        
        return response()->json([
            'data' => $settings,
        ]);
    }

    /**
     * Обновить настройки страницы "О нас"
     */
    public function update(Request $request)
    {
        $settings = AboutSettings::getSettings();

        $validator = Validator::make($request->all(), [
            'banner_image' => 'nullable|string|max:500',
            'banner_overlay' => 'nullable|boolean',
            'description' => 'nullable|string',
            'statistics' => 'nullable|array',
            'statistics.*.icon' => 'required_with:statistics|string|max:500',
            'statistics.*.text' => 'required_with:statistics|string|max:500',
            'clients' => 'nullable|array',
            'clients.*.title' => 'required_with:clients|string|max:255',
            'clients.*.description' => 'nullable|string|max:1000',
            'clients.*.icon' => 'nullable|string|max:500',
            'team' => 'nullable|array',
            'team.*.name' => 'required_with:team|string|max:255',
            'team.*.position' => 'nullable|string|max:255',
            'team.*.photo' => 'nullable|string|max:500',
            'benefits' => 'nullable|array',
            'benefits.*.title' => 'required_with:benefits|string|max:255',
            'benefits.*.description' => 'nullable|string|max:1000',
        ]);
        
        // Фильтруем пустые элементы перед сохранением
        if ($request->has('statistics') && is_array($request->statistics)) {
            $request->merge([
                'statistics' => array_filter($request->statistics, function($stat) {
                    return !empty($stat['icon']) && !empty($stat['text']);
                })
            ]);
        }

        if ($request->has('clients') && is_array($request->clients)) {
            $request->merge([
                'clients' => array_filter($request->clients, function($client) {
                    return !empty($client['title']);
                })
            ]);
        }

        if ($request->has('team') && is_array($request->team)) {
            $request->merge([
                'team' => array_filter($request->team, function($member) {
                    return !empty($member['name']);
                })
            ]);
        }

        if ($request->has('benefits') && is_array($request->benefits)) {
            $request->merge([
                'benefits' => array_filter($request->benefits, function($benefit) {
                    return !empty($benefit['title']);
                })
            ]);
        }

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        $settings->update($request->only([
            'banner_image',
            'banner_overlay',
            'description',
            'statistics',
            'clients',
            'team',
            'benefits',
        ]));

        return response()->json([
            'message' => 'Настройки страницы "О нас" успешно обновлены',
            'data' => $settings->fresh(),
        ]);
    }
}





