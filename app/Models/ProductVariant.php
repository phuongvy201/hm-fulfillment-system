<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'sku',
        'status',
    ];

    /**
     * Get the product that owns the variant.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the workshop SKUs for the variant.
     */
    public function workshopSkus(): HasMany
    {
        return $this->hasMany(WorkshopSku::class, 'variant_id');
    }

    /**
     * Get the user custom prices for the variant.
     */
    public function userCustomPrices(): HasMany
    {
        return $this->hasMany(UserCustomPrice::class, 'variant_id');
    }

    /**
     * Get the team prices for the variant.
     */
    public function teamPrices(): HasMany
    {
        return $this->hasMany(TeamPrice::class, 'variant_id');
    }

    /**
     * Get the printing prices for the variant.
     */
    public function printingPrices(): HasMany
    {
        return $this->hasMany(ProductPrintingPrice::class, 'variant_id');
    }

    /**
     * Get the tier prices for the variant.
     */
    public function tierPrices(): HasMany
    {
        return $this->hasMany(ProductTierPrice::class, 'variant_id');
    }

    /**
     * Get the attributes for the variant.
     */
    public function variantAttributes(): HasMany
    {
        return $this->hasMany(VariantAttribute::class, 'variant_id');
    }

    /**
     * Alias for variantAttributes() for backward compatibility
     */
    public function attributes(): HasMany
    {
        return $this->variantAttributes();
    }

    /**
     * Get a specific variant attribute value by name
     */
    public function getVariantAttributeValue(string $attributeName): ?string
    {
        $attribute = $this->variantAttributes()->where('attribute_name', $attributeName)->first();
        return $attribute ? $attribute->attribute_value : null;
    }

    /**
     * Get all variant attributes as key-value array
     */
    public function getAttributesArray(): array
    {
        // Ensure attributes are loaded
        if (!$this->relationLoaded('variantAttributes')) {
            $this->load('variantAttributes');
        }
        return $this->variantAttributes->pluck('attribute_value', 'attribute_name')->toArray();
    }

    /**
     * Get variant display name from attributes (e.g., "Red - XL")
     */
    public function getDisplayNameAttribute(): string
    {
        $attrs = $this->getAttributesArray();
        if (empty($attrs)) {
            return 'Variant #' . $this->id;
        }
        return implode(' - ', array_values($attrs));
    }
}
