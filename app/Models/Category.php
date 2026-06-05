<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'parent_id', 'image', 'is_active'];

    protected $casts = [
        'is_active' => \App\Casts\PostgresBoolean::class,
    ];

    /**
     * Boot the model and register cache-invalidation observers.
     */
    protected static function booted(): void
    {
        // Clear the navbar cache whenever a category is created, updated, or deleted.
        $clearCache = fn() => Cache::forget('navbar_categories');

        static::saved($clearCache);
        static::deleted($clearCache);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getNameAttribute($value)
    {
        if (request()->is('admin*')) {
            return $value;
        }
        return Lang::has($value) ? __($value) : $value;
    }
}
