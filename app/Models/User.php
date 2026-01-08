<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'team_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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

    /**
     * Get the role that owns the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the team that owns the user.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the custom prices for the user.
     */
    public function customPrices()
    {
        return $this->hasMany(UserCustomPrice::class);
    }

    /**
     * Get the pricing tier assigned to the user.
     */
    public function pricingTier()
    {
        return $this->hasOne(UserPricingTier::class);
    }

    /**
     * Get the wallet for the user.
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Get the credit for the user.
     */
    public function credit()
    {
        return $this->hasOne(Credit::class);
    }

    /**
     * Get the orders for the user.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the top-up requests for the user.
     */
    public function topUpRequests()
    {
        return $this->hasMany(TopUpRequest::class);
    }

    /**
     * Get the debt payment requests for the user.
     */
    public function debtPaymentRequests()
    {
        return $this->hasMany(DebtPaymentRequest::class);
    }

    /**
     * Get the wallet transactions for the user.
     */
    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole($roleSlug): bool
    {
        return $this->role && $this->role->slug === $roleSlug;
    }

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    /**
     * Check if user is admin (super-admin or it-admin).
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('super-admin') || $this->hasRole('it-admin');
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        if (!$this->role) {
            return false;
        }

        // Super-admin has all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->role->hasPermission($permissionSlug);
    }

    /**
     * Check if user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissionSlugs): bool
    {
        if (!$this->role) {
            return false;
        }

        // Super-admin has all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->role->permissions()->whereIn('slug', $permissionSlugs)->exists();
    }
}
