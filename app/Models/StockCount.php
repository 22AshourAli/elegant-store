<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockCount extends Model
{
    protected $fillable = [
        'branch_id',
        'created_by',
        'reference_number',
        'status',
        'notes',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

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
        return $this->hasMany(StockCountItem::class, 'stock_count_id');
    }

    public static function boot()
    {
        parent::boot();
        static::creating(function ($count) {
            if (!$count->reference_number) {
                $count->reference_number = 'SC-' . strtoupper(\Illuminate\Support\Str::random(8));
            }
        });
    }
}
