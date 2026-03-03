<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuizResource;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuizController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Quiz::with('questions')->ordered();

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $quizzes = $query->get();

        return response()->json([
            'data' => QuizResource::collection($quizzes),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'questions' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Определяем order если не указан
        if (!isset($request->order)) {
            $maxOrder = Quiz::max('order') ?? -1;
            $request->merge(['order' => $maxOrder + 1]);
        }

        $quiz = Quiz::create($request->only([
            'title',
            'description',
            'order',
            'is_active',
        ]));

        // Создаем вопросы, если они переданы
        if ($request->has('questions') && is_array($request->questions)) {
            foreach ($request->questions as $index => $questionData) {
                $quiz->questions()->create([
                    'order' => $questionData['order'] ?? $index,
                    'question_type' => $questionData['question_type'],
                    'question_text' => $questionData['question_text'] ?? null,
                    'question_data' => $questionData['question_data'] ?? null,
                    'is_active' => $questionData['is_active'] ?? true,
                ]);
            }
        }

        return response()->json([
            'message' => 'Квиз успешно создан',
            'data' => new QuizResource($quiz->load('questions')),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $quiz = Quiz::with('questions')->findOrFail($id);
        
        return response()->json([
            'data' => new QuizResource($quiz),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $quiz = Quiz::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'questions' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        $quiz->update($request->only([
            'title',
            'description',
            'order',
            'is_active',
        ]));

        // Обновляем вопросы, если они переданы
        if ($request->has('questions')) {
            // Удаляем старые вопросы
            $quiz->questions()->delete();
            
            // Создаем новые вопросы
            foreach ($request->questions as $index => $questionData) {
                $quiz->questions()->create([
                    'order' => $questionData['order'] ?? $index,
                    'question_type' => $questionData['question_type'],
                    'question_text' => $questionData['question_text'] ?? null,
                    'question_data' => $questionData['question_data'] ?? null,
                    'is_active' => $questionData['is_active'] ?? true,
                ]);
            }
        }

        return response()->json([
            'message' => 'Квиз успешно обновлен',
            'data' => new QuizResource($quiz->load('questions')),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $quiz = Quiz::findOrFail($id);
        $quiz->delete();

        return response()->json([
            'message' => 'Квиз успешно удален',
        ]);
    }

    /**
     * Получить квиз для публичного отображения (только активные)
     */
    public function showPublic(string $id)
    {
        $quiz = Quiz::with(['questions' => function($q) {
            $q->where('is_active', true)->orderBy('order');
        }])->where('is_active', true)->findOrFail($id);
        
        return response()->json([
            'data' => new QuizResource($quiz),
        ]);
    }
}
