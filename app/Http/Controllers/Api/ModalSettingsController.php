<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ModalSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ModalSettingsController extends Controller
{
    /**
     * Получить список всех настроек модальных окон
     */
    public function index()
    {
        try {
            $settings = ModalSettings::orderBy('type')->get();
            
            return response()->json([
                'data' => $settings,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching modal settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Ошибка получения настроек модальных окон',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 500);
        }
    }

    /**
     * Получить настройки конкретного модального окна по типу
     */
    public function show(string $type)
    {
        try {
            $settings = ModalSettings::where('type', $type)->first();
            
            if (!$settings) {
                return response()->json([
                    'message' => 'Настройки модального окна не найдены',
                ], 404);
            }
            
            return response()->json([
                'data' => $settings,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching modal settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Ошибка получения настроек модального окна',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 500);
        }
    }

    /**
     * Создать новые настройки модального окна
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|string|max:255|unique:modal_settings,type',
                'title' => 'nullable|string|max:255',
                'content' => 'nullable|string|max:5000',
                'is_active' => 'nullable|boolean',
                'additional_settings' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Ошибка валидации',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $request->only([
                'type',
                'title',
                'content',
                'is_active',
                'additional_settings',
            ]);

            // Тримим строки
            if (isset($data['title'])) {
                $data['title'] = trim($data['title']);
            }
            if (isset($data['content'])) {
                $data['content'] = trim($data['content']);
            }

            $settings = ModalSettings::create($data);

            return response()->json([
                'message' => 'Настройки модального окна успешно созданы',
                'data' => $settings,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating modal settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Ошибка создания настроек модального окна',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 500);
        }
    }

    /**
     * Обновить настройки модального окна
     */
    public function update(Request $request, string $id)
    {
        try {
            $settings = ModalSettings::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'type' => 'sometimes|required|string|max:255|unique:modal_settings,type,' . $id,
                'title' => 'nullable|string|max:255',
                'content' => 'nullable|string|max:5000',
                'is_active' => 'nullable|boolean',
                'additional_settings' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Ошибка валидации',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $request->only([
                'type',
                'title',
                'content',
                'is_active',
                'additional_settings',
            ]);

            // Тримим строки
            if (isset($data['title'])) {
                $data['title'] = trim($data['title']);
            }
            if (isset($data['content'])) {
                $data['content'] = trim($data['content']);
            }

            $settings->update($data);
            $settings->refresh();

            return response()->json([
                'message' => 'Настройки модального окна успешно обновлены',
                'data' => $settings,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating modal settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'id' => $id,
            ]);

            return response()->json([
                'message' => 'Ошибка обновления настроек модального окна',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 500);
        }
    }

    /**
     * Удалить настройки модального окна
     */
    public function destroy(string $id)
    {
        try {
            $settings = ModalSettings::findOrFail($id);
            $settings->delete();

            return response()->json([
                'message' => 'Настройки модального окна успешно удалены',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting modal settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'id' => $id,
            ]);

            return response()->json([
                'message' => 'Ошибка удаления настроек модального окна',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 500);
        }
    }
}
