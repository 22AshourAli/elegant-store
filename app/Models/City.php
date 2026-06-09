<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['governorate_id', 'name', 'delivery_time', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
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
