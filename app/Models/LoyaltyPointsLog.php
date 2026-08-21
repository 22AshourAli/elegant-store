<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyPointsLog extends Model
{
    protected $table = 'loyalty_points_log';

    protected $fillable = [
        'user_id', 'order_id', 'points', 'type',
        'description', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'meta' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
