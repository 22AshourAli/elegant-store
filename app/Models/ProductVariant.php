<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Lang;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ProductVariant extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'product_id', 'sku', 'color', 'size', 'price_override',
        'cost_price',
        'is_active', 'is_default', 'image_url'
    ];

    protected $casts = [
        'is_active' => \App\Casts\PostgresBoolean::class,
        'is_default' => \App\Casts\PostgresBoolean::class,
    ];

    protected $appends = [
        'current_price',
        'total_stock',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_product_variant')
                    ->withPivot('stock')
                    ->withTimestamps();
    }

    public function getCurrentPriceAttribute()
    {
        $base = $this->price_override ?? $this->product->base_price;
        if ($this->product->isOnSale) {
            return $this->product->current_price;
        }
        return $base;
    }

    // المخزون المتاح في فرع معين (أو كل الفروع)
    public function getStockInBranch($branchId)
    {
        $branch = $this->branches()->where('branch_id', $branchId)->first();
        return $branch ? $branch->pivot->stock : 0;
    }

    public function getTotalStockAttribute()
    {
        if ($this->relationLoaded('branches')) {
            return $this->branches->sum(fn($branch) => $branch->pivot->stock);
        }
        return $this->branches()->sum('branch_product_variant.stock');
    }

    public function imageUrl(): string
    {
        if (!empty($this->image_url)) {
            return $this->image_url;
        }
        return $this->getFirstMediaUrl('variant_images', 'thumb')
            ?: $this->getFirstMediaUrl('variant_images');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('variant_images')
             ->singleFile(false);
    }

    public function getColorAttribute($value)
    {
        if (request()->is('admin*')) {
            return $value;
        }
        return Lang::has($value) ? __($value) : $value;
    }

    public function getNameAttribute()
    {
        $name = $this->product->name;
        $parts = array_filter([$this->color, $this->size]);
        if (!empty($parts)) {
            $name .= ' (' . implode(', ', $parts) . ')';
        }
        return $name;
    }
}
