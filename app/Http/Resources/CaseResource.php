<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CaseResource extends JsonResource
{
    /**
     * Получить массив изображений для кейса
     */
    protected function getCaseImages(): array
    {
        $images = [];
        
        // Сначала добавляем основное изображение, если оно есть (для карточек)
        if ($this->relationLoaded('image') && $this->image) {
            $resource = new MediaResource($this->image);
            $imageData = $resource->resolve(request());
            $imageData['webp'] = $imageData['url'] ?? null;
            $images[] = $imageData;
        }
        
        // Затем добавляем изображения из галереи (если загружены и не дублируются)
        if ($this->relationLoaded('images') && $this->images && !$this->images->isEmpty()) {
            foreach ($this->images as $media) {
                // Пропускаем, если это уже основное изображение
                if ($this->image_id && $media->id === $this->image_id) {
                    continue;
                }
                $resource = new MediaResource($media);
                $imageData = $resource->resolve(request());
                $imageData['webp'] = $imageData['url'] ?? null;
                $images[] = $imageData;
            }
        }
        
        return $images;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Обрабатываем description - может быть JSON-строкой, массивом, объектом или строкой
        $description = $this->description;
        if ($description === null) {
            $description = null;
        } elseif (is_string($description)) {
            // Пробуем декодировать JSON-строку
            $decoded = json_decode($description, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $description = $decoded; // Оставляем как массив для обработки на фронтенде
            } else {
                // Если это не JSON, оставляем как строку
                $description = $description;
            }
        } elseif (is_array($description)) {
            // Уже массив, оставляем как есть
            $description = $description;
        } else {
            // Другие типы преобразуем в строку
            $description = (string) $description;
        }
        
        // Обрабатываем html - может быть JSON-строкой, массивом, объектом или строкой
        $html = $this->html;
        if ($html === null) {
            $html = null;
        } elseif (is_string($html)) {
            // Пробуем декодировать JSON-строку
            $decoded = json_decode($html, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $html = $decoded; // Оставляем как массив для обработки на фронтенде
            } else {
                // Если это не JSON, оставляем как строку
                $html = $html;
            }
        } elseif (is_array($html)) {
            // Уже массив, оставляем как есть
            $html = $html;
        } else {
            // Другие типы преобразуем в строку
            $html = (string) $html;
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $description,
            'html' => $html,
            'image_id' => $this->image_id,
            'image' => $this->when($this->relationLoaded('image') && $this->image, function() {
                $resource = new MediaResource($this->image);
                $imageData = $resource->resolve(request());
                // Добавляем webp для совместимости
                $imageData['webp'] = $imageData['url'] ?? null;
                return $imageData;
            }, null),
            'icon_id' => $this->icon_id,
            'icon' => $this->when($this->relationLoaded('icon') && $this->icon, function() {
                return new MediaResource($this->icon);
            }, null),
            'chapter_id' => $this->chapter_id,
            'chapter' => $this->when($this->relationLoaded('chapter') && $this->chapter, function() {
                return [
                    'id' => $this->chapter->id,
                    'name' => $this->chapter->name,
                ];
            }),
            'services' => $this->whenLoaded('services', function() {
                return $this->services->pluck('id')->toArray();
            }),
            'servicesData' => $this->whenLoaded('services', function() {
                return $this->services->map(function($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                        'slug' => $service->slug,
                    ];
                })->toArray();
            }),
            'products' => $this->whenLoaded('products', function() {
                return $this->products->pluck('id')->toArray();
            }),
            'productsData' => $this->whenLoaded('products', function() {
                return $this->products->map(function($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                    ];
                })->toArray();
            }),
            'images' => $this->getCaseImages(),
            'is_active' => $this->is_active,
            'order' => $this->order,
            'published' => $this->created_at ? $this->created_at->format('d F Y H:i') : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
