<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChapterResource;
use App\Models\Chapter;
use App\Exports\ChaptersExport;
use App\Imports\ChaptersImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChapterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Chapter::with([
            'products' => function($q) {
                $q->where('is_active', true)
                  ->orderBy('order')
                  ->with('image')
                  ->with('icon');
            },
            'services' => function($q) {
                $q->where('is_active', true)
                  ->orderBy('order')
                  ->with('image')
                  ->with('icon');
            },
        ])->ordered();

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        } else {
            $query->active();
        }

        $chapters = $query->get();

        return response()->json([
            'data' => ChapterResource::collection($chapters),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Определяем order если не указан
        if (!isset($request->order)) {
            $maxOrder = Chapter::max('order') ?? -1;
            $request->merge(['order' => $maxOrder + 1]);
        }

        $chapter = Chapter::create($request->only([
            'name',
            'order',
            'is_active',
        ]));

        return response()->json([
            'message' => 'Раздел успешно создан',
            'data' => new ChapterResource($chapter->load(['activeProducts', 'activeServices'])),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $chapter = Chapter::with(['activeProducts', 'activeServices'])->findOrFail($id);
        
        return response()->json([
            'data' => new ChapterResource($chapter),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $chapter = Chapter::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        $chapter->update($request->only([
            'name',
            'order',
            'is_active',
        ]));

        return response()->json([
            'message' => 'Раздел успешно обновлен',
            'data' => new ChapterResource($chapter->load(['activeProducts', 'activeServices'])),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $chapter = Chapter::findOrFail($id);
        $chapter->delete();

        return response()->json([
            'message' => 'Раздел успешно удален',
        ]);
    }

    /**
     * Обновить порядок разделов
     */
    public function updateOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chapters' => 'required|array',
            'chapters.*.id' => 'required|exists:chapters,id',
            'chapters.*.order' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        foreach ($request->chapters as $item) {
            Chapter::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json([
            'message' => 'Порядок разделов успешно обновлен',
        ]);
    }

    /**
     * Экспортировать разделы в ZIP архив
     */
    public function export()
    {
        $exporter = new ChaptersExport();
        return $exporter->exportToZip();
    }

    /**
     * Импортировать разделы из ZIP архива
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:zip,csv|max:102400', // 100MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        $importer = new ChaptersImport();
        $result = $importer->importFromZip($request->file('file'));

        $statusCode = $result['success'] ? 200 : 422;

        return response()->json($result, $statusCode);
    }
}
