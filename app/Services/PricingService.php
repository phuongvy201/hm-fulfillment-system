<?php

namespace App\Services;

use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Market;
use App\Models\UserCustomPrice;
use App\Models\TeamPrice;
use Carbon\Carbon;

class PricingService
{
    /**
     * Get price for a product/variant for a specific user and market.
     * Priority: User Custom Price > Team Price > Tier Price > Default Price
     * TODO: Implement full pricing system with printing prices, rules, and tier prices
     */
    public function getPrice(
        User $user,
        Product $product,
        ?ProductVariant $variant = null,
        Market $market
    ): ?array {
        $now = Carbon::now();

        // 1. Check user-specific custom price (highest priority)
        $userPrice = UserCustomPrice::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->where('variant_id', $variant?->id)
            ->where('market_id', $market->id)
            ->where('status', 'active')
            ->where(function ($query) use ($now) {
                $query->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', $now);
            })
            ->first();

        if ($userPrice) {
            return [
                'price' => $userPrice->price,
                'currency' => $userPrice->currency,
                'source' => 'user_custom',
            ];
        }

        // TODO: Implement team price, tier price, printing price calculation with rules

        return null;
    }

    /**
     * Convert price from one currency to another.
     */
    public function convertCurrency(float $amount, string $fromCurrency, string $toCurrency): float
    {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        // Exchange rates (should be stored in database or fetched from API)
        $rates = [
            'USD' => ['GBP' => 0.79, 'VND' => 24500],
            'GBP' => ['USD' => 1.27, 'VND' => 31000],
            'VND' => ['USD' => 0.000041, 'GBP' => 0.000032],
        ];

        return $amount * ($rates[$fromCurrency][$toCurrency] ?? 1);
    }

    /**
     * Get all available prices for a product/variant in a market.
     * TODO: Implement new pricing system with all price types
     */
    public function getAllPrices(
        Product $product,
        ?ProductVariant $variant = null,
        Market $market
    ): array {
        // TODO: Implement new pricing system
        // Should return: tier prices, printing prices, workshop prices, etc.
        return [];
    }
}
