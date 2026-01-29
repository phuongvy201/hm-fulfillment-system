<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class UserDesignPrice extends Model
{
    protected $fillable = [
        'user_id',
        'first_side_price_vnd',
        'additional_side_price_vnd',
        'status',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'first_side_price_vnd' => 'decimal:2',
        'additional_side_price_vnd' => 'decimal:2',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
    ];

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get active prices.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get prices effective for a specific date.
     */
    public function scopeEffectiveFor($query, $date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();

        return $query->where(function ($q) use ($date) {
            $q->whereNull('valid_from')
                ->orWhere('valid_from', '<=', $date);
        })
            ->where(function ($q) use ($date) {
                $q->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', $date);
            });
    }

    /**
     * Get the current active design price for a user.
     */
    public static function getCurrentPriceForUser($userId, $date = null): ?self
    {
        return static::active()
            ->effectiveFor($date)
            ->where('user_id', $userId)
            ->orderBy('valid_from', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
