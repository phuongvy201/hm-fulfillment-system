<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

        // Get all active markets (fallback)
        $markets = Market::where('status', 'active')->get();

        // Get all active tiers
        $tiers = PricingTier::where('status', 'active')->orderBy('priority')->get();

        // Get existing prices for this variant
        $existingPrices = ProductTierPrice::where('variant_id', $variant->id)
            ->with(['market', 'pricingTier'])
            ->get()
            ->keyBy(function ($price) {
                return $price->market_id . '_' . $price->pricing_tier_id;
            });

        return view('admin.products.variants.prices.create', compact('product', 'variant', 'markets', 'tiers', 'market', 'existingPrices'));
    }

    /**
     * Store prices for a variant.
     */
    public function store(Request $request, Product $product, ProductVariant $variant)
    {
        $validated = $request->validate([
            'prices' => ['required', 'array'],
        ]);

        // Validate each price entry
        foreach ($validated['prices'] as $key => $priceData) {
            $request->validate([
                "prices.{$key}.market_id" => ['required', 'exists:markets,id'],
                "prices.{$key}.pricing_tier_id" => ['required', 'exists:pricing_tiers,id'],
                "prices.{$key}.base_price" => ['nullable', 'numeric', 'min:0'],
                "prices.{$key}.status" => ['required', 'in:active,inactive'],
            ]);
        }

        $saved = 0;
        $errors = [];

        foreach ($validated['prices'] as $key => $priceData) {
            try {
                // Check if price exists
                $existingPrice = ProductTierPrice::where('product_id', $product->id)
                    ->where('variant_id', $variant->id)
                    ->where('market_id', $priceData['market_id'])
                    ->where('pricing_tier_id', $priceData['pricing_tier_id'])
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
                        'pricing_tier_id' => $priceData['pricing_tier_id'],
                    ],
                    [
                        'base_price' => $priceData['base_price'],
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
}
