<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProjectCase extends Model
{
    use HasFactory;

    protected $table = 'cases';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'html',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'image_id',
        'icon_id',
        'chapter_id',
        'is_active',
        'order',
    ];

    protected $casts = [
        'description' => 'array',
        'html' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Раздел, к которому относится кейс
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * Основное изображение
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    /**
     * Иконка
     */
    public function icon(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'icon_id');
    }

    /**
     * Галерея изображений
     */
    public function images(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'cases_image', 'cases_id', 'image_id')
            ->withPivot('order')
            ->orderByPivot('order');
    }

    /**
     * Услуги, связанные с кейсом
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'cases_service', 'cases_id', 'service_id');
    }

    /**
     * Продукты, связанные с кейсом
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'cases_product', 'cases_id', 'product_id');
    }

    /**
     * Scope для активных кейсов
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope для сортировки
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Scope для поиска по slug
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }
}
