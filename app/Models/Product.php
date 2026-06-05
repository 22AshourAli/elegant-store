<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        'meta_title', 'meta_description'
    ];

    protected $casts = [
        'discount_start' => 'datetime',
        'discount_end' => 'datetime',
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

    // السعر النهائي الحالي مع الأخذ في الاعتبار الخصم الزمني
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

    // التحقق مما إذا كان المنتج عليه خصم نشط
    public function getIsOnSaleAttribute()
    {
        return $this->current_price < $this->base_price;
    }

    public function getNameAttribute($value)
    {
        if (request()->is('admin*')) {
            return $value;
        }
        return __($value);
    }

    public function getDescriptionAttribute($value)
    {
        if (request()->is('admin*')) {
            return $value;
        }
        return __($value);
    }

    // تسجيل مجموعة الوسائط (الصور)
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('product_images')
             ->useFallbackUrl('/images/placeholder.jpg')
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
}
