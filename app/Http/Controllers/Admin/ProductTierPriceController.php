<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductTierPrice;
use App\Models\Market;
use App\Models\PricingTier;

class ProductTierPriceController extends Controller
{
    /**
     * Show the form for creating prices for a variant.
     */
    public function create(Product $product, ProductVariant $variant)
    {
        $variant->load('attributes');

        // Get market from product's workshop
        $product->load('workshop.market');
        $market = $product->workshop->market ?? null;

        // Chỉ lấy market của workshop (nếu có), nếu không có workshop thì lấy tất cả
        if ($market) {
            $markets = collect([$market]);
        } else {
            // Fallback: nếu không có workshop, lấy tất cả markets
            $markets = Market::where('status', 'active')->get();
        }

        // Get default tier (first active tier or create a default one)
        $defaultTier = PricingTier::where('status', 'active')->orderBy('priority')->first();
        if (!$defaultTier) {
            // Create a default tier if none exists
            $defaultTier = PricingTier::firstOrCreate(
                ['slug' => 'default'],
                [
                    'name' => 'Default',
                    'priority' => 0,
                    'status' => 'active'
                ]
            );
        }

        // Get existing prices for this variant (only for default tier)
        // Group by market_id and shipping_type
        $existingPrices = ProductTierPrice::where('variant_id', $variant->id)
            ->where('pricing_tier_id', $defaultTier->id)
            ->with(['market'])
            ->get()
            ->groupBy(function ($price) {
                return $price->market_id . '_' . ($price->shipping_type ?? 'standard');
            })
            ->map(function ($prices) {
                return $prices->first();
            });

        return view('admin.products.variants.prices.create', compact('product', 'variant', 'markets', 'market', 'existingPrices', 'defaultTier'));
    }

