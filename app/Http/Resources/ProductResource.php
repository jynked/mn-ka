<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
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
            'short_description' => $this->short_description,
            'page_title' => $this->page_title,
            'page_subtitle' => $this->page_subtitle,
            'cta_text' => $this->cta_text,
            'cta_link' => $this->cta_link,
            'image_id' => $this->image_id,
            'image' => $this->whenLoaded('image', function() {
                return new MediaResource($this->image);
            }),
            'icon_id' => $this->icon_id,
            'icon' => $this->whenLoaded('icon', function() {
                return new MediaResource($this->icon);
            }),
            'card_preview_image_id' => $this->card_preview_image_id,
            'card_preview_image' => $this->whenLoaded('cardPreviewImage', function() {
                return new MediaResource($this->cardPreviewImage);
            }),
            'chapter_id' => $this->chapter_id,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'category' => 'products', // Для фронтенда
            'services' => ServiceResource::collection($this->whenLoaded('services')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
