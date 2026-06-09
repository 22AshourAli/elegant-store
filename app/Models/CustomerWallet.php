<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerWallet extends Model
{
    protected $fillable = ['user_id', 'balance', 'loyalty_points', 'lifetime_spent'];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'lifetime_spent' => 'decimal:2',
            'loyalty_points' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pointsLog()
    {
        return $this->hasMany(LoyaltyPointsLog::class, 'user_id', 'user_id');
    }
}
