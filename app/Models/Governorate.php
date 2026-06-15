<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Governorate extends Model
{
    protected $fillable = ['name', 'name_ar', 'is_active', 'base_shipping_cost'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'base_shipping_cost' => 'decimal:2',
        ];
    }

    public function cities()
    {
        return $this->hasMany(City::class);
    }

    public function shippingRates()
    {
        return $this->hasMany(ShippingRate::class);
    }
}
