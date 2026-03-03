<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Получить все продукты раздела
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class)->orderBy('order');
    }

    /**
     * Получить активные продукты раздела
     */
    public function activeProducts(): HasMany
    {
        return $this->products()->where('is_active', true);
    }

    /**
     * Получить все услуги раздела
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class)->orderBy('order');
    }

    /**
     * Получить активные услуги раздела
     */
    public function activeServices(): HasMany
    {
        return $this->services()->where('is_active', true);
    }

    /**
     * Получить все случаи раздела
     */
    public function cases(): HasMany
    {
        return $this->hasMany(ProjectCase::class)->orderBy('order');
    }

    /**
     * Получить активные случаи раздела
     */
    public function activeCases(): HasMany
    {
        return $this->cases()->where('is_active', true);
    }

    /**
     * Scope для активных разделов
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
}
