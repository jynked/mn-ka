<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class OptionTree extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'parent',
        'sort',
        'is_active',
    ];

    protected $casts = [
        'parent' => 'integer',
        'sort' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $with = ['items'];

    /**
     * Дочерние элементы дерева
     */
    public function items(): HasMany
    {
        return $this->hasMany(OptionTree::class, 'parent', 'id')
            ->orderBy('sort');
    }

    /**
     * Родительский элемент
     */
    public function parentItem()
    {
        return $this->belongsTo(OptionTree::class, 'parent', 'id');
    }

    /**
     * Услуги, связанные с деревом опций
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'option_tree_service')
            ->withTimestamps();
    }

    /**
     * Автоматическое форматирование имени
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => Str::ucfirst($value),
        );
    }

    /**
     * Scope для активных элементов дерева
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
        return $query->orderBy('sort');
    }

    /**
     * Scope для корневых элементов (без родителя)
     */
    public function scopeRoot($query)
    {
        return $query->where('parent', 0);
    }
}
