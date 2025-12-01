<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopSku extends Model
{
    protected $fillable = [
        'workshop_id',
        'variant_id',
        'sku',
        'status',
    ];

    /**
     * Get the workshop that owns the SKU.
     */
    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    /**
     * Get the variant that owns the SKU.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
