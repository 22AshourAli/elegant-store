<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

#[Fillable(['name', 'email', 'password', 'role', 'branch_id', 'phone', 'avatar', 'social_id', 'social_type'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function avatarUrl(): string
    {
        if (!empty($this->avatar)) {
            if (str_starts_with($this->avatar, 'http')) {
                return $this->avatar;
            }
            if (\Storage::disk('public')->exists($this->avatar)) {
                return \Storage::disk('public')->url($this->avatar);
            }
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function isManager()
    {
        return $this->role === 'manager';
    }

    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    public function wishlist()
    {
        return $this->belongsToMany(Product::class, 'wishlists')->withTimestamps();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function returnRequests()
    {
        return $this->hasMany(ReturnRequest::class);
    }

    public function exchanges()
    {
        return $this->hasMany(Exchange::class);
    }

    public function scopeOnline(Builder $query): void
    {
        $activeIds = DB::table('sessions')
            ->where('last_activity', '>=', now()->subMinutes(15)->timestamp)
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        $query->whereIn('id', $activeIds);
    }

    public function scopeOffline(Builder $query): void
    {
        $activeIds = DB::table('sessions')
            ->where('last_activity', '>=', now()->subMinutes(15)->timestamp)
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        $query->whereNotIn('id', $activeIds);
    }
}
