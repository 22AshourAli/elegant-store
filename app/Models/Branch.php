<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['name', 'address', 'phone', 'is_active'];

    /**
     * Get the users associated with this branch.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function productVariants()
    {
        return $this->belongsToMany(ProductVariant::class, 'branch_product_variant')
                    ->withPivot('stock')
                    ->withTimestamps();
    }

    public function getNameAttribute($value)
    {
        if (request()->is('admin*')) {
            return $value;
        }
        return __($value);
    }

    public function getAddressAttribute($value)
    {
        if (request()->is('admin*')) {
            return $value;
        }
        return __($value);
    }
}
