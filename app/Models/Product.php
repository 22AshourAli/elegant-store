<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'base_price',
        'sale_price', 'discount_start', 'discount_end',
        'has_variants', 'is_active', 'featured',
        'meta_title', 'meta_description', 'image_urls'
    ];

    protected $casts = [
        'discount_start' => 'datetime',
        'discount_end' => 'datetime',
        'has_variants' => \App\Casts\PostgresBoolean::class,
        'is_active' => \App\Casts\PostgresBoolean::class,
        'featured' => \App\Casts\PostgresBoolean::class,
        'image_urls' => 'array',
    ];

    protected $appends = [
        'current_price',
        'is_on_sale',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function colorImages()
    {
        return $this->hasMany(ProductColorImage::class)->orderBy('sort_order');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews()
    {
        return $this->reviews()->where('status', 'approved');
    }

    public function colorGroupedImages(): array
    {
        $groups = [];
        foreach ($this->colorImages as $img) {
            $groups[$img->color][] = $img->image_url;
        }
        return $groups;
    }

    // المنتجات ذات الصلة (نفس التصنيف)
    public function relatedProducts()
    {
        return Product::where('category_id', $this->category_id)
            ->where('id', '!=', $this->id)
            ->active()
            ->limit(8);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getCurrentPriceAttribute()
    {
        $now = now();
        if ($this->sale_price &&
            (!$this->discount_start || $this->discount_start <= $now) &&
            (!$this->discount_end || $this->discount_end >= $now)) {
            return $this->sale_price;
        }
        return $this->base_price;
    }

    public function getIsOnSaleAttribute()
    {
        return $this->current_price < $this->base_price;
    }

    public function getNameAttribute($value)
    {
        if (request()->is('admin*')) {
            return $value;
        }
        return Lang::has($value) ? __($value) : $value;
    }

    public function getDescriptionAttribute($value)
    {
        if (request()->is('admin*')) {
            return $value;
        }
        return Lang::has($value) ? __($value) : $value;
    }

    public function getSkuAttribute()
    {
        if ($this->relationLoaded('variants')) {
            $default = $this->variants->firstWhere('is_default', true);
            if ($default && $default->sku) {
                return $default->sku;
            }
            $first = $this->variants->first();
            return $first?->sku;
        }
        return $this->variants()->where('is_default', true)->value('sku')
            ?? $this->variants()->value('sku');
    }

    public function hasStock(): bool
    {
        // If variants are already eager-loaded, avoid a new DB query entirely.
        if ($this->relationLoaded('variants')) {
            return $this->variants->contains(function ($variant) {
                if ($variant->relationLoaded('branches')) {
                    return $variant->branches->where('pivot.stock', '>', 0)->isNotEmpty();
                }
                return $variant->branches()->where('stock', '>', 0)->exists();
            });
        }

        return $this->variants()->whereHas('branches', fn($q) => $q->where('stock', '>', 0))->exists();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('product_images')
             ->useFallbackUrl('/images/logo.svg')
             ->singleFile(false); // نسمح برفع عدة صور
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
             ->width(368)
             ->height(232)
             ->sharpen(10)
             ->format('webp');

        $this->addMediaConversion('responsive')
             ->withResponsiveImages()
             ->format('webp');
    }

    public function firstImageUrl(): string
    {
        if (!empty($this->image_urls)) {
            return $this->image_urls[0];
        }
        return $this->getFirstMediaUrl('product_images') ?: asset('images/logo.svg');
    }

    public function allImageUrls(): array
    {
        if (!empty($this->image_urls)) {
            return $this->image_urls;
        }
        return $this->getMedia('product_images')->map(fn($m) => $m->getUrl('responsive') ?: $m->getUrl())->toArray();
    }

    public function allImageThumbs(): array
    {
        if (!empty($this->image_urls)) {
            return $this->image_urls;
        }
        return $this->getMedia('product_images')->map(fn($m) => $m->getUrl('thumb') ?: $m->getUrl())->toArray();
    }
}
