<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Products - {{ config('app.name', 'HM Fulfillment System') }}</title>
    <meta name="description" content="Browse all our print-on-demand products. Explore our wide range of customizable products for your business.">
    
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            background-color: #ffffff;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        /* Header */
        header {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 2rem;
        }
        
        .logo-section {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .logo-main {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.75rem;
            font-weight: 800;
            color: #1f2937;
        }
        
        .logo-image {
            height: 120px;
            width: auto;
            object-fit: contain;
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 900;
            font-size: 1.25rem;
        }
        
        .logo-text {
            color: #1f2937;
        }
        
        .logo-tagline {
            font-size: 0.75rem;
            color: #6b7280;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        
        .nav-menu {
            display: flex;
            align-items: center;
            gap: 0;
            background: #f3f4f6;
            padding: 0.5rem;
            border-radius: 12px;
        }
        
        .nav-menu a {
            padding: 0.625rem 1.25rem;
            text-decoration: none;
            color: #374151;
            font-weight: 500;
            font-size: 0.9375rem;
            border-radius: 8px;
            transition: all 0.2s;
        }
        
        .nav-menu a:hover,
        .nav-menu a.active {
            background: #ffffff;
            color: #f97316;
        }
        
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9375rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: #f97316;
            color: white;
        }
        
        .btn-primary:hover {
            background: #ea580c;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.4);
        }
        
        .btn-outline {
            background: #e0f2fe;
            color: #0284c7;
            border: 1px solid #0284c7;
        }
        
        .btn-outline:hover {
            background: #0284c7;
            color: white;
        }
        
        /* Page Header */
        .page-header {
            padding: 3rem 2rem 2rem;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 1rem;
            font-family: Georgia, 'Times New Roman', serif;
        }
        
        .page-header p {
            font-size: 1.125rem;
            color: #6b7280;
        }
        
        /* Search Section */
        .search-section {
            padding: 2rem;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .search-form {
            max-width: 600px;
            margin: 0 auto;
            display: flex;
            gap: 1rem;
        }
        
        .search-input {
            flex: 1;
            padding: 0.875rem 1.25rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #f97316;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
        }
        
        .search-btn {
            padding: 0.875rem 2rem;
            background: #f97316;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .search-btn:hover {
            background: #ea580c;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.4);
        }
        
        /* Products Grid Section */
        .products-grid-section {
            padding: 4rem 2rem;
            background: #ffffff;
        }
        
        .products-content {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 3rem;
        }
        
        /* Filter Sidebar */
        .filter-sidebar {
            background: #f9fafb;
            border-radius: 12px;
            padding: 1.5rem;
            height: fit-content;
            position: sticky;
            top: 100px;
        }
        
        .filter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .filter-header h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
        }
        
        .clear-filters {
            font-size: 0.875rem;
            color: #f97316;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .clear-filters:hover {
            color: #ea580c;
            text-decoration: underline;
        }
        
        .filter-group {
            margin-bottom: 2rem;
        }
        
        .filter-group:last-child {
            margin-bottom: 0;
        }
        
        .filter-group-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1rem;
        }
        
        .filter-options {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .filter-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .filter-option input[type="radio"],
        .filter-option input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #f97316;
        }
        
        .filter-option label {
            font-size: 0.9375rem;
            color: #4b5563;
            cursor: pointer;
            flex: 1;
        }
        
        .filter-option label:hover {
            color: #1f2937;
        }
        
        .price-range-inputs {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .price-input {
            flex: 1;
            padding: 0.5rem 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 0.875rem;
        }
        
        .price-input:focus {
            outline: none;
            border-color: #f97316;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
        }
        
        .filter-apply-btn {
            width: 100%;
            padding: 0.75rem;
            background: #f97316;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 1rem;
        }
        
        .filter-apply-btn:hover {
            background: #ea580c;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.4);
        }
        
        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .products-count {
            color: #6b7280;
            font-size: 1rem;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .filter-toggle {
            display: none;
            padding: 0.75rem 1.5rem;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-weight: 600;
            color: #374151;
            cursor: pointer;
            margin-bottom: 1.5rem;
            align-items: center;
            gap: 0.5rem;
        }
        
        .filter-toggle:hover {
            background: #e5e7eb;
        }
        
        .product-card {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .product-image-wrapper {
            width: 100%;
            height: 300px;
            background: #f9fafb;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .product-info {
            padding: 1.5rem;
        }
        
        .product-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.75rem;
            line-height: 1.4;
        }
        
        .product-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: #f97316;
        }
        
        .product-price-label {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 400;
        }
        
        /* Pagination */
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 3rem;
        }
        
        .pagination {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .pagination a,
        .pagination span {
            padding: 0.625rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            color: #374151;
            font-weight: 500;
            transition: all 0.2s;
            border: 1px solid #e5e7eb;
        }
        
        .pagination a:hover {
            background: #f97316;
            color: white;
            border-color: #f97316;
        }
        
        .pagination .active span {
            background: #f97316;
            color: white;
            border-color: #f97316;
        }
        
        .pagination .disabled span {
            color: #9ca3af;
            cursor: not-allowed;
            background: #f3f4f6;
        }
        
        /* Footer */
        footer {
            background: #1f2937;
            color: white;
            padding: 4rem 2rem 2rem;
            margin-top: 4rem;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-bottom: 2rem;
        }
        
        .footer-section h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        
        .footer-section p,
        .footer-section a {
            color: #d1d5db;
            text-decoration: none;
            line-height: 2;
            display: block;
        }
        
        .footer-section a:hover {
            color: #f97316;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid #374151;
            color: #9ca3af;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .products-content {
                grid-template-columns: 1fr;
            }
            
            .filter-sidebar {
                position: relative;
                top: 0;
                margin-bottom: 2rem;
            }
            
            .filter-toggle {
                display: flex;
            }
            
            .filter-sidebar.hidden {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            nav {
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav-menu {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 1.5rem;
            }
            
            .products-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <nav class="container">
            <div class="logo-section">
                <div class="logo-main">
                    <a href="{{ route('home') }}" style="display: flex; align-items: center; gap: 0.5rem; text-decoration: none; color: inherit;">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url('images/logo HM-02.png') }}" alt="HM FULFILL" class="logo-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="logo-fallback" style="display: none; align-items: center; gap: 0.5rem;">
                            <div class="logo-icon">HM</div>
                            <span class="logo-text">FULFILL</span>
                        </div>
                    </a>
                </div>
            </div>
            <div class="nav-menu">
                <a href="{{ route('home') }}#how-it-works">How it works</a>
                <a href="{{ route('products.index') }}" class="active">All products</a>
                <a href="{{ route('home') }}#sku">SKU</a>
                <a href="{{ route('home') }}#blogs">Blogs</a>
                <a href="{{ route('home') }}#contact">Contact Us</a>
                <a href="{{ route('home') }}#help">Help Center</a>
            </div>
            <div class="nav-actions">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-outline">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">Sign in</a>
                @endauth
            </div>
        </nav>
    </header>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>All Products</h1>
            <p>Explore our complete collection of print-on-demand products</p>
        </div>
    </section>

    <!-- Search Section -->
    <section class="search-section">
        <div class="container">
            <form action="{{ route('products.index') }}" method="GET" class="search-form">
                <input 
                    type="text" 
                    name="search" 
                    class="search-input" 
                    placeholder="Search products..." 
                    value="{{ request('search') }}"
                >
                <button type="submit" class="search-btn">Search</button>
            </form>
        </div>
    </section>

    <!-- Products Grid Section -->
    <section class="products-grid-section">
        <div class="container">
            <div class="products-content">
                <!-- Filter Sidebar -->
                <aside class="filter-sidebar" id="filterSidebar">
                    <div class="filter-header">
                        <h3>Filters</h3>
                        @if(request()->anyFilled(['min_price', 'max_price', 'market']))
                            <a href="{{ route('products.index', request()->only('search')) }}" class="clear-filters">Clear All</a>
                        @endif
                    </div>
                    
                    <form action="{{ route('products.index') }}" method="GET" id="filterForm">
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        
                        <!-- Price Range Filter -->
                        <div class="filter-group">
                            <h4 class="filter-group-title">Price Range</h4>
                            <div class="price-range-inputs">
                                <input 
                                    type="number" 
                                    name="min_price" 
                                    class="price-input" 
                                    placeholder="Min" 
                                    value="{{ request('min_price') }}"
                                    step="0.01"
                                    min="0"
                                >
                                <span style="color: #6b7280;">-</span>
                                <input 
                                    type="number" 
                                    name="max_price" 
                                    class="price-input" 
                                    placeholder="Max" 
                                    value="{{ request('max_price') }}"
                                    step="0.01"
                                    min="0"
                                >
                            </div>
                        </div>
                        
                        <!-- Market Filter -->
                        @if($markets->count() > 0)
                            <div class="filter-group">
                                <h4 class="filter-group-title">Market</h4>
                                <div class="filter-options">
                                    @foreach($markets as $market)
                                        <div class="filter-option">
                                            <input 
                                                type="radio" 
                                                name="market" 
                                                id="market_{{ $market->id }}" 
                                                value="{{ $market->id }}"
                                                {{ request('market') == $market->id ? 'checked' : '' }}
                                                onchange="document.getElementById('filterForm').submit();"
                                            >
                                            <label for="market_{{ $market->id }}">{{ $market->name }} ({{ $market->code }})</label>
                                        </div>
                                    @endforeach
                                    @if(request('market'))
                                        <div class="filter-option">
                                            <input 
                                                type="radio" 
                                                name="market" 
                                                id="market_all" 
                                                value=""
                                                onchange="document.getElementById('filterForm').submit();"
                                            >
                                            <label for="market_all">All Markets</label>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                        
                        <button type="submit" class="filter-apply-btn">Apply Filters</button>
                    </form>
                </aside>
                
                <!-- Products Grid -->
                <div class="products-main">
                    <button class="filter-toggle" onclick="toggleFilter()">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        Filters
                    </button>
                    
                    <div class="products-header">
                        <div>
                            <h2 style="font-size: 1.5rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem;">Our Products</h2>
                            <p class="products-count">
                                Showing {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products
                                @if(request('search'))
                                    for "{{ request('search') }}"
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <div class="products-grid">
                @forelse($products as $product)
                    @php
                        $primaryImage = $product->images->first();
                        $firstVariant = $product->variants->first();
                        $price = null;
                        $currency = 'USD';
                        $marketCode = 'US';
                        
                        if ($firstVariant) {
                            $tierPrice = $firstVariant->tierPrices->first();
                            if ($tierPrice) {
                                $price = $tierPrice->base_price;
                                $currency = $tierPrice->currency;
                                $market = $tierPrice->market;
                                if ($market) {
                                    $marketCode = $market->code ?? 'US';
                                }
                            }
                        }
                        
                        // Format currency symbol
                        $currencySymbol = match($currency) {
                            'GBP' => '£',
                            'USD' => '$',
                            'EUR' => '€',
                            default => '$'
                        };
                    @endphp
                    <a href="{{ route('products.show', $product->slug) }}" class="product-card">
                        <div class="product-image-wrapper">
                            @if($primaryImage)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($primaryImage->image_path) }}" alt="{{ $product->name }}" class="product-image">
                            @else
                                <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #9ca3af;">
                                    <svg width="80" height="80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">{{ $product->name }}</h3>
                            @if($price)
                                <div class="product-price">
                                    <span class="product-price-label">From: </span>
                                    {{ $currencySymbol }}{{ number_format($price, 2) }} {{ $marketCode }}
                                </div>
                            @else
                                <div class="product-price">
                                    <span class="product-price-label">Contact for price</span>
                                </div>
                            @endif
                        </div>
                    </a>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; padding: 4rem; color: #6b7280;">
                        <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin: 0 auto 1rem; opacity: 0.5;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <h3 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 0.5rem; color: #1f2937;">No products found</h3>
                        <p>
                            @if(request('search'))
                                No products match your search "{{ request('search') }}". Try different keywords.
                            @else
                                No products available at the moment.
                            @endif
                        </p>
                    </div>
                @endforelse
            </div>

                    <!-- Pagination -->
                    @if($products->hasPages())
                        <div class="pagination-wrapper">
                            <div class="pagination">
                                {{ $products->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
    
    <script>
        function toggleFilter() {
            const sidebar = document.getElementById('filterSidebar');
            sidebar.classList.toggle('hidden');
        }
    </script>

    <!-- Footer -->
    <footer id="contact">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>HM FULFILL</h3>
                    <p>Quality is everything. Professional print-on-demand fulfillment services for creators and businesses worldwide.</p>
                </div>
                
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <a href="{{ route('home') }}#how-it-works">How it works</a>
                    <a href="{{ route('products.index') }}">All products</a>
                    <a href="{{ route('home') }}#sku">SKU Management</a>
                    @auth
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}">Login</a>
                        <a href="{{ route('register') }}">Register</a>
                    @endauth
                </div>
                
                <div class="footer-section">
                    <h3>Services</h3>
                    <p>Print-on-Demand</p>
                    <p>Order Fulfillment</p>
                    <p>Global Shipping</p>
                    <p>Design Tools</p>
                </div>
                
                <div class="footer-section" id="help">
                    <h3>Support</h3>
                    <p>Need help? We're here for you.</p>
                    <a href="mailto:support@hmfulfill.com">support@hmfulfill.com</a>
                    <a href="tel:+1234567890">+1 (234) 567-890</a>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} HM FULFILL. All rights reserved. | Quality is Everything</p>
            </div>
        </div>
    </footer>
</body>
</html>

