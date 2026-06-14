<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    protected $fillable = [
        'governorate_id', 'city_id', 'district_id', 'shipping_provider_id',
        'rate', 'min_cart_amount', 'estimated_days', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_cart_amount' => 'decimal:2',
            'rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function shippingProvider()
    {
        return $this->belongsTo(ShippingProvider::class, 'shipping_provider_id');
    }
}
