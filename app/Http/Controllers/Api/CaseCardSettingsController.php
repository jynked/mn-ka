<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaseCardSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CaseCardSettingsController extends Controller
{
    /**
     * Получить настройки карточек кейсов
     */
    public function show()
    {
        try {
            $settings = CaseCardSettings::getSettings();
            
            return response()->json([
                'data' => $settings,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in CaseCardSettingsController::show: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'message' => 'Ошибка при загрузке настроек карточек кейсов',
                'error' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null,
            ], 500);
        }
    }

    /**
     * Обновить настройки карточек кейсов
     */
    public function update(Request $request)
    {
        $settings = CaseCardSettings::getSettings();

        $validator = Validator::make($request->all(), [
            'page_title' => 'nullable|string|max:255',
            'page_description' => 'nullable|string|max:1000',
            'items_per_page' => 'nullable|integer|min:1|max:100',
            'show_filters' => 'nullable|boolean',
            'show_breadcrumbs' => 'nullable|boolean',
            'card_aspect_ratio' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        $settings->update($request->only([
            'page_title',
            'page_description',
            'items_per_page',
            'show_filters',
            'show_breadcrumbs',
            'card_aspect_ratio',
        ]));

        return response()->json([
            'message' => 'Настройки карточек кейсов успешно обновлены',
            'data' => $settings->fresh(),
        ]);
    }
}



