<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CasesBlockSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class CasesBlockSettingsController extends Controller
{
    /**
     * Получить настройки блока
     */
    public function show()
    {
        try {
            $settings = CasesBlockSettings::getSettings();
            
            return response()->json([
                'data' => $settings,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching Cases block settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Ошибка получения настроек блока',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 500);
        }
    }

    /**
     * Обновить настройки блока
     */
    public function update(Request $request)
    {
        try {
            $settings = CasesBlockSettings::getSettings();

            $validator = Validator::make($request->all(), [
                'title' => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean',
                'case_ids' => 'nullable|array|max:2',
                'case_ids.*' => 'required|exists:cases,id',
                'additional_settings' => 'nullable|array',
            ], [
                'case_ids.max' => 'Можно выбрать максимум 2 кейса',
                'case_ids.*.exists' => 'Выбранный кейс не найден',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Ошибка валидации',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Санитизация данных
            $data = $request->only([
                'title',
                'is_active',
                'case_ids',
                'additional_settings',
            ]);

            // Ограничиваем количество кейсов до 2
            if (isset($data['case_ids']) && is_array($data['case_ids'])) {
                $data['case_ids'] = array_slice(array_values(array_filter($data['case_ids'])), 0, 2);
            }

            // Тримим строки
            if (isset($data['title'])) {
                $data['title'] = trim($data['title']);
            }

            $settings->update($data);

            // Перезагружаем модель для получения актуальных данных
            $settings->refresh();

            return response()->json([
                'message' => 'Настройки блока успешно обновлены',
                'data' => $settings,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating Cases block settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Ошибка обновления настроек блока',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 500);
        }
    }
}
