<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'description',
        'status',
        'workshop_id',
    ];

    /**
     * Get the workshop that owns the product.
     */
    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    /**
     * Get the variants for the product.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Get the user custom prices for the product.
     */
    public function userCustomPrices(): HasMany
    {
        return $this->hasMany(UserCustomPrice::class);
    }

    /**
     * Get the team prices for the product.
     */
    public function teamPrices(): HasMany
    {
        return $this->hasMany(TeamPrice::class);
    }

    /**
     * Get the printing prices for the product.
     */
    public function printingPrices(): HasMany
    {
        return $this->hasMany(ProductPrintingPrice::class);
    }

    /**
     * Get the tier prices for the product.
     */
    public function tierPrices(): HasMany
    {
        return $this->hasMany(ProductTierPrice::class);
    }

    /**
     * Get the pricing rules for the product.
     */
    public function pricingRules(): HasMany
    {
        return $this->hasMany(PricingRule::class);
    }

    /**
     * Get the workshop product SKU codes for the product.
     */
    public function workshopProductSkuCodes(): HasMany
    {
        return $this->hasMany(WorkshopProductSkuCode::class);
    }
}
