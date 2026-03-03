<?php

namespace App\Http\Resources;

use App\Models\AppCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Получить доступные разделы с их случаями
     */
    private function getAvailableChapters(): array
    {
        // Получаем все случаи, связанные с услугой
        // Если связь уже загружена, используем её, иначе загружаем
        if ($this->relationLoaded('cases')) {
            $cases = $this->cases->where('is_active', true);
        } else {
            $cases = $this->cases()->with('chapter')->where('is_active', true)->get();
        }
        
        // Группируем случаи по разделам
        $chaptersMap = [];
        foreach ($cases as $case) {
            // Если chapter не загружен, загружаем его
            if (!$case->relationLoaded('chapter')) {
                $case->load('chapter');
            }
            
            if (!$case->chapter || !$case->chapter->is_active) {
                continue;
            }
            
            $chapterId = $case->chapter->id;
            if (!isset($chaptersMap[$chapterId])) {
                $chaptersMap[$chapterId] = [
                    'id' => $case->chapter->id,
                    'name' => $case->chapter->name,
                    'order' => $case->chapter->order ?? 0,
                    'cases' => [],
                ];
            }
            
            $chaptersMap[$chapterId]['cases'][] = [
                'id' => $case->id,
                'name' => $case->name,
            ];
        }
        
        // Сортируем разделы по order
        usort($chaptersMap, function($a, $b) {
            return ($a['order'] ?? 0) <=> ($b['order'] ?? 0);
        });
        
        // Сортируем случаи внутри каждого раздела
        foreach ($chaptersMap as &$chapter) {
            usort($chapter['cases'], function($a, $b) {
                return 0; // Можно добавить сортировку по order если нужно
            });
        }
        
        return array_values($chaptersMap);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'html_content' => $this->html_content,
            'image_id' => $this->image_id,
            'image' => $this->whenLoaded('image', function() {
                return new MediaResource($this->image);
            }),
            'icon_id' => $this->icon_id,
            'icon' => $this->whenLoaded('icon', function() {
                return new MediaResource($this->icon);
            }),
            'chapter_id' => $this->chapter_id,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'category' => 'services', // Для фронтенда
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'options' => $this->whenLoaded('options', function() {
                return $this->options->map(function($option) {
                    return [
                        'id' => $option->id,
                        'name' => $option->name,
                    ];
                })->toArray();
            }),
            'option_trees' => $this->whenLoaded('optionTrees', function() {
                return $this->optionTrees->map(function($tree) {
                    $items = [];
                    if ($tree->relationLoaded('items') && $tree->items) {
                        $items = $tree->items->map(function($item) {
                            return [
                                'id' => $item->id,
                                'name' => $item->name,
                                'parent' => $item->parent,
                                'sort' => $item->sort,
                            ];
                        })->toArray();
                    }
                    return [
                        'id' => $tree->id,
                        'name' => $tree->name,
                        'parent' => $tree->parent,
                        'sort' => $tree->sort,
                        'items' => $items,
                    ];
                })->toArray();
            }),
            'instances' => $this->whenLoaded('instances', function() {
                return $this->instances->map(function($instance) {
                    return [
                        'id' => $instance->id,
                        'name' => $instance->name,
                    ];
                })->toArray();
            }),
            // Получаем все активные разделы с их случаями для выбора
            // Разделы берутся из случаев, связанных с услугой
            'available_chapters' => $this->getAvailableChapters(),
            // Всегда возвращаем категории заявителя
            'app_categories' => AppCategory::all()->map(function($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                ];
            })->toArray(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
