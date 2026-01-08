<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Workshop;
use App\Models\WorkshopPrice;
use App\Models\Market;

class WorkshopPriceController extends Controller
{
    /**
     * Show the form for bulk creating workshop prices for multiple variants.
     */
    public function bulkCreate(Product $product)
    {
        // Load product with workshop
        $product->load('workshop.market');
        
        if (!$product->workshop) {
            return redirect()->route('admin.products.show', $product)
                ->withErrors(['error' => 'Product does not belong to any workshop.']);
        }

        $workshop = $product->workshop;
        
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

        // Get market from workshop
        $market = $workshop->market ?? null;
        
        // Get currency from market
        $currency = $market->currency ?? 'USD';

        return view('admin.products.workshop-prices.bulk-create', compact('product', 'workshop', 'variants', 'attributesByGroup', 'market', 'currency'));
    }

    /**
     * Store workshop prices for multiple variants at once.
     */
    public function bulkStore(Request $request, Product $product)
    {
        Log::info('[WORKSHOP PRICE] Raw request received', [
            'product_id' => $product->id,
            'all_request_data' => $request->all(),
        ]);

        // Load product with workshop
        $product->load('workshop.market');
        
        if (!$product->workshop) {
            return back()->withErrors(['error' => 'Product does not belong to any workshop.'])->withInput();
        }

        $workshop = $product->workshop;
        $market = $workshop->market;
        
        if (!$market) {
            return back()->withErrors(['error' => 'Workshop does not have a market assigned.'])->withInput();
        }

        $validated = $request->validate([
            'selected_attributes' => ['nullable', 'array'],
            'matching_logic' => ['required', 'in:or,and'],
            'clear_existing' => ['nullable', 'boolean'],
            'prices' => ['required', 'array'],
            'prices.seller.base_price' => ['nullable', 'numeric', 'min:0'],
            'prices.tiktok.base_price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['nullable', 'date', 'after_or_equal:valid_from'],
        ]);

        Log::info('[WORKSHOP PRICE] Validation passed', [
            'product_id' => $product->id,
            'workshop_id' => $workshop->id,
            'validated_data' => $validated,
        ]);

        // Get all variants for this product
        $allVariants = $product->variants()->with('attributes')->get();
        
        Log::info('[WORKSHOP PRICE] All variants loaded', [
            'product_id' => $product->id,
            'total_variants' => $allVariants->count(),
        ]);
        
        // Filter variants based on selected attributes
        $selectedAttributes = $validated['selected_attributes'] ?? [];
        $matchingLogic = $validated['matching_logic'] ?? 'or';
        
        Log::info('[WORKSHOP PRICE] Filtering variants', [
            'product_id' => $product->id,
            'selected_attributes' => $selectedAttributes,
            'matching_logic' => $matchingLogic,
        ]);
        
        $matchingVariants = $allVariants->filter(function ($variant) use ($selectedAttributes, $matchingLogic) {
            if (empty($selectedAttributes)) {
                return false; // No attributes selected
            }
            
            // Filter out empty attribute groups
            $nonEmptyAttributes = array_filter($selectedAttributes, function($values) {
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

        Log::info('[WORKSHOP PRICE] Variants filtered', [
            'product_id' => $product->id,
            'matching_variants_count' => $matchingVariants->count(),
            'matching_variant_ids' => $matchingVariants->pluck('id')->toArray(),
        ]);

        if ($matchingVariants->isEmpty()) {
            Log::warning('[WORKSHOP PRICE] No matching variants', [
                'product_id' => $product->id,
                'selected_attributes' => $selectedAttributes,
            ]);
            return back()->withErrors(['selected_attributes' => 'Không có variants nào khớp với các attributes đã chọn.'])->withInput();
        }

        $saved = 0;
        $errors = [];
        $cleared = 0;

        // Clear existing prices if requested
        if (!empty($validated['clear_existing'])) {
            foreach ($matchingVariants as $variant) {
                foreach (['seller', 'tiktok'] as $shippingType) {
                    if (!empty($validated['prices'][$shippingType]['base_price'])) {
                        $query = WorkshopPrice::where('workshop_id', $workshop->id)
                            ->where('product_id', $product->id)
                            ->where('variant_id', $variant->id)
                            ->where('shipping_type', $shippingType);
                        $deleted = $query->delete();
                        $cleared += $deleted;
                    }
                }
            }
        }

        // Apply prices to all matching variants
        foreach ($matchingVariants as $variant) {
            foreach (['seller', 'tiktok'] as $shippingType) {
                try {
                    if (empty($validated['prices'][$shippingType]['base_price']) || $validated['prices'][$shippingType]['base_price'] <= 0) {
                        continue;
                    }

                    WorkshopPrice::updateOrCreate(
                        [
                            'workshop_id' => $workshop->id,
                            'product_id' => $product->id,
                            'variant_id' => $variant->id,
                            'shipping_type' => $shippingType,
                        ],
                        [
                            'base_price' => $validated['prices'][$shippingType]['base_price'],
                            'currency' => $market->currency,
                            'status' => $validated['status'] ?? 'active',
                            'valid_from' => !empty($validated['valid_from']) ? $validated['valid_from'] : null,
                            'valid_to' => !empty($validated['valid_to']) ? $validated['valid_to'] : null,
                        ]
                    );
                    $saved++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to save {$shippingType} price for variant {$variant->display_name}: " . $e->getMessage();
                    Log::error('[WORKSHOP PRICE] Exception occurred', [
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'workshop_id' => $workshop->id,
                        'shipping_type' => $shippingType,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $message = "Đã lưu giá workshop cho {$saved} variant(s).";
        if ($cleared > 0) {
            $message .= " Đã xóa {$cleared} giá cũ.";
        }
        if (!empty($errors)) {
            $message .= " " . count($errors) . " lỗi xảy ra.";
        }

        Log::info('[WORKSHOP PRICE] Bulk store completed', [
            'product_id' => $product->id,
            'workshop_id' => $workshop->id,
            'saved' => $saved,
            'cleared' => $cleared,
            'errors_count' => count($errors),
        ]);

        return redirect()->route('admin.products.show', $product)
            ->with('success', $message);
    }

    /**
     * Show the form for creating workshop price for a single variant.
     */
    public function create(Product $product, ProductVariant $variant)
    {
        $variant->load('attributes');
        
        // Load product with workshop
        $product->load('workshop.market');
        
        if (!$product->workshop) {
            return redirect()->route('admin.products.show', $product)
                ->withErrors(['error' => 'Product does not belong to any workshop.']);
        }

        $workshop = $product->workshop;
        $market = $workshop->market;
        
        if (!$market) {
            return redirect()->route('admin.products.show', $product)
                ->withErrors(['error' => 'Workshop does not have a market assigned.']);
        }

        // Get existing prices grouped by shipping_type
        $existingPricesCollection = WorkshopPrice::where('workshop_id', $workshop->id)
            ->where('product_id', $product->id)
            ->where('variant_id', $variant->id)
            ->get();
        
        $existingPrices = [
            'seller' => $existingPricesCollection->where('shipping_type', 'seller')->first(),
            'tiktok' => $existingPricesCollection->where('shipping_type', 'tiktok')->first(),
        ];

        return view('admin.products.workshop-prices.create', compact('product', 'variant', 'workshop', 'market', 'existingPrices'));
    }

    /**
     * Store workshop price for a single variant.
     */
    public function store(Request $request, Product $product, ProductVariant $variant)
    {
        // Load product with workshop
        $product->load('workshop.market');
        
        if (!$product->workshop) {
            return back()->withErrors(['error' => 'Product does not belong to any workshop.'])->withInput();
        }

        $workshop = $product->workshop;
        $market = $workshop->market;
        
        if (!$market) {
            return back()->withErrors(['error' => 'Workshop does not have a market assigned.'])->withInput();
        }

        $validated = $request->validate([
            'prices' => ['required', 'array'],
            'prices.seller.base_price' => ['nullable', 'numeric', 'min:0'],
            'prices.tiktok.base_price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['nullable', 'date', 'after_or_equal:valid_from'],
        ]);

        $saved = 0;
        $errors = [];

        try {
            foreach (['seller', 'tiktok'] as $shippingType) {
                if (empty($validated['prices'][$shippingType]['base_price']) || $validated['prices'][$shippingType]['base_price'] <= 0) {
                    // Delete if exists and no price provided
                    WorkshopPrice::where('workshop_id', $workshop->id)
                        ->where('product_id', $product->id)
                        ->where('variant_id', $variant->id)
                        ->where('shipping_type', $shippingType)
                        ->delete();
                    continue;
                }

                WorkshopPrice::updateOrCreate(
                    [
                        'workshop_id' => $workshop->id,
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'shipping_type' => $shippingType,
                    ],
                    [
                        'base_price' => $validated['prices'][$shippingType]['base_price'],
                        'currency' => $market->currency,
                        'status' => $validated['status'] ?? 'active',
                        'valid_from' => !empty($validated['valid_from']) ? $validated['valid_from'] : null,
                        'valid_to' => !empty($validated['valid_to']) ? $validated['valid_to'] : null,
                    ]
                );
                $saved++;
            }

            if ($saved > 0) {
                return redirect()->route('admin.products.show', $product)
                    ->with('success', "Đã lưu {$saved} giá workshop cho variant {$variant->display_name}.");
            } else {
                return redirect()->route('admin.products.show', $product)
                    ->with('info', "Đã xóa giá workshop cho variant {$variant->display_name}.");
            }
        } catch (\Exception $e) {
            Log::error('[WORKSHOP PRICE] Store failed', [
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'workshop_id' => $workshop->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to save workshop price: ' . $e->getMessage()])->withInput();
        }
    }
}
