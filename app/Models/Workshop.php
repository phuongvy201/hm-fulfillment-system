<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workshop extends Model
{
    protected $fillable = [
        'market_id',
        'code',
        'name',
        'description',
        'product_types',
        'status',
    ];

    protected $casts = [
        'product_types' => 'array',
    ];

    /**
     * Get the market that owns the workshop.
     */
    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    /**
     * Get the SKUs for the workshop.
     */
    public function skus(): HasMany
    {
        return $this->hasMany(WorkshopSku::class);
    }

    /**
     * Get the prices for the workshop.
     */
    public function prices(): HasMany
    {
        return $this->hasMany(WorkshopPrice::class);
    }

    /**
     * Get the workshop product SKU codes for the workshop.
     */
    public function productSkuCodes(): HasMany
    {
        return $this->hasMany(WorkshopProductSkuCode::class);
    }

    /**
     * Get the products for the workshop.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
