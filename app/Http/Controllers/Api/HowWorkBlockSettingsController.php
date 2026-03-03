<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HowWorkBlockSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HowWorkBlockSettingsController extends Controller
{
    /**
     * Получить настройки блока
     */
    public function show()
    {
        $settings = HowWorkBlockSettings::getSettings();
        
        return response()->json([
            'data' => $settings,
        ]);
    }

    /**
     * Обновить настройки блока
     */
    public function update(Request $request)
    {
        $settings = HowWorkBlockSettings::getSettings();

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:1000',
            'image' => 'nullable|string|max:500',
            'image_alt' => 'nullable|string|max:255',
            'button_text' => 'nullable|string|max:255',
            'button_type' => 'nullable|in:url,method',
            'button_value' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
            'steps' => 'nullable|array',
            'steps.*.point' => 'required_with:steps|in:disc,star',
            'steps.*.title' => 'required_with:steps|string|max:500',
            'steps.*.description' => 'nullable|string|max:1000',
            'additional_settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        $settings->update($request->only([
            'title',
            'subtitle',
            'image',
            'image_alt',
            'button_text',
            'button_type',
            'button_value',
            'is_active',
            'steps',
            'additional_settings',
        ]));

        return response()->json([
            'message' => 'Настройки блока успешно обновлены',
            'data' => $settings,
        ]);
    }
}
