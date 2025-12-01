<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPricingTier extends Model
{
    protected $fillable = [
        'user_id',
        'pricing_tier_id',
        'assigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the pricing tier.
     */
    public function pricingTier(): BelongsTo
    {
        return $this->belongsTo(PricingTier::class);
    }
}
