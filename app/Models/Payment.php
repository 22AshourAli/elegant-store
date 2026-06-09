<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'gateway', 'transaction_id', 'gateway_reference',
        'status', 'amount', 'gateway_fee', 'net_amount',
        'settlement_status', 'settled_at', 'response',
    ];

    protected $casts = [
        'response' => 'array',
        'amount' => 'decimal:2',
        'gateway_fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'settled_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
