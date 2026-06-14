<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number', 'supplier_id', 'branch_id', 'created_by',
        'status', 'subtotal', 'discount', 'shipping_cost', 'total',
        'notes', 'ordered_at', 'expected_at', 'received_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total' => 'decimal:2',
            'ordered_at' => 'datetime',
            'expected_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function scopePending($q)
    {
        return $q->whereIn('status', ['pending', 'sent']);
    }
}
