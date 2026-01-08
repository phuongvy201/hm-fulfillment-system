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
                'images' => function($query) {
                    $query->orderBy('is_primary', 'desc')->orderBy('sort_order')->limit(1);
                },
                'variants' => function($query) {
                    $query->where('status', 'active')->limit(1);
                },
                'variants.tierPrices' => function($query) {
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
            $query->whereHas('variants.tierPrices', function($q) use ($request) {
                $q->where('status', 'active')
                  ->where('base_price', '>=', $request->min_price);
            });
        }

        if ($request->has('max_price') && $request->max_price) {
            $query->whereHas('variants.tierPrices', function($q) use ($request) {
                $q->where('status', 'active')
                  ->where('base_price', '<=', $request->max_price);
            });
        }

        // Filter by market
        if ($request->has('market') && $request->market) {
            $query->whereHas('variants.tierPrices', function($q) use ($request) {
                $q->where('status', 'active')
                  ->where('market_id', $request->market);
            });
        }

        // Get filter data for view
        $workshops = Workshop::where('status', 'active')
            ->whereHas('products', function($q) {
                $q->where('status', 'active')->whereHas('images');
            })
            ->orderBy('name')
            ->get();

        // Get markets that have active tier prices
        $marketIds = ProductTierPrice::where('status', 'active')
            ->whereHas('product', function($q) {
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
                'images' => function($query) {
                    $query->orderBy('is_primary', 'desc')->orderBy('sort_order');
                },
                'variants' => function($query) {
                    $query->where('status', 'active');
                },
                'variants.variantAttributes',
                'variants.tierPrices' => function($query) {
                    $query->where('status', 'active')
                        ->with('market', 'pricingTier')
                        ->orderBy('base_price');
                },
                'workshop.market'
            ])
            ->firstOrFail();

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

        // Prepare variants data for JavaScript
        $variantsData = $product->variants->map(function($variant) use ($defaultTier) {
            $attributes = [];
            foreach ($variant->variantAttributes as $attr) {
                $attributes[$attr->attribute_name] = $attr->attribute_value;
            }
            
            // Get prices for all shipping types from default tier (wood tier)
            $prices = [
                'default' => null,
                'seller' => null,
                'tiktok' => null,
            ];
            $currency = 'USD';
            $market = null;
            
            if ($defaultTier) {
                // Get default price (shipping_type = null)
                $defaultPrice = $variant->tierPrices
                    ->where('pricing_tier_id', $defaultTier->id)
                    ->where('status', 'active')
                    ->whereNull('shipping_type')
                    ->first();
                
                if ($defaultPrice) {
                    $prices['default'] = $defaultPrice->base_price;
                    $currency = $defaultPrice->currency;
                    $market = $defaultPrice->market;
                }
                
                // Get seller price
                $sellerPrice = $variant->tierPrices
                    ->where('pricing_tier_id', $defaultTier->id)
                    ->where('status', 'active')
                    ->where('shipping_type', 'seller')
                    ->first();
                
                if ($sellerPrice) {
                    $prices['seller'] = $sellerPrice->base_price;
                    if (!$currency) $currency = $sellerPrice->currency;
                    if (!$market) $market = $sellerPrice->market;
                }
                
                // Get tiktok price
                $tiktokPrice = $variant->tierPrices
                    ->where('pricing_tier_id', $defaultTier->id)
                    ->where('status', 'active')
                    ->where('shipping_type', 'tiktok')
                    ->first();
                
                if ($tiktokPrice) {
                    $prices['tiktok'] = $tiktokPrice->base_price;
                    if (!$currency) $currency = $tiktokPrice->currency;
                    if (!$market) $market = $tiktokPrice->market;
                }
            }
            
            // Fallback: if no default tier prices, get first active prices
            if (!$prices['default'] && !$prices['seller'] && !$prices['tiktok']) {
                $activePrices = $variant->tierPrices->where('status', 'active');
                
                // Get default price
                $firstDefault = $activePrices->whereNull('shipping_type')->first();
                if ($firstDefault) {
                    $prices['default'] = $firstDefault->base_price;
                    $currency = $firstDefault->currency;
                    $market = $firstDefault->market;
                }
                
                // Get seller price
                $firstSeller = $activePrices->where('shipping_type', 'seller')->first();
                if ($firstSeller) {
                    $prices['seller'] = $firstSeller->base_price;
                    if (!$currency) $currency = $firstSeller->currency;
                    if (!$market) $market = $firstSeller->market;
                }
                
                // Get tiktok price
                $firstTiktok = $activePrices->where('shipping_type', 'tiktok')->first();
                if ($firstTiktok) {
                    $prices['tiktok'] = $firstTiktok->base_price;
                    if (!$currency) $currency = $firstTiktok->currency;
                    if (!$market) $market = $firstTiktok->market;
                }
            }
            
            return [
                'id' => $variant->id,
                'sku' => $variant->sku ?? 'N/A',
                'attributes' => $attributes,
                'prices' => $prices,
                'currency' => $currency,
                'market' => $market ? $market->name : null,
            ];
        });

        // Get all active markets for price display
        $markets = Market::where('status', 'active')->get();

        // Get related products (same workshop, different product)
        $relatedProducts = Product::where('status', 'active')
            ->where('workshop_id', $product->workshop_id)
            ->where('id', '!=', $product->id)
            ->with(['images' => function($query) {
                $query->orderBy('is_primary', 'desc')->orderBy('sort_order')->limit(1);
            }])
            ->whereHas('images')
            ->latest()
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'markets', 'relatedProducts', 'defaultTier', 'variantsData'));
    }
}




