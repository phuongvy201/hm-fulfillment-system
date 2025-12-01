<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopProductSkuCode extends Model
{
    protected $fillable = [
        'workshop_id',
        'product_id',
        'sku_code',
        'description',
        'status',
    ];

    /**
     * Get the workshop that owns the SKU code.
     */
    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    /**
     * Get the product that owns the SKU code.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
