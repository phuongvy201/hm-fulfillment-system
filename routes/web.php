<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Super Admin only routes
Route::middleware(['auth', 'role:super-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    Route::resource('teams', \App\Http\Controllers\Admin\TeamController::class);

    // Markets
    Route::resource('markets', \App\Http\Controllers\Admin\MarketController::class);

    // Workshops
    Route::resource('workshops', \App\Http\Controllers\Admin\WorkshopController::class);

    // Pricing Tiers
    Route::resource('pricing-tiers', \App\Http\Controllers\Admin\PricingTierController::class);

    // Pricing Rules
    Route::resource('pricing-rules', \App\Http\Controllers\Admin\PricingRuleController::class);

    // Products
    Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);

    // Product Variants (nested)
    Route::prefix('products/{product}')->name('products.')->group(function () {
        Route::get('variants/create', [\App\Http\Controllers\Admin\ProductVariantController::class, 'create'])->name('variants.create');
        Route::post('variants', [\App\Http\Controllers\Admin\ProductVariantController::class, 'store'])->name('variants.store');
        Route::get('variants/bulk-create', [\App\Http\Controllers\Admin\ProductVariantController::class, 'bulkCreate'])->name('variants.bulk-create');
        Route::post('variants/bulk-store', [\App\Http\Controllers\Admin\ProductVariantController::class, 'bulkStore'])->name('variants.bulk-store');
        Route::get('variants/{variant}/edit', [\App\Http\Controllers\Admin\ProductVariantController::class, 'edit'])->name('variants.edit');
        Route::put('variants/{variant}', [\App\Http\Controllers\Admin\ProductVariantController::class, 'update'])->name('variants.update');
        Route::delete('variants/{variant}', [\App\Http\Controllers\Admin\ProductVariantController::class, 'destroy'])->name('variants.destroy');

        // Variant Prices
        Route::get('variants/{variant}/prices/create', [\App\Http\Controllers\Admin\ProductTierPriceController::class, 'create'])->name('variants.prices.create');
        Route::post('variants/{variant}/prices', [\App\Http\Controllers\Admin\ProductTierPriceController::class, 'store'])->name('variants.prices.store');
    });

    // Workshop SKUs
    Route::get('variants/{variant}/workshop-skus', [\App\Http\Controllers\Admin\WorkshopSkuController::class, 'create'])->name('workshop-skus.create');
    Route::post('variants/{variant}/workshop-skus', [\App\Http\Controllers\Admin\WorkshopSkuController::class, 'store'])->name('workshop-skus.store');

    // Import routes
    Route::get('import', [\App\Http\Controllers\Admin\ImportController::class, 'index'])->name('import.index');
    Route::post('import/products', [\App\Http\Controllers\Admin\ImportController::class, 'importProducts'])->name('import.products');
    Route::post('import/variants', [\App\Http\Controllers\Admin\ImportController::class, 'importVariants'])->name('import.variants');
    Route::post('import/product-prices', [\App\Http\Controllers\Admin\ImportController::class, 'importProductPrices'])->name('import.product-prices');
    Route::post('import/user-prices', [\App\Http\Controllers\Admin\ImportController::class, 'importUserPrices'])->name('import.user-prices');
    Route::post('import/team-prices', [\App\Http\Controllers\Admin\ImportController::class, 'importTeamPrices'])->name('import.team-prices');
    Route::get('import/sample/{type}', [\App\Http\Controllers\Admin\ImportController::class, 'downloadSample'])->name('import.sample');
});
