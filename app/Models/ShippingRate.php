<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    protected $fillable = [
        'governorate_id', 'city_id', 'min_cart_amount', 'rate', 'is_active',
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
}