    /**
     * Store prices for a variant.
     */
    public function store(Request $request, Product $product, ProductVariant $variant)
    {
        // Get default tier
        $defaultTier = PricingTier::where('status', 'active')->orderBy('priority')->first();
        if (!$defaultTier) {
            $defaultTier = PricingTier::firstOrCreate(
                ['slug' => 'default'],
                [
                    'name' => 'Default',
                    'priority' => 0,
                    'status' => 'active'
                ]
            );
        }

        $validated = $request->validate([
            'prices' => ['required', 'array'],
        ]);

        // Validate each price entry
        foreach ($validated['prices'] as $key => $priceData) {
            $request->validate([
                "prices.{$key}.market_id" => ['required', 'exists:markets,id'],
                "prices.{$key}.base_price" => ['nullable', 'numeric', 'min:0'],
                "prices.{$key}.additional_item_price" => ['nullable', 'numeric', 'min:0'],
                "prices.{$key}.shipping_type" => ['nullable', 'in:seller,tiktok'],
                "prices.{$key}.status" => ['required', 'in:active,inactive'],
            ]);
        }

        $saved = 0;
        $errors = [];

        foreach ($validated['prices'] as $key => $priceData) {
            try {
                // Check if price exists (with shipping_type)
                $shippingType = !empty($priceData['shipping_type']) ? $priceData['shipping_type'] : null;
                $existingPrice = ProductTierPrice::where('product_id', $product->id)
                    ->where('variant_id', $variant->id)
                    ->where('market_id', $priceData['market_id'])
                    ->where('pricing_tier_id', $defaultTier->id)
                    ->where('shipping_type', $shippingType)
                    ->first();

                // If no price provided and exists, delete it
                if ((empty($priceData['base_price']) || $priceData['base_price'] <= 0)) {
                    if ($existingPrice) {
                        $existingPrice->delete();
                    }
                    continue;
                }

                // Get market to get currency
                $market = Market::find($priceData['market_id']);
                if (!$market) {
                    continue;
                }

                ProductTierPrice::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'market_id' => $priceData['market_id'],
                        'pricing_tier_id' => $defaultTier->id,
                        'shipping_type' => $priceData['shipping_type'] ?? null,
                    ],
                    [
                        'base_price' => $priceData['base_price'],
                        'additional_item_price' => $priceData['additional_item_price'] ?? null,
                        'currency' => $market->currency,
                        'status' => $priceData['status'] ?? 'active',
                    ]
                );
                $saved++;
            } catch (\Exception $e) {
                $errors[] = "Failed to save price: " . $e->getMessage();
            }
        }

        if ($saved > 0) {
            $message = "Successfully saved {$saved} price(s).";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " error(s) occurred.";
            }
            return redirect()->route('admin.products.show', $product)
                ->with('success', $message);
        } else {
            return back()->withErrors(['prices' => 'No prices were saved. Please check your input.'])->withInput();
        }
    }

    /**
     * Show the form for bulk creating prices for multiple variants.
     */
    public function bulkCreate(Product $product)
    {
        // Load all variants with attributes
        $variants = $product->variants()->with('attributes')->get();

        // Group attributes by attribute_name
        $attributesByGroup = [];
        foreach ($variants as $variant) {
            foreach ($variant->attributes as $attr) {
                $attrName = $attr->attribute_name;
                $attrValue = $attr->attribute_value;

                if (!isset($attributesByGroup[$attrName])) {
                    $attributesByGroup[$attrName] = [];
                }

                if (!in_array($attrValue, $attributesByGroup[$attrName])) {
                    $attributesByGroup[$attrName][] = $attrValue;
                }
            }
        }

        // Sort attribute values for each group
        foreach ($attributesByGroup as $key => $values) {
            sort($attributesByGroup[$key]);
        }

        // Get market from product's workshop
        $product->load('workshop.market');
        $market = $product->workshop->market ?? null;

        // Chỉ lấy market của workshop (nếu có), nếu không có workshop thì lấy tất cả
        if ($market) {
            $markets = collect([$market]);
        } else {
            // Fallback: nếu không có workshop, lấy tất cả markets
            $markets = Market::where('status', 'active')->get();
        }

        // Get all active tiers
        $tiers = PricingTier::where('status', 'active')->orderBy('priority')->get();

        return view('admin.products.variants.bulk-prices', compact('product', 'variants', 'markets', 'market', 'attributesByGroup', 'tiers'));
    }

    /**
     * Store prices for multiple variants at once.
     */
    public function bulkStore(Request $request, Product $product)
    {
        // Log raw request data for debugging
        Log::info('[TIER PRICE] Raw request received', [
            'product_id' => $product->id,
            'all_request_data' => $request->all(),
        ]);

        $validated = $request->validate([
            'pricing_tier_id' => ['required', 'exists:pricing_tiers,id'],
            'selected_attributes' => ['nullable', 'array'],
            'matching_logic' => ['required', 'in:or,and'],
            'clear_existing' => ['nullable', 'boolean'],
            'prices' => ['required', 'array'],
        ]);

        Log::info('[TIER PRICE] Validation passed', [
            'product_id' => $product->id,
            'pricing_tier_id' => $validated['pricing_tier_id'],
            'validated_data' => $validated,
        ]);

        // Get selected tier
        $selectedTier = PricingTier::findOrFail($validated['pricing_tier_id']);

        // Validate each price entry
        foreach ($validated['prices'] as $key => $priceData) {
            $request->validate([
                "prices.{$key}.market_id" => ['required', 'exists:markets,id'],
                "prices.{$key}.base_price" => ['nullable', 'numeric', 'min:0'],
                "prices.{$key}.additional_item_price" => ['nullable', 'numeric', 'min:0'],
                "prices.{$key}.status" => ['required', 'in:active,inactive'],
            ]);
        }

        // Get all variants for this product
        $allVariants = $product->variants()->with('attributes')->get();

        Log::info('[TIER PRICE] All variants loaded', [
            'product_id' => $product->id,
            'total_variants' => $allVariants->count(),
        ]);

        // Filter variants based on selected attributes
        $selectedAttributes = $validated['selected_attributes'] ?? [];
        $matchingLogic = $validated['matching_logic'] ?? 'or';

        Log::info('[TIER PRICE] Filtering variants', [
            'product_id' => $product->id,
            'selected_attributes' => $selectedAttributes,
            'matching_logic' => $matchingLogic,
        ]);

        $matchingVariants = $allVariants->filter(function ($variant) use ($selectedAttributes, $matchingLogic) {
            if (empty($selectedAttributes)) {
                return false; // No attributes selected
            }

            // Filter out empty attribute groups
            $nonEmptyAttributes = array_filter($selectedAttributes, function ($values) {
                return !empty($values) && is_array($values);
            });

            if (empty($nonEmptyAttributes)) {
                return false;
            }

            $variantAttributes = $variant->attributes->pluck('attribute_value', 'attribute_name')->toArray();

            if ($matchingLogic === 'and') {
                // AND: Variant must have at least one selected value from EACH attribute group
                foreach ($nonEmptyAttributes as $attrName => $attrValues) {
                    $variantValue = $variantAttributes[$attrName] ?? null;
                    if (!$variantValue || !in_array($variantValue, $attrValues)) {
                        return false; // This attribute group doesn't match
                    }
                }
                return true; // All attribute groups matched
            } else {
                // OR: Variant must have at least one selected value from ANY attribute group
                foreach ($nonEmptyAttributes as $attrName => $attrValues) {
                    $variantValue = $variantAttributes[$attrName] ?? null;
                    if ($variantValue && in_array($variantValue, $attrValues)) {
                        return true; // At least one match found
                    }
                }
                return false; // No matches found
            }
        });

        Log::info('[TIER PRICE] Variants filtered', [
            'product_id' => $product->id,
            'matching_variants_count' => $matchingVariants->count(),
            'matching_variant_ids' => $matchingVariants->pluck('id')->toArray(),
        ]);

        if ($matchingVariants->isEmpty()) {
            Log::warning('[TIER PRICE] No matching variants', [
                'product_id' => $product->id,
                'selected_attributes' => $selectedAttributes,
            ]);
            return back()->withErrors(['selected_attributes' => 'Không có variants nào khớp với các attributes đã chọn.'])->withInput();
        }

        $saved = 0;
        $errors = [];
        $cleared = 0;

        // Clear existing prices if requested (only for the markets being set)
        if (!empty($validated['clear_existing'])) {
            foreach ($matchingVariants as $variant) {
                foreach ($validated['prices'] as $key => $priceData) {
                    if (!empty($priceData['market_id'])) {
                        $query = ProductTierPrice::where('product_id', $product->id)
                            ->where('variant_id', $variant->id)
                            ->where('market_id', $priceData['market_id'])
                            ->where('pricing_tier_id', $selectedTier->id);

                        // Nếu có shipping_type, chỉ xóa loại đó, nếu không thì xóa tất cả
                        if (isset($priceData['shipping_type']) && $priceData['shipping_type'] !== '') {
                            $query->where('shipping_type', $priceData['shipping_type']);
                        }

                        $query->delete();
                        $cleared++;
                    }
                }
            }
        }

        // Apply prices to all matching variants
        foreach ($matchingVariants as $variant) {
            foreach ($validated['prices'] as $key => $priceData) {
                try {
                    Log::info('[TIER PRICE] Processing price for variant', [
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'variant_name' => $variant->display_name,
                        'price_key' => $key,
                        'price_data' => $priceData,
                    ]);

                    // Skip if no price provided
                    if (empty($priceData['base_price']) || $priceData['base_price'] <= 0) {
                        Log::warning('[TIER PRICE] Skipping empty price', [
                            'product_id' => $product->id,
                            'variant_id' => $variant->id,
                            'price_data' => $priceData,
                        ]);
                        continue;
                    }

                    // Get market to get currency
                    $market = Market::find($priceData['market_id']);
                    if (!$market) {
                        Log::warning('[TIER PRICE] Market not found', [
                            'product_id' => $product->id,
                            'variant_id' => $variant->id,
                            'market_id' => $priceData['market_id'] ?? null,
                        ]);
                        continue;
                    }

                    // Xử lý shipping_type: nếu là empty string thì set null
                    $shippingType = !empty($priceData['shipping_type']) && $priceData['shipping_type'] !== '' ? $priceData['shipping_type'] : null;

                    Log::info('[TIER PRICE] Saving price', [
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'market_id' => $market->id,
                        'pricing_tier_id' => $selectedTier->id,
                        'pricing_tier_name' => $selectedTier->name,
                        'shipping_type' => $shippingType,
                        'base_price' => $priceData['base_price'],
                        'currency' => $market->currency,
                    ]);

                    $result = ProductTierPrice::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'variant_id' => $variant->id,
                            'market_id' => $priceData['market_id'],
                            'pricing_tier_id' => $selectedTier->id,
                            'shipping_type' => $shippingType,
                        ],
                        [
                            'base_price' => $priceData['base_price'],
                            'additional_item_price' => $priceData['additional_item_price'] ?? null,
                            'currency' => $market->currency,
                            'status' => $priceData['status'] ?? 'active',
                        ]
                    );
                    $saved++;

                    Log::info('[TIER PRICE] Price saved successfully', [
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'record_id' => $result->id,
                        'wasRecentlyCreated' => $result->wasRecentlyCreated,
                    ]);
                } catch (\Exception $e) {
                    $errorMsg = "Failed to save price for variant {$variant->display_name}: " . $e->getMessage();
                    $errors[] = $errorMsg;

                    Log::error('[TIER PRICE] Exception occurred', [
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'variant_name' => $variant->display_name,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
        }

        Log::info('[TIER PRICE] Bulk store completed', [
            'product_id' => $product->id,
            'saved_count' => $saved,
            'cleared_count' => $cleared,
            'errors_count' => count($errors),
            'errors' => $errors,
            'matching_variants_count' => $matchingVariants->count(),
        ]);

        if ($saved > 0) {
            $message = "Đã lưu {$saved} giá cho tier '{$selectedTier->name}' cho " . $matchingVariants->count() . " variant(s).";
            if ($cleared > 0) {
                $message .= " Đã xóa {$cleared} giá cũ.";
            }
            if (!empty($errors)) {
                $message .= " Có " . count($errors) . " lỗi xảy ra.";
            }
            return redirect()->route('admin.products.show', $product)
                ->with('success', $message);
        } else {
            $errorMessage = 'Không có giá nào được lưu. ';
            if (!empty($errors)) {
                $errorMessage .= implode(' ', $errors);
            } else {
                $errorMessage .= 'Vui lòng kiểm tra lại dữ liệu nhập.';
            }
            Log::warning('[TIER PRICE] No prices saved', [
                'product_id' => $product->id,
                'errors' => $errors,
                'request_data' => $request->all(),
            ]);
            return back()->withErrors(['prices' => $errorMessage])->withInput();
        }
    }
}
