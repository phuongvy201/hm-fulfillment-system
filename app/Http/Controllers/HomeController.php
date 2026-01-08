<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the home page.
     */
    public function index()
    {
        // Get featured products (active products with images)
        $featuredProducts = Product::where('status', 'active')
            ->with(['images' => function($query) {
                $query->orderBy('is_primary', 'desc')->orderBy('sort_order')->limit(1);
            }, 'workshop'])
            ->whereHas('images')
            ->latest()
            ->take(6)
            ->get();

        // Get products for grid (8 products with pricing info)
        $products = Product::where('status', 'active')
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
            ->whereHas('images')
            ->latest()
            ->take(8)
            ->get();

        // Get all active products count
        $productsCount = Product::where('status', 'active')->count();

        return view('home', compact('featuredProducts', 'productsCount', 'products'));
    }
}
