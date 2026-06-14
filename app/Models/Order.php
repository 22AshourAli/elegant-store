<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'order_type',
        'cashier_id',
        'status',
        'payment_method',
        'payment_status',
        'subtotal',
        'discount',
        'shipping_cost',
        'total',
        'shipping_address',
        'phone',
        'notes',
        'tracking_number',
        'delivered_at',
        'governorate_id',
        'city_id',
        'district_id',
        'shipping_provider_id',
        'courier_name',
        'tracking_url',
        'shipping_status',
        'building',
        'apartment',
        'street',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function returnRequests()
    {
        return $this->hasMany(ReturnRequest::class);
    }

    public function exchanges()
    {
        return $this->hasMany(Exchange::class);
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
