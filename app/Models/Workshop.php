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
        'api_type',
        'api_endpoint',
        'api_key',
        'api_secret',
        'api_settings',
        'api_enabled',
        'api_notes',
    ];

    protected $casts = [
        'product_types' => 'array',
        'api_settings' => 'array',
        'api_enabled' => 'boolean',
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

    /**
     * Get the orders for the workshop.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
