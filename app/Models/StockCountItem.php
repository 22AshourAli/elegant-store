<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockCountItem extends Model
{
    protected $fillable = [
        'stock_count_id',
        'product_variant_id',
        'system_stock',
        'counted_stock',
        'difference',
        'notes',
    ];

    protected $casts = [
        'system_stock' => 'integer',
        'counted_stock' => 'integer',
        'difference' => 'integer',
    ];

    public function stockCount()
    {
        return $this->belongsTo(StockCount::class, 'stock_count_id');
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
