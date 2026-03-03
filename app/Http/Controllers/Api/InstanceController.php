<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InstanceResource;
use App\Models\Instance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InstanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Instance::ordered();

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        } else {
            $query->active();
        }

        $instances = $query->get();

        return response()->json([
            'data' => InstanceResource::collection($instances),
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

        $data = $request->only(['name', 'order', 'is_active']);

        if (!isset($data['order'])) {
            $maxOrder = Instance::max('order') ?? -1;
            $data['order'] = $maxOrder + 1;
        }

        $instance = Instance::create($data);

        return response()->json([
            'message' => 'Экземпляр успешно создан',
            'data' => new InstanceResource($instance),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $instance = Instance::findOrFail($id);
        
        return response()->json([
            'data' => new InstanceResource($instance),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $instance = Instance::findOrFail($id);

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

        $instance->update($request->only(['name', 'order', 'is_active']));

        return response()->json([
            'message' => 'Экземпляр успешно обновлен',
            'data' => new InstanceResource($instance),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $instance = Instance::findOrFail($id);
        $instance->delete();

        return response()->json(null, 204);
    }
}
