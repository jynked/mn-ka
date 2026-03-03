<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OptionTreeResource;
use App\Models\OptionTree;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OptionTreeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = OptionTree::with('items')->ordered();

        if ($request->has('parent')) {
            $query->where('parent', $request->parent);
        } else {
            $query->root(); // По умолчанию показываем корневые элементы
        }

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        } else {
            $query->active();
        }

        $trees = $query->get();

        return response()->json([
            'data' => OptionTreeResource::collection($trees),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'parent' => 'nullable|integer|min:0',
            'sort' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $request->only(['name', 'parent', 'sort', 'is_active']);

        if (!isset($data['parent'])) {
            $data['parent'] = 0;
        }

        if (!isset($data['sort'])) {
            $maxSort = OptionTree::where('parent', $data['parent'])->max('sort') ?? -1;
            $data['sort'] = $maxSort + 1;
        }

        $tree = OptionTree::create($data);
        $tree->load('items');

        return response()->json([
            'message' => 'Элемент дерева опций успешно создан',
            'data' => new OptionTreeResource($tree),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tree = OptionTree::with('items')->findOrFail($id);
        
        return response()->json([
            'data' => new OptionTreeResource($tree),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $tree = OptionTree::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'parent' => 'nullable|integer|min:0',
            'sort' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        $tree->update($request->only(['name', 'parent', 'sort', 'is_active']));
        $tree->load('items');

        return response()->json([
            'message' => 'Элемент дерева опций успешно обновлен',
            'data' => new OptionTreeResource($tree),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $tree = OptionTree::findOrFail($id);
        $tree->delete();

        return response()->json(null, 204);
    }
}
