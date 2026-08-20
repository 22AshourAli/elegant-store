<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryCountItem extends Model
{
    protected $fillable = [
        'inventory_count_id',
        'product_variant_id',
        'system_stock',
        'counted_stock',
        'difference',
        'notes',
    ];

    public function inventoryCount()
    {
        return $this->belongsTo(InventoryCount::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
