<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChapterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Получаем активные продукты и услуги из загруженных данных
        $products = $this->whenLoaded('products', function() {
            return $this->products->filter(function($product) {
                return $product->is_active;
            })->values();
        }, collect());
        
        $services = $this->whenLoaded('services', function() {
            return $this->services->filter(function($service) {
                return $service->is_active;
            })->values();
        }, collect());
        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'active' => $this->is_active, // Для совместимости с фронтендом
            'products' => ProductResource::collection($products),
            'services' => ServiceResource::collection($services),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
