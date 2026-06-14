<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransferItem extends Model
{
    protected $fillable = [
        'stock_transfer_id', 'product_variant_id', 'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function stockTransfer()
    {
        return $this->belongsTo(StockTransfer::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
