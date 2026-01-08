<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingTier extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'priority',
        'description',
        'status',
        'min_orders',
        'auto_assign',
        'reset_period',
    ];

    protected $casts = [
        'min_orders' => 'integer',
        'auto_assign' => 'boolean',
    ];

    /**
     * Get the tier prices for products.
     */
    public function productTierPrices(): HasMany
    {
        return $this->hasMany(ProductTierPrice::class);
    }

    /**
     * Get the user pricing tier assignments.
     */
    public function userPricingTiers(): HasMany
    {
        return $this->hasMany(UserPricingTier::class);
    }
}
