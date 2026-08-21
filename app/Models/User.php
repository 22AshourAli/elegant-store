<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

/**
 * User model — represents any application user.
 *
 * Roles: customer, manager (branch-level admin), super_admin (full access).
 * Uses PHP 8 attributes for fillable/hidden fields.
 * Supports social login (Google, GitHub, Microsoft) via nullable social_id/social_type.
 */
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
    /** @var array<string, string> Attribute cast definitions. */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** The branch this user belongs to (manager role only). */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user's avatar URL.
     * Falls back to ui-avatars.com if no avatar is set.
     */
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

    /** Check if user has super_admin role (full system access). */
    public function isSuperAdmin()
    {
        return $this->role === UserRole::SuperAdmin->value;
    }

    /** Check if user has manager role (branch-level admin access). */
    public function isManager()
    {
        return $this->role === UserRole::Manager->value;
    }

    /** Check if user has customer role (default, shop access). */
    public function isCustomer()
    {
        return $this->role === UserRole::Customer->value;
    }

    /** Products this user has added to their wishlist. */
    public function wishlist()
    {
        return $this->belongsToMany(Product::class, 'wishlists')->withTimestamps();
    }

    /** All orders placed by this user (online + POS). */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /** Return requests submitted by this user. */
    public function returnRequests()
    {
        return $this->hasMany(ReturnRequest::class);
    }

    /** Exchange requests submitted by this user. */
    public function exchanges()
    {
        return $this->hasMany(Exchange::class);
    }

    /**
     * Scope: filter to online users (active session in last 15 minutes).
     * Queries the sessions table to determine activity status.
     */
    public function scopeOnline(Builder $query): void
    {
        $activeIds = DB::table('sessions')
            ->where('last_activity', '>=', now()->subMinutes(15)->timestamp)
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        $query->whereIn('id', $activeIds);
    }

    /** Scope: filter to offline users (no session in last 15 minutes). */
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
