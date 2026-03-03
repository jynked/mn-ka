<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'html_content',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'image_id',
        'icon_id',
        'card_preview_image_id',
        'short_description',
        'page_title',
        'page_subtitle',
        'cta_text',
        'cta_link',
        'chapter_id',
        'order',
        'is_active',
    ];

    protected $casts = [
        'description' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Boot method для автоматической очистки кеша при изменении модели
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($product) {
            $product->clearCache();
        });

        static::deleted(function ($product) {
            $product->clearCache();
        });
    }

    /**
     * Очистить кеш продукта
     */
    public function clearCache()
    {
        // Очищаем кеш конкретного продукта
        $slug = trim($this->slug, '/');
        Cache::forget("product_slug_{$slug}");
        
        // Очищаем кеш списков продуктов (по шаблону)
        $keys = [
            'products_' . md5(json_encode(['active' => 1])),
            'products_' . md5(json_encode(['active' => true])),
            'products_' . md5(json_encode([])),
        ];
        
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        
        // Очищаем все кеши продуктов (если нужно)
        // Можно улучшить, используя теги кеширования, если драйвер поддерживает
        if (config('cache.default') === 'redis') {
            Cache::tags(['products'])->flush();
        }
    }

    /**
     * Изображение продукта
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    /**
     * Иконка продукта
     */
    public function icon(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'icon_id');
    }

    /**
     * Изображение для карточки (превью)
     */
    public function cardPreviewImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'card_preview_image_id');
    }

    /**
     * Глава/категория
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * Услуги, связанные с продуктом
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'product_service');
    }

    /**
     * Scope для активных продуктов
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
        return $query->orderBy('order', 'asc')->orderBy('name', 'asc');
    }

    /**
     * Scope для поиска по slug
     */
    public function scopeBySlug($query, string $slug)
    {
        $cleanSlug = trim($slug, '/');
        return $query->where(function($q) use ($cleanSlug) {
            $q->where('slug', $cleanSlug)
              ->orWhere('slug', '/' . $cleanSlug);
        });
    }
}
