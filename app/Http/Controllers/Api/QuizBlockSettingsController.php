<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuizBlockSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuizBlockSettingsController extends Controller
{
    /**
     * Получить настройки блока квиза
     */
    public function show()
    {
        $settings = QuizBlockSettings::getSettings();
        $settings->load('quiz');
        
        return response()->json([
            'data' => $settings,
        ]);
    }

    /**
     * Обновить настройки блока квиза
     */
    public function update(Request $request)
    {
        $settings = QuizBlockSettings::getSettings();

        $validator = Validator::make($request->all(), [
            'quiz_id' => 'nullable|exists:quizzes,id',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        $settings->update($request->only([
            'quiz_id',
            'is_active',
        ]));

        $settings->load('quiz');

        return response()->json([
            'message' => 'Настройки блока квиза успешно обновлены',
            'data' => $settings,
        ]);
    }
}
