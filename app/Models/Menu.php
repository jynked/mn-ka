<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'url',
        'type',
        'parent_id',
        'order',
        'is_active',
        'additional_data',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'additional_data' => 'array',
    ];

    /**
     * Родительский элемент меню
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /**
     * Дочерние элементы меню
     */
    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order');
    }

    /**
     * Активные дочерние элементы
     */
    public function activeChildren(): HasMany
    {
        return $this->children()->where('is_active', true);
    }

    /**
     * Scope для фильтрации по типу
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope для активных элементов
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope для сортировки по order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Получить финальный URL
     */
    public function getFinalUrlAttribute(): ?string
    {
        if ($this->url) {
            return $this->url;
        }
        
        if ($this->slug) {
            return '/' . ltrim($this->slug, '/');
        }
        
        return null;
    }
}
