<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamPrice extends Model
{
    protected $fillable = [
        'team_id',
        'product_id',
        'variant_id',
        'location_id',
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
     * Get the team.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
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
     * Get the location.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
