<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Public routes
Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/products', [\App\Http\Controllers\ProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [\App\Http\Controllers\ProductController::class, 'show'])->name('products.show');

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

    // Customer routes (non-admin users)
    Route::middleware(\App\Http\Middleware\CheckCustomer::class)->prefix('customer')->name('customer.')->group(function () {
        // Top-up Requests
        Route::resource('top-up-requests', \App\Http\Controllers\Customer\TopUpRequestController::class)->only(['index', 'create', 'store', 'show']);

        // Debt Payment Requests
        Route::resource('debt-payment-requests', \App\Http\Controllers\Customer\DebtPaymentRequestController::class)->only(['index', 'create', 'store', 'show']);
    });
});

// Super Admin only routes
Route::middleware(['auth', 'role:super-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    Route::resource('teams', \App\Http\Controllers\Admin\TeamController::class);

    // Markets
    Route::resource('markets', \App\Http\Controllers\Admin\MarketController::class);

    // Workshops
    Route::resource('workshops', \App\Http\Controllers\Admin\WorkshopController::class);
    Route::post('workshops/{workshop}/test-api', [\App\Http\Controllers\Admin\WorkshopController::class, 'testApi'])->name('workshops.test-api');

    // Orders
    Route::resource('orders', \App\Http\Controllers\Admin\OrderController::class);
    Route::post('orders/{order}/submit', [\App\Http\Controllers\Admin\OrderController::class, 'submit'])->name('orders.submit');
    Route::post('orders/{order}/tracking', [\App\Http\Controllers\Admin\OrderController::class, 'getTracking'])->name('orders.tracking');
    Route::post('orders/{order}/update-status', [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.update-status');

    // Pricing Tiers
    Route::resource('pricing-tiers', \App\Http\Controllers\Admin\PricingTierController::class);

    // Permissions Management
    Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class);
    Route::get('roles/{role}/permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'rolePermissions'])->name('roles.permissions');
    Route::post('roles/{role}/permissions/assign', [\App\Http\Controllers\Admin\PermissionController::class, 'assignToRole'])->name('roles.permissions.assign');

    // User Pricing Tiers Management
    Route::get('user-pricing-tiers', [\App\Http\Controllers\Admin\UserPricingTierController::class, 'index'])->name('user-pricing-tiers.index');
    Route::get('user-pricing-tiers/{user}/edit', [\App\Http\Controllers\Admin\UserPricingTierController::class, 'edit'])->name('user-pricing-tiers.edit');
    Route::put('user-pricing-tiers/{user}', [\App\Http\Controllers\Admin\UserPricingTierController::class, 'update'])->name('user-pricing-tiers.update');

    // Wallet Management
    Route::get('wallets', [\App\Http\Controllers\Admin\WalletController::class, 'index'])->name('wallets.index');
    Route::get('wallets/{user}', [\App\Http\Controllers\Admin\WalletController::class, 'show'])->name('wallets.show');
    Route::post('wallets/{user}/adjust', [\App\Http\Controllers\Admin\WalletController::class, 'adjust'])->name('wallets.adjust');
    Route::post('wallets/{user}/refund', [\App\Http\Controllers\Admin\WalletController::class, 'refund'])->name('wallets.refund');

    // Top-up Requests (Admin only - approve/reject)
    Route::get('top-up-requests', [\App\Http\Controllers\Admin\TopUpRequestController::class, 'index'])->name('top-up-requests.index');
    Route::get('top-up-requests/{topUpRequest}', [\App\Http\Controllers\Admin\TopUpRequestController::class, 'show'])->name('top-up-requests.show');
    Route::get('top-up-requests/{topUpRequest}/edit', [\App\Http\Controllers\Admin\TopUpRequestController::class, 'edit'])->name('top-up-requests.edit');
    Route::put('top-up-requests/{topUpRequest}', [\App\Http\Controllers\Admin\TopUpRequestController::class, 'update'])->name('top-up-requests.update');
    Route::post('top-up-requests/{topUpRequest}/approve', [\App\Http\Controllers\Admin\TopUpRequestController::class, 'approve'])->name('top-up-requests.approve');
    Route::post('top-up-requests/{topUpRequest}/reject', [\App\Http\Controllers\Admin\TopUpRequestController::class, 'reject'])->name('top-up-requests.reject');

    // Credit Management
    Route::get('credits', [\App\Http\Controllers\Admin\CreditController::class, 'index'])->name('credits.index');
    Route::get('credits/{user}/edit', [\App\Http\Controllers\Admin\CreditController::class, 'edit'])->name('credits.edit');
    Route::put('credits/{user}', [\App\Http\Controllers\Admin\CreditController::class, 'update'])->name('credits.update');
    Route::post('credits/{user}/pay-from-wallet', [\App\Http\Controllers\Admin\CreditController::class, 'payFromWallet'])->name('credits.pay-from-wallet');
    Route::post('credits/{user}/adjust-debt', [\App\Http\Controllers\Admin\CreditController::class, 'adjustDebt'])->name('credits.adjust-debt');

    // Debt Payment Requests (Admin)
    Route::get('debt-payment-requests', [\App\Http\Controllers\Admin\DebtPaymentRequestController::class, 'index'])->name('debt-payment-requests.index');
    Route::get('debt-payment-requests/{debtPaymentRequest}', [\App\Http\Controllers\Admin\DebtPaymentRequestController::class, 'show'])->name('debt-payment-requests.show');
    Route::post('debt-payment-requests/{debtPaymentRequest}/approve', [\App\Http\Controllers\Admin\DebtPaymentRequestController::class, 'approve'])->name('debt-payment-requests.approve');
    Route::post('debt-payment-requests/{debtPaymentRequest}/reject', [\App\Http\Controllers\Admin\DebtPaymentRequestController::class, 'reject'])->name('debt-payment-requests.reject');

    // Products
    Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);
    Route::get('products/trashed/list', [\App\Http\Controllers\Admin\ProductController::class, 'trashed'])->name('products.trashed');
    Route::post('products/{id}/restore', [\App\Http\Controllers\Admin\ProductController::class, 'restore'])->name('products.restore');

    // Product Images
    Route::prefix('products/{product}')->name('products.')->group(function () {
        Route::post('images/upload', [\App\Http\Controllers\Admin\ProductController::class, 'uploadImagesAction'])->name('images.upload');
        Route::delete('images/{image}', [\App\Http\Controllers\Admin\ProductController::class, 'deleteImage'])->name('images.delete');
        Route::post('images/{image}/set-primary', [\App\Http\Controllers\Admin\ProductController::class, 'setPrimaryImage'])->name('images.set-primary');
        Route::post('images/reorder', [\App\Http\Controllers\Admin\ProductController::class, 'updateImageOrder'])->name('images.reorder');
    });

    // Product Variants (nested)
    Route::prefix('products/{product}')->name('products.')->group(function () {
        Route::get('variants/create', [\App\Http\Controllers\Admin\ProductVariantController::class, 'create'])->name('variants.create');
        Route::post('variants', [\App\Http\Controllers\Admin\ProductVariantController::class, 'store'])->name('variants.store');
        Route::get('variants/bulk-create', [\App\Http\Controllers\Admin\ProductVariantController::class, 'bulkCreate'])->name('variants.bulk-create');
        Route::post('variants/bulk-store', [\App\Http\Controllers\Admin\ProductVariantController::class, 'bulkStore'])->name('variants.bulk-store');
        Route::delete('variants/bulk-delete', [\App\Http\Controllers\Admin\ProductVariantController::class, 'bulkDestroy'])->name('variants.bulk-destroy');
        Route::get('variants/{variant}/edit', [\App\Http\Controllers\Admin\ProductVariantController::class, 'edit'])->name('variants.edit');
        Route::put('variants/{variant}', [\App\Http\Controllers\Admin\ProductVariantController::class, 'update'])->name('variants.update');
        Route::delete('variants/{variant}', [\App\Http\Controllers\Admin\ProductVariantController::class, 'destroy'])->name('variants.destroy');

        // Variant Prices
        Route::get('variants/{variant}/prices/create', [\App\Http\Controllers\Admin\ProductTierPriceController::class, 'create'])->name('variants.prices.create');
        Route::post('variants/{variant}/prices', [\App\Http\Controllers\Admin\ProductTierPriceController::class, 'store'])->name('variants.prices.store');

        // Bulk Variant Prices
        Route::get('variants/bulk-prices/create', [\App\Http\Controllers\Admin\ProductTierPriceController::class, 'bulkCreate'])->name('variants.bulk-prices.create');
        Route::post('variants/bulk-prices', [\App\Http\Controllers\Admin\ProductTierPriceController::class, 'bulkStore'])->name('variants.bulk-prices.store');

        // Bulk Printing Prices
        Route::get('variants/bulk-printing-prices/create', [\App\Http\Controllers\Admin\ProductPrintingPriceController::class, 'bulkCreate'])->name('variants.bulk-printing-prices.create');
        Route::post('variants/bulk-printing-prices', [\App\Http\Controllers\Admin\ProductPrintingPriceController::class, 'bulkStore'])->name('variants.bulk-printing-prices.store');

        // User Custom Prices
        Route::get('variants/{variant}/user-prices/{user}/create', [\App\Http\Controllers\Admin\UserCustomPriceController::class, 'create'])->name('variants.user-prices.create');
        Route::post('variants/{variant}/user-prices/{user}', [\App\Http\Controllers\Admin\UserCustomPriceController::class, 'store'])->name('variants.user-prices.store');

        // Bulk User Custom Prices
        Route::get('variants/user-prices/bulk-create', [\App\Http\Controllers\Admin\UserCustomPriceController::class, 'bulkCreate'])->name('variants.user-prices.bulk-create');
        Route::post('variants/user-prices/bulk-store', [\App\Http\Controllers\Admin\UserCustomPriceController::class, 'bulkStore'])->name('variants.user-prices.bulk-store');

        // Workshop Prices
        Route::get('workshop-prices/bulk-create', [\App\Http\Controllers\Admin\WorkshopPriceController::class, 'bulkCreate'])->name('workshop-prices.bulk-create');
        Route::post('workshop-prices/bulk-store', [\App\Http\Controllers\Admin\WorkshopPriceController::class, 'bulkStore'])->name('workshop-prices.bulk-store');
        Route::get('variants/{variant}/workshop-prices/create', [\App\Http\Controllers\Admin\WorkshopPriceController::class, 'create'])->name('workshop-prices.create');
        Route::post('variants/{variant}/workshop-prices', [\App\Http\Controllers\Admin\WorkshopPriceController::class, 'store'])->name('workshop-prices.store');
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
