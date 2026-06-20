<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCart extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'items', 'coupon_code'];

    protected function casts(): array
    {
        return [
            'items' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function forUser(int $userId): ?self
    {
        return static::where('user_id', $userId)->first();
    }
}
