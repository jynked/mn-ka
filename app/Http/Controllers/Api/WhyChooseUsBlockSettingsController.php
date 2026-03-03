<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhyChooseUsBlockSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WhyChooseUsBlockSettingsController extends Controller
{
    /**
     * Получить настройки блока
     */
    public function show()
    {
        try {
            $settings = WhyChooseUsBlockSettings::getSettings();
            
            // Получаем данные модели (уже преобразованные в массив через cast)
            $items = $settings->items ?? [];
            
            // Убеждаемся, что items - это массив
            if (!is_array($items)) {
                $items = json_decode($items, true) ?? [];
            }
            
            // Загружаем иконки для каждой карточки
            if (!empty($items) && is_array($items)) {
                foreach ($items as &$item) {
                    if (isset($item['icon_id']) && !empty($item['icon_id'])) {
                        try {
                            $icon = \App\Models\Media::find($item['icon_id']);
                            if ($icon) {
                                $item['icon'] = [
                                    'id' => $icon->id,
                                    'url' => $icon->url,
                                    'name' => $icon->name,
                                    'disk' => $icon->disk,
                                ];
                            }
                        } catch (\Exception $e) {
                            \Log::error('Error loading icon: ' . $e->getMessage());
                        }
                    }
                }
                unset($item);
            }
            
            // Формируем ответ
            return response()->json([
                'data' => [
                    'id' => $settings->id,
                    'title' => $settings->title ?? 'Почему выбирают нас',
                    'is_active' => (bool) ($settings->is_active ?? true),
                    'items' => $items,
                    'additional_settings' => $settings->additional_settings ?? [],
                    'created_at' => $settings->created_at?->toDateTimeString(),
                    'updated_at' => $settings->updated_at?->toDateTimeString(),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in WhyChooseUsBlockSettingsController::show: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'message' => 'Ошибка при загрузке настроек блока',
                'error' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null,
            ], 500);
        }
    }

    /**
     * Обновить настройки блока
     */
    public function update(Request $request)
    {
        $settings = WhyChooseUsBlockSettings::getSettings();

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'items' => 'nullable|array|max:6',
            'items.*.text' => 'required_with:items|string|max:500',
            'items.*.icon_id' => 'nullable|integer|exists:media,id',
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
            'is_active',
            'items',
            'additional_settings',
        ]));

        return response()->json([
            'message' => 'Настройки блока успешно обновлены',
            'data' => $settings,
        ]);
    }
}

