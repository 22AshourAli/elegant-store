<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name', 'phone', 'email', 'address',
        'contact_person', 'contact_phone', 'notes', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
