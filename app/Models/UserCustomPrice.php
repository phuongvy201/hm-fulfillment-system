<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCustomPrice extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'variant_id',
        'market_id',
        'price',
        'currency',
        'status',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'price' => 'decimal:2',
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
}
