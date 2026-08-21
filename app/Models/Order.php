<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;

/**
 * Order model — represents a customer purchase.
 *
 * Supports both online orders (customer checkout) and offline orders (POS).
 * Contains structured address fields for Egyptian delivery (street, building, floor, apartment, landmark).
 * Tracks shipping status independently from order status for carrier integration.
 */
class Order extends Model
{
    /** @var list<string> Mass-assignable attributes */
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

    /** @var array<string, string> Field type casts for decimal/currency precision. */
    protected $casts = [
        'delivered_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /** The customer who placed this order. */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** The cashier who processed this order (POS offline orders only). */
    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    /** The branch this order was assigned to (fulfillment location). */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /** Line items in this order (product, quantity, price). */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /** The payment record linked to this order (one payment per order). */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /** Return/exchange requests submitted for this order. */
    public function returnRequests()
    {
        return $this->hasMany(ReturnRequest::class);
    }

    /** Exchange requests submitted for this order. */
    public function exchanges()
    {
        return $this->hasMany(Exchange::class);
    }

    /** The governorate (province) for delivery address. */
    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    /** The city within the governorate for delivery address. */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Check if this order is still within the 3-day return window.
     * Returns true only if delivered and delivered_at is within the last 3 days.
     */
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
