<?php

namespace App\Models;

use App\Enums\OrderStatus;
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
        'governorate_id',
        'city_id',
        'address_street',
        'address_building',
        'address_floor',
        'address_apartment',
        'address_landmark',
        'address_type',
        'phone',
        'notes',
        'tracking_number',
        'courier_name',
        'tracking_url',
        'shipping_status',
        'delivered_at',
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

    public function isWithinReturnWindow(): bool
    {
        if ($this->status !== OrderStatus::Delivered->value) {
            return false;
        }

        if ($this->delivered_at === null) {
            return false;
        }

        return $this->delivered_at->diffInDays(now()) <= 3;
    }
}
