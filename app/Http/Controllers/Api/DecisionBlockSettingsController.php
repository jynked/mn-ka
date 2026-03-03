<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DecisionBlockSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DecisionBlockSettingsController extends Controller
{
    /**
     * Получить настройки блока
     */
    public function show()
    {
        $settings = DecisionBlockSettings::getSettings();
        
        return response()->json([
            'data' => $settings,
        ]);
    }

    /**
     * Обновить настройки блока
     */
    public function update(Request $request)
    {
        $settings = DecisionBlockSettings::getSettings();

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'columns' => 'nullable|integer|min:1|max:6',
            'is_active' => 'nullable|boolean',
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
            'columns',
            'is_active',
            'additional_settings',
        ]));

        return response()->json([
            'message' => 'Настройки блока успешно обновлены',
            'data' => $settings,
        ]);
    }
}
