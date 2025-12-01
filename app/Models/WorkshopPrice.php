<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopPrice extends Model
{
    protected $fillable = [
        'workshop_id',
        'product_id',
        'variant_id',
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
     * Get the workshop.
     */
    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
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
}
