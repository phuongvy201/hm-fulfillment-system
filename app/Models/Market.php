<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Market extends Model
{
    protected $fillable = [
        'code',
        'name',
        'currency',
        'currency_symbol',
        'timezone',
        'status',
    ];

    /**
     * Get the workshops for the market.
     */
    public function workshops(): HasMany
    {
        return $this->hasMany(Workshop::class);
    }

    /**
     * Get the pricing rules for the market.
     */
    public function pricingRules(): HasMany
    {
        return $this->hasMany(PricingRule::class);
    }

    /**
     * Get the user custom prices for the market.
     */
    public function userCustomPrices(): HasMany
    {
        return $this->hasMany(UserCustomPrice::class);
    }
}
