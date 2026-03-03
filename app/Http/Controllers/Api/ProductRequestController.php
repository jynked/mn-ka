<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductRequestResource;
use App\Models\ProductRequest;
use App\Models\RequestHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProductRequestController extends Controller
{
    /**
     * Получить список заявок
     */
    public function index(Request $request)
    {
        try {
            $query = ProductRequest::with(['product', 'assignedUser', 'creator'])
                ->orderBy('created_at', 'desc');

            // Фильтрация по статусу
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Фильтрация по назначенному пользователю
            if ($request->has('assigned_to')) {
                $query->where('assigned_to', $request->assigned_to);
            }

            // Фильтрация по продукту
            if ($request->has('product_id')) {
                $query->where('product_id', $request->product_id);
            }

            // Поиск
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('comment', 'like', "%{$search}%");
                });
            }

            $perPage = $request->get('per_page', 20);
            $requests = $query->paginate($perPage);

            return response()->json([
                'data' => ProductRequestResource::collection($requests->items()),
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
                'from' => $requests->firstItem(),
                'to' => $requests->lastItem(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching product requests', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Ошибка получения заявок',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 500);
        }
    }

    /**
     * Получить заявку с историей
     */
    public function show(string $id)
    {
        try {
            $request = ProductRequest::with(['product', 'assignedUser', 'creator', 'history.user'])
                ->findOrFail($id);

            return response()->json([
                'data' => new ProductRequestResource($request),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching product request', [
                'error' => $e->getMessage(),
                'id' => $id,
            ]);

            return response()->json([
                'message' => 'Заявка не найдена',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 404);
        }
    }

    /**
     * Обновить заявку (статус, назначение, заметки)
     */
    public function update(Request $request, string $id)
    {
        try {
            $productRequest = ProductRequest::findOrFail($id);
            $user = auth()->user();

            $validator = Validator::make($request->all(), [
                'status' => 'sometimes|required|string|in:new,in_progress,completed,cancelled,rejected',
                'assigned_to' => 'nullable|exists:users,id',
                'notes' => 'nullable|string|max:5000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Ошибка валидации',
                    'errors' => $validator->errors(),
                ], 422);
            }

            DB::beginTransaction();

            $oldStatus = $productRequest->status;
            $oldAssignedTo = $productRequest->assigned_to;
            $changes = [];

            // Обновление статуса
            if ($request->has('status') && $request->status !== $oldStatus) {
                $productRequest->status = $request->status;
                $changes['status'] = [
                    'old' => $oldStatus,
                    'new' => $request->status,
                ];

                // Если статус "completed", устанавливаем дату завершения
                if ($request->status === ProductRequest::STATUS_COMPLETED) {
                    $productRequest->completed_at = now();
                    $productRequest->addHistory(
                        RequestHistory::ACTION_COMPLETED,
                        $user,
                        'Заявка завершена'
                    );
                } elseif ($request->status === ProductRequest::STATUS_CANCELLED) {
                    $productRequest->addHistory(
                        RequestHistory::ACTION_CANCELLED,
                        $user,
                        $request->input('cancel_reason')
                    );
                } elseif ($request->status === ProductRequest::STATUS_REJECTED) {
                    $productRequest->addHistory(
                        RequestHistory::ACTION_REJECTED,
                        $user,
                        $request->input('reject_reason')
                    );
                } else {
                    $productRequest->addHistory(
                        RequestHistory::ACTION_STATUS_CHANGED,
                        $user,
                        "Статус изменен с '{$oldStatus}' на '{$request->status}'",
                        $changes
                    );
                }
            }

            // Обновление назначения
            if ($request->has('assigned_to') && $request->assigned_to != $oldAssignedTo) {
                $oldUser = $oldAssignedTo ? User::find($oldAssignedTo) : null;
                $newUser = $request->assigned_to ? User::find($request->assigned_to) : null;

                $productRequest->assigned_to = $request->assigned_to;
                $changes['assigned_to'] = [
                    'old' => $oldUser ? $oldUser->name : null,
                    'new' => $newUser ? $newUser->name : null,
                ];

                $productRequest->addHistory(
                    RequestHistory::ACTION_ASSIGNED,
                    $user,
                    $newUser 
                        ? "Заявка назначена пользователю: {$newUser->name}"
                        : "Назначение снято",
                    $changes
                );
            }

            // Обновление заметок
            if ($request->has('notes')) {
                $productRequest->notes = $request->notes;
                if ($request->notes) {
                    $productRequest->addHistory(
                        RequestHistory::ACTION_NOTE_ADDED,
                        $user,
                        $request->notes
                    );
                }
            }

            $productRequest->save();

            DB::commit();

            return response()->json([
                'message' => 'Заявка успешно обновлена',
                'data' => new ProductRequestResource($productRequest->load(['product', 'assignedUser', 'creator', 'history.user'])),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating product request', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'id' => $id,
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Ошибка обновления заявки',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 500);
        }
    }

    /**
     * Удалить заявку
     */
    public function destroy(string $id)
    {
        try {
            $productRequest = ProductRequest::findOrFail($id);
            $productRequest->delete();

            return response()->json([
                'message' => 'Заявка успешно удалена',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting product request', [
                'error' => $e->getMessage(),
                'id' => $id,
            ]);

            return response()->json([
                'message' => 'Ошибка удаления заявки',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 500);
        }
    }

    /**
     * Получить статистику по заявкам
     */
    public function stats()
    {
        try {
            $stats = [
                'total' => ProductRequest::count(),
                'new' => ProductRequest::where('status', ProductRequest::STATUS_NEW)->count(),
                'in_progress' => ProductRequest::where('status', ProductRequest::STATUS_IN_PROGRESS)->count(),
                'completed' => ProductRequest::where('status', ProductRequest::STATUS_COMPLETED)->count(),
                'cancelled' => ProductRequest::where('status', ProductRequest::STATUS_CANCELLED)->count(),
                'rejected' => ProductRequest::where('status', ProductRequest::STATUS_REJECTED)->count(),
            ];

            return response()->json([
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching product requests stats', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Ошибка получения статистики',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 500);
        }
    }
}
