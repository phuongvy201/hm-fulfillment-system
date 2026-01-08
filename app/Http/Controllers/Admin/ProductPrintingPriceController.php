<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductPrintingPrice;
use App\Models\Market;

class ProductPrintingPriceController extends Controller
{
    /**
     * Show the form for creating printing prices for a product.
     */
    public function bulkCreate(Product $product)
    {
        // Get all active markets
        $markets = Market::where('status', 'active')->get();

        // Get existing printing prices for this product
        $existingPrices = ProductPrintingPrice::where('product_id', $product->id)
            ->whereNull('variant_id')
            ->with('market')
            ->get()
            ->groupBy('market_id');

        return view('admin.products.variants.bulk-printing-prices', compact('product', 'markets', 'existingPrices'));
    }

    /**
     * Store printing prices for a product.
     */
    public function bulkStore(Request $request, Product $product)
    {
        // Log raw request data for debugging
        Log::info('[PRINTING PRICE] Raw request received', [
            'product_id' => $product->id,
            'all_request_data' => $request->all(),
            'pricing_mode' => $request->input('pricing_mode'),
        ]);

        // First validate basic fields
        $basicValidated = $request->validate([
            'clear_existing' => ['nullable', 'boolean'],
            'pricing_mode' => ['required', 'in:per_side,incremental'],
            'markets' => ['required', 'array'],
            'markets.*.market_id' => ['required', 'exists:markets,id'],
        ]);

        $pricingMode = $basicValidated['pricing_mode'];

        // Conditional validation based on pricing mode
        if ($pricingMode === 'incremental') {
            // For incremental mode, only validate additional_side_price
        $validated = $request->validate([
            'clear_existing' => ['nullable', 'boolean'],
            'pricing_mode' => ['required', 'in:per_side,incremental'],
            'markets' => ['required', 'array'],
            'markets.*.market_id' => ['required', 'exists:markets,id'],
            'markets.*.additional_side_price' => ['nullable', 'numeric', 'min:0'],
            ]);
        } else {
            // For per_side mode, validate prices array
            $validated = $request->validate([
                'clear_existing' => ['nullable', 'boolean'],
                'pricing_mode' => ['required', 'in:per_side,incremental'],
                'markets' => ['required', 'array'],
                'markets.*.market_id' => ['required', 'exists:markets,id'],
                'markets.*.prices' => ['required', 'array', 'min:1'],
                'markets.*.prices.*.sides' => ['required', 'integer', 'min:2', 'max:10'],
                'markets.*.prices.*.price' => ['required', 'numeric', 'min:0'],
            ]);
        }

        Log::info('[PRINTING PRICE] Validation passed', [
            'product_id' => $product->id,
            'pricing_mode' => $pricingMode,
            'validated_data' => $validated,
        ]);

        $saved = 0;
        $errors = [];
        $cleared = 0;

        Log::info('[PRINTING PRICE] Starting bulk store', [
            'product_id' => $product->id,
            'pricing_mode' => $pricingMode,
            'markets_count' => count($validated['markets']),
            'clear_existing' => !empty($validated['clear_existing']),
        ]);

        // Clear existing prices if requested (only product-level, not variant-level)
        if (!empty($validated['clear_existing'])) {
            foreach ($validated['markets'] as $marketData) {
                if (!empty($marketData['market_id'])) {
                    $deleted = ProductPrintingPrice::where('product_id', $product->id)
                        ->whereNull('variant_id')
                        ->where('market_id', $marketData['market_id'])
                        ->delete();
                    $cleared += $deleted;
                    
                    Log::info('[PRINTING PRICE] Cleared existing prices', [
                        'product_id' => $product->id,
                        'market_id' => $marketData['market_id'],
                        'deleted_count' => $deleted,
                    ]);
                }
            }
        }

        // Apply printing prices to product (variant_id = null)
        foreach ($validated['markets'] as $marketData) {
            try {
                $market = Market::find($marketData['market_id']);
                if (!$market) {
                    Log::warning('[PRINTING PRICE] Market not found', [
                        'market_id' => $marketData['market_id'] ?? null,
                    ]);
                    continue;
                }
                
                Log::info('[PRINTING PRICE] Processing market', [
                    'product_id' => $product->id,
                    'market_id' => $market->id,
                    'market_name' => $market->name,
                    'pricing_mode' => $pricingMode,
                ]);

                if ($pricingMode === 'incremental') {
                    // Incremental mode: chỉ lưu giá cho mặt 2-10 (mặt 1 đã bao gồm trong giá variant)
                    $additionalSidePrice = $marketData['additional_side_price'] ?? 0;

                    Log::info('[PRINTING PRICE] Incremental mode - checking additional_side_price', [
                        'product_id' => $product->id,
                        'market_id' => $market->id,
                        'additional_side_price' => $additionalSidePrice,
                        'market_data' => $marketData,
                    ]);

                    if ($additionalSidePrice > 0) {
                        Log::info('[PRINTING PRICE] Incremental mode - additional_side_price', [
                            'product_id' => $product->id,
                            'market_id' => $market->id,
                            'additional_side_price' => $additionalSidePrice,
                        ]);
                        
                        // Lưu giá cho mặt 1 = 0 (đã bao gồm trong giá variant)
                        $result1 = ProductPrintingPrice::updateOrCreate(
                            [
                                'product_id' => $product->id,
                                'variant_id' => null,
                                'market_id' => $market->id,
                                'sides' => 1,
                            ],
                            [
                                'price' => 0,
                                'currency' => $market->currency,
                                'status' => 'active',
                            ]
                        );
                        $saved++;
                        
                        Log::info('[PRINTING PRICE] Saved side 1', [
                            'product_id' => $product->id,
                            'market_id' => $market->id,
                            'sides' => 1,
                            'price' => 0,
                            'record_id' => $result1->id,
                            'wasRecentlyCreated' => $result1->wasRecentlyCreated,
                        ]);
                        
                        // Lưu giá cho mặt 2-10: giá tích lũy cho các mặt thêm
                        for ($sides = 2; $sides <= 10; $sides++) {
                            // Giá tích lũy: mặt 2 = +additionalSidePrice, mặt 3 = +2*additionalSidePrice, ...
                            $price = ($sides - 1) * $additionalSidePrice;
                            
                            $result = ProductPrintingPrice::updateOrCreate(
                                [
                                    'product_id' => $product->id,
                                    'variant_id' => null,
                                    'market_id' => $market->id,
                                    'sides' => $sides,
                                ],
                                [
                                    'price' => $price,
                                    'currency' => $market->currency,
                                    'status' => 'active',
                                ]
                            );
                            $saved++;
                            
                            Log::info('[PRINTING PRICE] Saved side', [
                                'product_id' => $product->id,
                                'market_id' => $market->id,
                                'sides' => $sides,
                                'price' => $price,
                                'record_id' => $result->id,
                                'wasRecentlyCreated' => $result->wasRecentlyCreated,
                            ]);
                        }
                    } else {
                        Log::warning('[PRINTING PRICE] Incremental mode - additional_side_price is 0 or empty', [
                            'product_id' => $product->id,
                            'market_id' => $market->id,
                            'additional_side_price' => $additionalSidePrice,
                            'market_data' => $marketData,
                        ]);
                        $errors[] = "Market {$market->name}: Giá mỗi mặt thêm phải lớn hơn 0.";
                    }
                } else {
                    Log::info('[PRINTING PRICE] Per-side mode', [
                        'product_id' => $product->id,
                        'market_id' => $market->id,
                        'prices_count' => !empty($marketData['prices']) ? count($marketData['prices']) : 0,
                        'market_data' => $marketData,
                    ]);
                    
                    // Per-side mode: specific price for each side count (chỉ từ mặt 2 trở đi)
                    // Lưu giá cho mặt 1 = 0 (đã bao gồm trong giá variant)
                    $result1 = ProductPrintingPrice::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'variant_id' => null,
                            'market_id' => $market->id,
                            'sides' => 1,
                        ],
                        [
                            'price' => 0,
                            'currency' => $market->currency,
                            'status' => 'active',
                        ]
                    );
                    $saved++;
                    
                    Log::info('[PRINTING PRICE] Saved side 1 (per-side mode)', [
                        'product_id' => $product->id,
                        'market_id' => $market->id,
                        'sides' => 1,
                        'price' => 0,
                        'record_id' => $result1->id,
                        'wasRecentlyCreated' => $result1->wasRecentlyCreated,
                    ]);
                    
                    // Lưu giá cho mặt 2-10
                    if (!empty($marketData['prices']) && is_array($marketData['prices'])) {
                        foreach ($marketData['prices'] as $priceData) {
                            if (isset($priceData['sides']) && $priceData['sides'] >= 2 && $priceData['sides'] <= 10) {
                                $price = $priceData['price'] ?? 0;
                                
                                $result = ProductPrintingPrice::updateOrCreate(
                                    [
                                        'product_id' => $product->id,
                                        'variant_id' => null,
                                        'market_id' => $market->id,
                                        'sides' => $priceData['sides'],
                                    ],
                                    [
                                        'price' => $price,
                                        'currency' => $market->currency,
                                        'status' => 'active',
                                    ]
                                );
                                $saved++;
                                
                                Log::info('[PRINTING PRICE] Saved side (per-side mode)', [
                                    'product_id' => $product->id,
                                    'market_id' => $market->id,
                                    'sides' => $priceData['sides'],
                                    'price' => $price,
                                    'record_id' => $result->id,
                                    'wasRecentlyCreated' => $result->wasRecentlyCreated,
                                ]);
                            } else {
                                Log::warning('[PRINTING PRICE] Invalid price data skipped', [
                                    'product_id' => $product->id,
                                    'market_id' => $market->id,
                                    'price_data' => $priceData,
                                ]);
                            }
                        }
                    } else {
                        Log::warning('[PRINTING PRICE] No prices array or empty', [
                            'product_id' => $product->id,
                            'market_id' => $market->id,
                            'prices' => $marketData['prices'] ?? null,
                            'market_data' => $marketData,
                        ]);
                        $errors[] = "Market {$market->name}: Chưa nhập giá cho các mặt thêm.";
                    }
                }
            } catch (\Exception $e) {
                $errorMsg = "Failed to save printing price for market {$market->name}: " . $e->getMessage();
                $errors[] = $errorMsg;
                
                Log::error('[PRINTING PRICE] Exception occurred', [
                    'product_id' => $product->id,
                    'market_id' => $market->id ?? null,
                    'market_name' => $market->name ?? null,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
        
        Log::info('[PRINTING PRICE] Bulk store completed', [
            'product_id' => $product->id,
            'saved_count' => $saved,
            'cleared_count' => $cleared,
            'errors_count' => count($errors),
            'errors' => $errors,
        ]);

        if ($saved > 0) {
            $message = "Đã lưu {$saved} giá in cho sản phẩm.";
            if ($cleared > 0) {
                $message .= " Đã xóa {$cleared} giá cũ.";
            }
            if (!empty($errors)) {
                $message .= " Có " . count($errors) . " cảnh báo: " . implode(' ', $errors);
            }
            return redirect()->route('admin.products.show', $product)
                ->with('success', $message);
        } else {
            $errorMessage = 'Không có giá in nào được lưu. ';
            if (!empty($errors)) {
                $errorMessage .= implode(' ', $errors);
            } else {
                $errorMessage .= 'Vui lòng kiểm tra lại dữ liệu nhập.';
            }
            Log::warning('[PRINTING PRICE] No prices saved', [
                'product_id' => $product->id,
                'errors' => $errors,
                'request_data' => $request->all(),
            ]);
            return back()->withErrors(['prices' => $errorMessage])->withInput();
        }
    }
}

