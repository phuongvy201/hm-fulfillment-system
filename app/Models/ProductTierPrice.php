<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductTierPrice extends Model
{
    protected $fillable = [
        'product_id',
        'variant_id',
        'market_id',
        'pricing_tier_id',
        'base_price',
        'currency',
        'status',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
    ];

    /**
     * Get the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the variant.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Get the market.
     */
    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    /**
     * Get the pricing tier.
     */
    public function pricingTier(): BelongsTo
    {
        return $this->belongsTo(PricingTier::class);
    }
}
