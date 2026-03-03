<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FaqBlockSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class FaqBlockSettingsController extends Controller
{
    /**
     * Получить настройки блока
     */
    public function show()
    {
        try {
            $settings = FaqBlockSettings::getSettings();
            
            return response()->json([
                'data' => $settings,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching FAQ block settings', [
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
            $settings = FaqBlockSettings::getSettings();

            $validator = Validator::make($request->all(), [
                'title' => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean',
                'faq_items' => 'nullable|array|min:0',
                'faq_items.*.question' => 'required_with:faq_items|string|max:500',
                'faq_items.*.answer' => 'required_with:faq_items|string|max:5000',
                'additional_settings' => 'nullable|array',
            ], [
                'faq_items.*.question.required_with' => 'Вопрос обязателен для заполнения',
                'faq_items.*.question.max' => 'Вопрос не должен превышать 500 символов',
                'faq_items.*.answer.required_with' => 'Ответ обязателен для заполнения',
                'faq_items.*.answer.max' => 'Ответ не должен превышать 5000 символов',
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
                'faq_items',
                'additional_settings',
            ]);

            // Очистка пустых вопросов
            if (isset($data['faq_items']) && is_array($data['faq_items'])) {
                $data['faq_items'] = array_values(array_filter($data['faq_items'], function ($item) {
                    return !empty($item['question']) && !empty($item['answer']);
                }));
            }

            // Тримим строки
            if (isset($data['title'])) {
                $data['title'] = trim($data['title']);
            }

            if (isset($data['faq_items'])) {
                foreach ($data['faq_items'] as &$item) {
                    $item['question'] = trim($item['question'] ?? '');
                    $item['answer'] = trim($item['answer'] ?? '');
                }
                unset($item);
            }

            $settings->update($data);

            // Перезагружаем модель для получения актуальных данных
            $settings->refresh();

            return response()->json([
                'message' => 'Настройки блока успешно обновлены',
                'data' => $settings,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating FAQ block settings', [
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
