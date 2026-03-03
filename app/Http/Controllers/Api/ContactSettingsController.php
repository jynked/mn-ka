<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactSettingsController extends Controller
{
    /**
     * Получить настройки контактов
     */
    public function show()
    {
        $settings = ContactSettings::getSettings();
        
        return response()->json([
            'data' => $settings,
        ]);
    }

    /**
     * Обновить настройки контактов
     */
    public function update(Request $request)
    {
        $settings = ContactSettings::getSettings();

        $validator = Validator::make($request->all(), [
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
            'working_hours' => 'nullable|string|max:255',
            'socials' => 'nullable|array',
            'socials.*.icon' => 'nullable|string|max:50',
            'socials.*.title' => 'nullable|string|max:255',
            'socials.*.link' => 'nullable|url|max:500',
        ]);
        
        // Фильтруем пустые социальные сети перед сохранением
        if ($request->has('socials') && is_array($request->socials)) {
            $request->merge([
                'socials' => array_filter($request->socials, function($social) {
                    return !empty($social['icon']) && !empty($social['title']) && !empty($social['link']);
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
            'phone',
            'email',
            'address',
            'working_hours',
            'socials',
        ]));

        return response()->json([
            'message' => 'Настройки контактов успешно обновлены',
            'data' => $settings->fresh(),
        ]);
    }
}

