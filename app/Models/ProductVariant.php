<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ProductVariant extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'product_id', 'sku', 'color', 'size', 'price_override',
        'sale_price', 'cost_price', 'discount_start', 'discount_end',
        'is_active', 'is_default'
    ];

    protected $casts = [
        'discount_start' => 'datetime',
        'discount_end' => 'datetime',
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

    // السعر النهائي لهذا المتغير
    public function getCurrentPriceAttribute()
    {
        $now = now();
        // نبدأ من الـ price_override إن وجد، وإلا base_price من الأب
        $base = $this->price_override ?? $this->product->base_price;
        if ($this->sale_price &&
            (!$this->discount_start || $this->discount_start <= $now) &&
            (!$this->discount_end || $this->discount_end >= $now)) {
            return $this->sale_price;
        }
        // وإلا نفحص خصم المنتج العام إن لم يحدد المتغير خصماً
        if (!$this->sale_price && $this->product->isOnSale) {
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
