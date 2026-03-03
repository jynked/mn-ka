<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $type = $request->get('type');
        $query = Menu::with('children')->ordered();

        if ($type) {
            $query->ofType($type);
        }

        $menus = $query->get();
        
        // Формируем древовидную структуру
        $tree = $this->buildTree($menus);

        return response()->json([
            'data' => $tree,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:500',
            'type' => 'required|in:header,footer,burger',
            'parent_id' => 'nullable|exists:menus,id',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'additional_data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Определяем order если не указан
        if (!isset($request->order)) {
            $maxOrder = Menu::where('type', $request->type)
                ->where('parent_id', $request->parent_id)
                ->max('order') ?? -1;
            $request->merge(['order' => $maxOrder + 1]);
        }

        $menu = Menu::create($request->only([
            'title',
            'slug',
            'url',
            'type',
            'parent_id',
            'order',
            'is_active',
            'additional_data',
        ]));

        return response()->json([
            'message' => 'Пункт меню успешно создан',
            'data' => $menu->load('children'),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $menu = Menu::with('children', 'parent')->findOrFail($id);
        
        return response()->json([
            'data' => $menu,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $menu = Menu::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:500',
            'type' => 'required|in:header,footer,burger',
            'parent_id' => 'nullable|exists:menus,id',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'additional_data' => 'nullable|array',
        ]);

        // Проверка на циклическую ссылку
        if ($request->parent_id && $request->parent_id == $id) {
            return response()->json([
                'message' => 'Элемент не может быть родителем самого себя',
                'errors' => ['parent_id' => ['Элемент не может быть родителем самого себя']],
            ], 422);
        }

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        $menu->update($request->only([
            'title',
            'slug',
            'url',
            'type',
            'parent_id',
            'order',
            'is_active',
            'additional_data',
        ]));

        return response()->json([
            'message' => 'Пункт меню успешно обновлен',
            'data' => $menu->fresh()->load('children', 'parent'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $menu = Menu::findOrFail($id);
        
        // Каскадное удаление дочерних элементов
        $menu->delete();

        return response()->json([
            'message' => 'Пункт меню успешно удален',
        ]);
    }

    /**
     * Обновить порядок элементов меню
     */
    public function updateOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.id' => 'required|exists:menus,id',
            'items.*.order' => 'required|integer|min:0',
            'items.*.parent_id' => 'nullable|exists:menus,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            foreach ($request->items as $item) {
                Menu::where('id', $item['id'])->update([
                    'order' => $item['order'],
                    'parent_id' => $item['parent_id'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Порядок элементов меню успешно обновлен',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Ошибка при обновлении порядка',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Получить публичное меню по типу
     */
    public function getPublicMenu(string $type)
    {
        if (!in_array($type, ['header', 'footer', 'burger'])) {
            return response()->json([
                'message' => 'Недопустимый тип меню',
            ], 422);
        }

        // Загружаем все активные элементы данного типа
        $allMenus = Menu::ofType($type)
            ->active()
            ->ordered()
            ->get();

        // Строим дерево, начиная с корневых элементов
        $tree = $this->buildTree($allMenus);

        return response()->json([
            'data' => $tree,
        ]);
    }

    /**
     * Построить древовидную структуру меню
     */
    protected function buildTree($menus, $parentId = null)
    {
        return $menus
            ->filter(function ($menu) use ($parentId) {
                return $menu->parent_id == $parentId;
            })
            ->values()
            ->map(function ($menu) use ($menus) {
                $children = $this->buildTree($menus, $menu->id);
                $result = [
                    'id' => $menu->id,
                    'title' => $menu->title,
                    'name' => $menu->title, // Для совместимости
                    'slug' => $menu->slug,
                    'url' => $menu->url ?? ($menu->slug ? '/' . ltrim($menu->slug, '/') : null),
                    'type' => $menu->type,
                    'order' => $menu->order,
                    'is_active' => $menu->is_active,
                    'additional_data' => $menu->additional_data,
                ];
                
                if ($children->isNotEmpty()) {
                    $result['children'] = $children;
                    $result['items'] = $children; // Для совместимости с burger menu
                }
                
                return $result;
            });
    }
}
