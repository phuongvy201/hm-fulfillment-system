<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantAttribute extends Model
{
    protected $fillable = [
        'variant_id',
        'attribute_name',
        'attribute_value',
    ];

    /**
     * Get the variant that owns the attribute.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
