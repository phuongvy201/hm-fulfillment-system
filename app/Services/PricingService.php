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
    public function convertCurrency(float $amount, string $fromCurrency, string $toCurrency, $date = null): float
    {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        // Get exchange rate from database
        $rate = \App\Models\ExchangeRate::getCurrentRate($fromCurrency, $toCurrency, $date);

        if ($rate === null) {
            // Fallback to hardcoded rates if no database rate found
            // This ensures backward compatibility
            $fallbackRates = [
            'USD' => ['GBP' => 0.79, 'VND' => 24500, 'EUR' => 0.92],
            'GBP' => ['USD' => 1.27, 'VND' => 31000, 'EUR' => 1.17],
            'VND' => ['USD' => 0.000041, 'GBP' => 0.000032, 'EUR' => 0.000038],
            'EUR' => ['USD' => 1.09, 'GBP' => 0.85, 'VND' => 26600],
        ];

            $rate = $fallbackRates[$fromCurrency][$toCurrency] ?? 1;
        }

        return $amount * $rate;
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
