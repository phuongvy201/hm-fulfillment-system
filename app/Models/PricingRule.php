<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingRule extends Model
{
    protected $fillable = [
        'market_id',
        'product_id',
        'rule_type',
        'condition_key',
        'condition_value',
        'operation',
        'amount',
        'currency',
        'priority',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the market that owns the rule.
     */
    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    /**
     * Get the product that owns the rule.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
