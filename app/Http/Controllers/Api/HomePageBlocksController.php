<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HomePageBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class HomePageBlocksController extends Controller
{
    /**
     * Получить все блоки главной страницы
     */
    public function index()
    {
        try {
            $blocks = HomePageBlock::ordered()->get();
            
            return response()->json([
                'data' => $blocks,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching home page blocks', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Ошибка получения блоков',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 500);
        }
    }

    /**
     * Получить блоки для публичной страницы (только активные)
     */
    public function getPublic()
    {
        try {
            $blocks = HomePageBlock::getOrderedBlocks();
            
            return response()->json([
                'data' => $blocks,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching public home page blocks', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Ошибка получения блоков',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 500);
        }
    }

    /**
     * Обновить порядок блоков
     */
    public function updateOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'blocks' => 'required|array',
            'blocks.*.id' => 'required|exists:home_page_blocks,id',
            'blocks.*.order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            foreach ($request->blocks as $blockData) {
                HomePageBlock::where('id', $blockData['id'])
                    ->update(['order' => $blockData['order']]);
            }

            DB::commit();

            $blocks = HomePageBlock::ordered()->get();

            return response()->json([
                'message' => 'Порядок блоков успешно обновлен',
                'data' => $blocks,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error updating home page blocks order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Ошибка обновления порядка блоков',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 500);
        }
    }

    /**
     * Обновить активность блока
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $block = HomePageBlock::findOrFail($id);
            
            if ($request->has('is_active')) {
                $block->is_active = $request->is_active;
            }
            
            $block->save();

            return response()->json([
                'message' => 'Блок успешно обновлен',
                'data' => $block,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating home page block', [
                'error' => $e->getMessage(),
                'block_id' => $id,
            ]);

            return response()->json([
                'message' => 'Ошибка обновления блока',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 500);
        }
    }
}
