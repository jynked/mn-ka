<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreCaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Управляется через middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:500',
            'slug' => 'nullable|string|max:500|unique:cases,slug',
            'description' => 'nullable|array',
            'html' => 'nullable|array',
            'image_id' => 'nullable|exists:media,id',
            'icon_id' => 'nullable|exists:media,id',
            'chapter_id' => 'required|exists:chapters,id',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
            'services' => 'nullable|array',
            'services.*' => 'exists:services,id',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id',
            'images' => 'nullable|array',
            'images.*.id' => 'exists:media,id',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Извлекаем image_id и icon_id если переданы объектами
        if ($this->has('image_id') && is_array($this->image_id)) {
            $this->merge([
                'image_id' => $this->image_id['id'] ?? null,
            ]);
        }

        if ($this->has('icon_id') && is_array($this->icon_id)) {
            $this->merge([
                'icon_id' => $this->icon_id['id'] ?? null,
            ]);
        }

        // Генерируем slug если не указан
        if (empty($this->slug) && !empty($this->name)) {
            $this->merge([
                'slug' => Str::slug($this->name),
            ]);
        }
    }
}
