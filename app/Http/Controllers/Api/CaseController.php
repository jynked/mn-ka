<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CaseResource;
use App\Http\Requests\StoreCaseRequest;
use App\Http\Requests\UpdateCaseRequest;
use App\Models\ProjectCase;
use App\Exports\CasesExport;
use App\Imports\CasesImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = ProjectCase::query();

            // Загружаем отношения только если они нужны
            $with = [];
            if ($request->has('with')) {
                $with = is_array($request->with) ? $request->with : explode(',', $request->with);
            } else {
                $with = ['image', 'icon', 'images'];
            }
            
            $query->with($with);

            if ($request->has('chapter_id')) {
                $query->where('chapter_id', $request->chapter_id);
            }

            if ($request->has('active')) {
                $query->where('is_active', $request->boolean('active'));
            } else {
                $query->active();
            }

            // Исключить определенный кейс
            if ($request->has('offerNot')) {
                $query->where('id', '!=', $request->offerNot);
            }

            // Фильтр по ID (для блока кейсов на главной)
            if ($request->has('ids')) {
                $ids = is_array($request->ids) ? $request->ids : explode(',', $request->ids);
                $ids = array_filter(array_map('intval', $ids));
                if (!empty($ids)) {
                    $query->whereIn('id', $ids);
                    // Сохраняем порядок из запроса
                    $query->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')');
                }
            } else {
                // Применяем стандартную сортировку только если нет фильтра по ids
                $query->ordered();
            }

            // Поиск по slug или id
            if ($request->has('slug')) {
                $case = $query->where('slug', $request->slug)->first();
                if ($case) {
                    return response()->json([
                        'data' => new CaseResource($case),
                    ]);
                }
                return response()->json(['message' => 'Кейс не найден'], 404);
            }

            // Пагинация
            if ($request->has('paginate')) {
                $cases = $query->paginate($request->paginate);
                return response()->json([
                    'data' => CaseResource::collection($cases->items()),
                    'meta' => [
                        'current_page' => $cases->currentPage(),
                        'last_page' => $cases->lastPage(),
                        'per_page' => $cases->perPage(),
                        'total' => $cases->total(),
                    ],
                ]);
            }

            $cases = $query->get();

            return response()->json([
                'data' => CaseResource::collection($cases),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching cases: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'message' => 'Ошибка при загрузке кейсов',
                'error' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null,
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCaseRequest $request)
    {
        $data = $request->validated();

        // Генерируем slug если не указан
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
            // Проверяем уникальность
            $counter = 1;
            $originalSlug = $data['slug'];
            while (ProjectCase::where('slug', $data['slug'])->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        // Определяем order если не указан
        if (!isset($data['order'])) {
            $maxOrder = ProjectCase::where('chapter_id', $data['chapter_id'] ?? null)->max('order') ?? -1;
            $data['order'] = $maxOrder + 1;
        }

        // Извлекаем image_id и icon_id если переданы объектами
        if (isset($data['image_id']) && is_array($data['image_id'])) {
            $data['image_id'] = $data['image_id']['id'] ?? null;
        }
        if (isset($data['icon_id']) && is_array($data['icon_id'])) {
            $data['icon_id'] = $data['icon_id']['id'] ?? null;
        }

        $case = ProjectCase::create($data);

        // Синхронизируем связи
        if ($request->has('services')) {
            $case->services()->sync($request->services);
        }

        if ($request->has('products')) {
            $case->products()->sync($request->products);
        }

        if ($request->has('images')) {
            $imageIds = collect($request->images)->pluck('id')->toArray();
            $case->images()->sync($imageIds);
        }

        $case->load(['image', 'icon', 'services', 'products', 'images', 'chapter']);

        return response()->json([
            'message' => 'Кейс успешно создан',
            'data' => new CaseResource($case),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $query = ProjectCase::query();
            
            // Определяем, является ли параметр числом (ID) или строкой (slug)
            if (is_numeric($id)) {
                $query->where('id', $id);
            } else {
                // Если slug, убираем префикс "/" если есть и ищем по slug
                $slug = ltrim($id, '/');
                $query->where('slug', $slug);
            }

            // Загружаем только необходимые связи
            $case = $query->with(['image', 'icon', 'images', 'chapter'])->first();

            if (!$case) {
                return response()->json(['message' => 'Кейс не найден'], 404);
            }

            return response()->json([
                'data' => new CaseResource($case),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching case: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'id' => $id,
            ]);
            
            return response()->json([
                'message' => 'Ошибка при загрузке кейса',
                'error' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null,
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCaseRequest $request, string $id)
    {
        $case = ProjectCase::findOrFail($id);
        $data = $request->validated();

        // Генерируем slug если не указан и изменилось название
        if (empty($data['slug']) && isset($data['name']) && $data['name'] !== $case->name) {
            $data['slug'] = Str::slug($data['name']);
            // Проверяем уникальность
            $counter = 1;
            $originalSlug = $data['slug'];
            while (ProjectCase::where('slug', $data['slug'])->where('id', '!=', $case->id)->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        // Извлекаем image_id и icon_id если переданы объектами
        if (isset($data['image_id']) && is_array($data['image_id'])) {
            $data['image_id'] = $data['image_id']['id'] ?? null;
        }
        if (isset($data['icon_id']) && is_array($data['icon_id'])) {
            $data['icon_id'] = $data['icon_id']['id'] ?? null;
        }

        $case->update($data);

        // Синхронизируем связи
        if ($request->has('services')) {
            $case->services()->sync($request->services);
        }

        if ($request->has('products')) {
            $case->products()->sync($request->products);
        }

        if ($request->has('images')) {
            $imageIds = collect($request->images)->pluck('id')->toArray();
            $case->images()->sync($imageIds);
        }

        $case->load(['image', 'icon', 'services', 'products', 'images', 'chapter']);

        return response()->json([
            'message' => 'Кейс успешно обновлен',
            'data' => new CaseResource($case),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $case = ProjectCase::findOrFail($id);
        $case->delete();

        return response()->json(null, 204);
    }

    /**
     * Экспортировать случаи в ZIP архив
     */
    public function export()
    {
        $exporter = new CasesExport();
        return $exporter->exportToZip();
    }

    /**
     * Импортировать случаи из ZIP архива
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

        $importer = new CasesImport();
        $result = $importer->importFromZip($request->file('file'));

        $statusCode = $result['success'] ? 200 : 422;

        return response()->json($result, $statusCode);
    }
}
