<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingProvider extends Model
{
    protected $fillable = [
        'name', 'type', 'phone', 'contact_person', 'notes', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function shippingRates()
    {
        return $this->hasMany(ShippingRate::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
