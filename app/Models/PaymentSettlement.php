<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSettlement extends Model
{
    protected $table = 'payment_settlements';

    protected $fillable = [
        'batch_id', 'gateway', 'gross_amount', 'total_fees',
        'net_amount', 'status', 'metadata', 'settled_at',
    ];

    protected function casts(): array
    {
        return [
            'gross_amount' => 'decimal:2',
            'total_fees' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'metadata' => 'array',
            'settled_at' => 'datetime',
        ];
    }
}
