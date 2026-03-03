<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FooterSettingsResource extends JsonResource
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
            'title' => $this->title,
            'department_label' => $this->department_label,
            'department_phone' => $this->department_phone,
            'objects_label' => $this->objects_label,
            'objects_phone' => $this->objects_phone,
            'issues_label' => $this->issues_label,
            'issues_email' => $this->issues_email,
            'social_networks' => $this->social_networks,
            'menu_items' => $this->menu_items,
            'privacy_policy_link' => $this->privacy_policy_link,
            'copyright' => $this->copyright,
            'vk_icon_id' => $this->vk_icon_id,
            'vk_icon_svg' => $this->vk_icon_svg,
            'instagram_icon_id' => $this->instagram_icon_id,
            'instagram_icon_svg' => $this->instagram_icon_svg,
            'telegram_icon_id' => $this->telegram_icon_id,
            'telegram_icon_svg' => $this->telegram_icon_svg,
            'vk_icon' => $this->whenLoaded('vkIcon', function () {
                return new MediaResource($this->vkIcon);
            }),
            'instagram_icon' => $this->whenLoaded('instagramIcon', function () {
                return new MediaResource($this->instagramIcon);
            }),
            'telegram_icon' => $this->whenLoaded('telegramIcon', function () {
                return new MediaResource($this->telegramIcon);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

