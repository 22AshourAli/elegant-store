<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['governorate_id', 'name', 'name_ar', 'delivery_time', 'base_shipping_cost', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'base_shipping_cost' => 'decimal:2',
        ];
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    public function shippingRates()
    {
        return $this->hasMany(ShippingRate::class);
    }
}
