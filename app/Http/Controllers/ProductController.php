<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Market;
use App\Models\Workshop;
use App\Models\ProductTierPrice;
use App\Models\PricingTier;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of all products.
     */
    public function index(Request $request)
    {
        $query = Product::where('status', 'active')
            ->with([
                'images' => function ($query) {
                    $query->orderBy('is_primary', 'desc')->orderBy('sort_order')->limit(1);
                },
                'variants' => function ($query) {
                    $query->where('status', 'active')->limit(1);
                },
                'variants.tierPrices' => function ($query) {
                    $query->where('status', 'active')
                        ->orderBy('base_price')
                        ->limit(1);
                },
                'variants.tierPrices.market',
                'workshop.market'
            ])
            ->whereHas('images');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by workshop
        if ($request->has('workshop') && $request->workshop) {
            $query->where('workshop_id', $request->workshop);
        }

        // Filter by price range
        if ($request->has('min_price') && $request->min_price) {
            $query->whereHas('variants.tierPrices', function ($q) use ($request) {
                $q->where('status', 'active')
                    ->where('base_price', '>=', $request->min_price);
            });
        }

        if ($request->has('max_price') && $request->max_price) {
            $query->whereHas('variants.tierPrices', function ($q) use ($request) {
                $q->where('status', 'active')
                    ->where('base_price', '<=', $request->max_price);
            });
        }

        // Filter by market
        if ($request->has('market') && $request->market) {
            $query->whereHas('variants.tierPrices', function ($q) use ($request) {
                $q->where('status', 'active')
                    ->where('market_id', $request->market);
            });
        }

        // Get filter data for view
        $workshops = Workshop::where('status', 'active')
            ->whereHas('products', function ($q) {
                $q->where('status', 'active')->whereHas('images');
            })
            ->orderBy('name')
            ->get();

        // Get markets that have active tier prices
        $marketIds = ProductTierPrice::where('status', 'active')
            ->whereHas('product', function ($q) {
                $q->where('status', 'active')->whereHas('images');
            })
            ->distinct()
            ->pluck('market_id');

        $markets = Market::where('status', 'active')
            ->whereIn('id', $marketIds)
            ->orderBy('name')
            ->get();

        $products = $query->latest()->paginate(12)->appends($request->query());

        return view('products.index', compact('products', 'workshops', 'markets'));
    }

    /**
     * Display the specified product.
     */
    public function show($slug)
    {
        $product = Product::where('slug', $slug)
            ->where('status', 'active')
            ->with([
                'images' => function ($query) {
                    $query->orderBy('is_primary', 'desc')->orderBy('sort_order');
                },
                'variants' => function ($query) {
                    $query->where('status', 'active');
                },
                'variants.variantAttributes',
                'variants.workshopSkus',
                'variants.tierPrices' => function ($query) {
                    $query->where('status', 'active')
                        ->with('market', 'pricingTier')
                        ->orderBy('base_price');
                },
                'tierPrices' => function ($query) {
                    $query->where('status', 'active')
                        ->whereNull('variant_id')
                        ->with('market', 'pricingTier')
                        ->orderBy('base_price');
                },
                'workshop.market'
            ])
            ->firstOrFail();

        // Get product market to determine which currencies to convert to
        $productMarket = $product->workshop->market ?? null;
        $marketCode = $productMarket ? strtoupper($productMarket->code) : null;

        // Determine currencies to convert to based on market
        $currenciesToConvert = [];
        if ($marketCode === 'US' || $marketCode === 'USA') {
            // US market: convert to GBP and VND (already in USD)
            $currenciesToConvert = ['GBP', 'VND'];
        } elseif ($marketCode === 'UK' || $marketCode === 'GB') {
            // UK market: convert to USD and VND (already in GBP)
            $currenciesToConvert = ['USD', 'VND'];
        } else {
            // Default: convert to all three
            $currenciesToConvert = ['GBP', 'USD', 'VND'];
        }

        // Get default tier (wood tier - tier có priority thấp nhất hoặc không có min_orders)
        $defaultTier = PricingTier::where('status', 'active')
            ->whereNull('min_orders')
            ->orderBy('priority', 'asc')
            ->first();

        // Fallback: nếu không có tier wood, lấy tier có priority thấp nhất
        if (!$defaultTier) {
            $defaultTier = PricingTier::where('status', 'active')
                ->orderBy('priority', 'asc')
                ->first();
        }

        // Get wood tier - try multiple ways to find it
        $woodTier = PricingTier::where('status', 'active')
            ->where(function ($q) {
                $q->where('name', 'like', '%wood%')
                    ->orWhere('slug', 'wood')
                    ->orWhere('slug', 'like', '%wood%');
            })
            ->first();

        // Fallback: if no wood tier found by name/slug, get tier with lowest priority (usually wood tier)
        if (!$woodTier) {
            $woodTier = PricingTier::where('status', 'active')
                ->whereNull('min_orders')
                ->orderBy('priority', 'asc')
                ->first();
        }

        // Final fallback: get any tier with lowest priority
        if (!$woodTier) {
            $woodTier = PricingTier::where('status', 'active')
                ->orderBy('priority', 'asc')
                ->first();
        }

        // Get PricingService for currency conversion
        // This service uses ExchangeRate model to get current exchange rates from database
        $pricingService = app(\App\Services\PricingService::class);

        // Get product-level wood tier prices as fallback
        $productLevelSellerPrice = null;
        $productLevelTiktokPrice = null;
        if ($woodTier) {
            $productLevelSellerPrice = $product->tierPrices
                ->where('pricing_tier_id', $woodTier->id)
                ->where('status', 'active')
                ->where('shipping_type', 'seller')
                ->first();

            $productLevelTiktokPrice = $product->tierPrices
                ->where('pricing_tier_id', $woodTier->id)
                ->where('status', 'active')
                ->where('shipping_type', 'tiktok')
                ->first();
        }

        // Prepare variants data for JavaScript - get TikTok and Seller prices
        $variantsData = $product->variants->map(function ($variant) use ($woodTier, $pricingService, $productLevelSellerPrice, $productLevelTiktokPrice, $currenciesToConvert) {
            $attributes = [];
            foreach ($variant->variantAttributes as $attr) {
                $attributes[$attr->attribute_name] = $attr->attribute_value;
            }

            // Get prices for TikTok and Seller shipping types
            $prices = [
                'seller' => null,
                'seller_additional' => null,
                'tiktok' => null,
                'tiktok_additional' => null,
            ];
            $currency = 'USD';
            $market = null;

            if ($woodTier) {
                // Get seller price from variant first
                $sellerPrice = $variant->tierPrices
                    ->where('pricing_tier_id', $woodTier->id)
                    ->where('status', 'active')
                    ->where('shipping_type', 'seller')
                    ->first();

                // Fallback to product-level price if variant doesn't have price
                if (!$sellerPrice && $productLevelSellerPrice) {
                    $sellerPrice = $productLevelSellerPrice;
                }

                if ($sellerPrice) {
                    $prices['seller'] = $sellerPrice->base_price;
                    $prices['seller_additional'] = $sellerPrice->additional_item_price;
                    $currency = $sellerPrice->currency;
                    $market = $sellerPrice->market;
                }

                // Get tiktok price from variant first
                $tiktokPrice = $variant->tierPrices
                    ->where('pricing_tier_id', $woodTier->id)
                    ->where('status', 'active')
                    ->where('shipping_type', 'tiktok')
                    ->first();

                // Fallback to product-level price if variant doesn't have price
                if (!$tiktokPrice && $productLevelTiktokPrice) {
                    $tiktokPrice = $productLevelTiktokPrice;
                }

                if ($tiktokPrice) {
                    $prices['tiktok'] = $tiktokPrice->base_price;
                    $prices['tiktok_additional'] = $tiktokPrice->additional_item_price;
                    if (!$currency) $currency = $tiktokPrice->currency;
                    if (!$market) $market = $tiktokPrice->market;
                }
            }

            // Convert prices based on market (both base and additional)
            // Exchange rates are retrieved from database via PricingService::convertCurrency()
            // Falls back to hardcoded rates if no database rate is found
            $convertedPrices = [
                'seller' => [
                    'base' => [],
                    'additional' => [],
                ],
                'tiktok' => [
                    'base' => [],
                    'additional' => [],
                ],
            ];

            if ($prices['seller']) {
                foreach ($currenciesToConvert as $targetCurrency) {
                    // Uses ExchangeRate::getCurrentRate() from database
                    $convertedPrices['seller']['base'][$targetCurrency] = $pricingService->convertCurrency((float)$prices['seller'], $currency, $targetCurrency);
                }
                if ($prices['seller_additional']) {
                    foreach ($currenciesToConvert as $targetCurrency) {
                        $convertedPrices['seller']['additional'][$targetCurrency] = $pricingService->convertCurrency((float)$prices['seller_additional'], $currency, $targetCurrency);
                    }
                }
            }

            if ($prices['tiktok']) {
                foreach ($currenciesToConvert as $targetCurrency) {
                    $convertedPrices['tiktok']['base'][$targetCurrency] = $pricingService->convertCurrency((float)$prices['tiktok'], $currency, $targetCurrency);
                }
                if ($prices['tiktok_additional']) {
                    foreach ($currenciesToConvert as $targetCurrency) {
                        $convertedPrices['tiktok']['additional'][$targetCurrency] = $pricingService->convertCurrency((float)$prices['tiktok_additional'], $currency, $targetCurrency);
                    }
                }
            }

            return [
                'id' => $variant->id,
                'sku' => $variant->sku ?? 'N/A',
                'attributes' => $attributes,
                'prices' => $prices,
                'convertedPrices' => $convertedPrices,
                'currency' => $currency,
                'market' => $market ? $market->name : null,
            ];
        });

        // Calculate minimum price from all variants for "From" display
        // Use the lowest price from seller or tiktok
        $minWoodPrice = null;
        $minWoodPriceCurrency = 'USD';
        $minWoodPriceMarket = null;

        foreach ($variantsData as $variant) {
            // Check seller price
            if (!is_null($variant['prices']['seller'])) {
                if (is_null($minWoodPrice) || $variant['prices']['seller'] < $minWoodPrice) {
                    $minWoodPrice = $variant['prices']['seller'];
                    $minWoodPriceCurrency = $variant['currency'] ?? 'USD';
                    $minWoodPriceMarket = $variant['market'];
                }
            }

            // Check tiktok price
            if (!is_null($variant['prices']['tiktok'])) {
                if (is_null($minWoodPrice) || $variant['prices']['tiktok'] < $minWoodPrice) {
                    $minWoodPrice = $variant['prices']['tiktok'];
                    $minWoodPriceCurrency = $variant['currency'] ?? 'USD';
                    $minWoodPriceMarket = $variant['market'];
                }
            }
        }

        // Get first variant with price for summary display (for currency/market info)
        $firstVariantWithWoodPrice = $variantsData->first(function ($variant) {
            return !is_null($variant['prices']['seller']) || !is_null($variant['prices']['tiktok']);
        });

        // If no variant has price, try to get any price from first variant
        if (!$firstVariantWithWoodPrice && $variantsData->isNotEmpty()) {
            $firstVariantWithWoodPrice = $variantsData->first();
        }

        // Get all active markets for price display
        $markets = Market::where('status', 'active')->get();

        // Get related products (same workshop, different product)
        $relatedProducts = Product::where('status', 'active')
            ->where('workshop_id', $product->workshop_id)
            ->where('id', '!=', $product->id)
            ->with(['images' => function ($query) {
                $query->orderBy('is_primary', 'desc')->orderBy('sort_order')->limit(1);
            }])
            ->whereHas('images')
            ->latest()
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'markets', 'relatedProducts', 'defaultTier', 'variantsData', 'firstVariantWithWoodPrice', 'woodTier', 'minWoodPrice', 'minWoodPriceCurrency', 'minWoodPriceMarket', 'currenciesToConvert', 'marketCode'));
    }
}
