<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Service extends Model
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
     * Раздел, к которому относится услуга
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * Основное изображение
     */
    public function image(): HasOne
    {
        return $this->hasOne(Media::class, 'id', 'image_id');
    }

    /**
     * Иконка
     */
    public function icon(): HasOne
    {
        return $this->hasOne(Media::class, 'id', 'icon_id');
    }

    /**
     * Связанные продукты
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_service')
            ->withTimestamps();
    }

    /**
     * Простые опции услуги
     */
    public function options(): BelongsToMany
    {
        return $this->belongsToMany(Option::class, 'option_service')
            ->withTimestamps();
    }

    /**
     * Древовидные опции услуги
     */
    public function optionTrees(): BelongsToMany
    {
        return $this->belongsToMany(OptionTree::class, 'option_tree_service')
            ->withTimestamps();
    }

    /**
     * Экземпляры услуги
     */
    public function instances(): BelongsToMany
    {
        return $this->belongsToMany(Instance::class, 'instance_service')
            ->withTimestamps();
    }

    /**
     * Случаи, связанные с услугой
     */
    public function cases(): BelongsToMany
    {
        return $this->belongsToMany(ProjectCase::class, 'cases_service', 'service_id', 'cases_id')
            ->withTimestamps();
    }

    /**
     * Scope для активных услуг
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
