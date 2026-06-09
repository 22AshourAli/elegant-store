<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbandonedCart extends Model
{
    protected $fillable = [
        'user_id', 'session_id', 'items', 'total', 'coupon_code',
        'status', 'first_abandoned_at', 'last_reminder_sent_at',
        'recovery_token', 'reminder_count',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'total' => 'decimal:2',
            'first_abandoned_at' => 'datetime',
            'last_reminder_sent_at' => 'datetime',
            'reminder_count' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }

    public function scopeAbandoned($q)
    {
        return $q->where('status', 'abandoned');
    }

    public function scopeRecoverable($q)
    {
        return $q->whereIn('status', ['active', 'abandoned']);
    }
}
