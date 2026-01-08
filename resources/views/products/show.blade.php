<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->name }} - {{ config('app.name', 'HM Fulfillment System') }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit($product->description ?? $product->name, 160) }}">
    
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
        
        .nav-menu a:hover {
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
        
        /* Breadcrumb */
        .breadcrumb {
            padding: 1rem 2rem;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .breadcrumb a {
            color: #6b7280;
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            color: #f97316;
        }
        
        /* Product Details */
        .product-details {
            padding: 3rem 2rem;
        }
        
        .product-main {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            margin-bottom: 4rem;
        }
        
        .product-gallery {
            position: relative;
        }
        
        .product-main-image {
            width: 100%;
            aspect-ratio: 1;
            object-fit: cover;
            border-radius: 12px;
            background: #f9fafb;
            margin-bottom: 1rem;
        }
        
        .product-thumbnails {
            display: flex;
            gap: 0.75rem;
            overflow-x: auto;
        }
        
        .product-thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s;
        }
        
        .product-thumbnail:hover {
            border-color: #f97316;
        }
        
        .product-thumbnail.active {
            border-color: #f97316;
        }
        
        .product-info {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .product-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1f2937;
        }
        
        .product-description {
            font-size: 1.125rem;
            color: #4b5563;
            line-height: 1.8;
        }
        
        .product-meta {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            padding: 1.5rem;
            background: #f9fafb;
            border-radius: 8px;
        }
        
        .product-meta-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .product-meta-label {
            color: #6b7280;
            font-weight: 500;
        }
        
        .product-meta-value {
            color: #1f2937;
            font-weight: 600;
        }
        
        /* Variants Section */
        .variants-section {
            margin-top: 2rem;
        }
        
        .variants-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1.5rem;
        }
        
        .variants-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .variants-table thead {
            background: #f9fafb;
        }
        
        .variants-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #1f2937;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .variants-table td {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .variants-table tr:last-child td {
            border-bottom: none;
        }
        
        .variants-table tr:hover {
            background: #f9fafb;
        }
        
        .variant-attributes {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .variant-attribute {
            padding: 0.25rem 0.75rem;
            background: #f3f4f6;
            border-radius: 4px;
            font-size: 0.875rem;
            color: #4b5563;
        }
        
        .variant-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: #f97316;
        }
        
        .variant-price-label {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 400;
        }
        
        /* Variant Selection */
        .variant-selection {
            margin-top: 2rem;
        }
        
        .variant-select-group {
            margin-bottom: 1.5rem;
        }
        
        .variant-select-label {
            display: block;
            font-size: 1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        
        .variant-select {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            color: #1f2937;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .variant-select:focus {
            outline: none;
            border-color: #f97316;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
        }
        
        .selected-variant-info {
            border: 2px solid #f97316;
        }
        
        /* Related Products */
        .related-products {
            margin-top: 5rem;
            padding-top: 3rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .related-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 2rem;
        }
        
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
        }
        
        .related-card {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }
        
        .related-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .related-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        
        .related-info {
            padding: 1.5rem;
        }
        
        .related-name {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
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
        
        @media (max-width: 1024px) {
            .product-main {
                grid-template-columns: 1fr;
                gap: 3rem;
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
            
            .product-title {
                font-size: 2rem;
            }
            
            .breadcrumb {
                padding: 0.75rem 1rem;
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
                <a href="{{ route('products.index') }}">All products</a>
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

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="{{ route('home') }}">Home</a> / 
            <a href="{{ route('products.index') }}">All products</a> / 
            <span style="color: #1f2937;">{{ $product->name }}</span>
        </div>
    </div>

    <!-- Product Details -->
    <section class="product-details">
        <div class="container">
            <div class="product-main">
                <!-- Product Gallery -->
                <div class="product-gallery">
                    @php
                        $images = $product->images;
                        $primaryImage = $images->where('is_primary', true)->first() ?? $images->first();
                    @endphp
                    
                    @if($primaryImage)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($primaryImage->image_path) }}" 
                             alt="{{ $product->name }}" 
                             class="product-main-image" 
                             id="mainImage">
                    @endif
                    
                    @if($images->count() > 1)
                        <div class="product-thumbnails">
                            @foreach($images as $image)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($image->image_path) }}" 
                                     alt="{{ $product->name }} - Image {{ $loop->iteration }}"
                                     class="product-thumbnail {{ ($image->id === $primaryImage->id) ? 'active' : '' }}"
                                     onclick="changeMainImage('{{ \Illuminate\Support\Facades\Storage::url($image->image_path) }}', this)">
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Product Info -->
                <div class="product-info">
                    <h1 class="product-title">{{ $product->name }}</h1>
                    
                    @if($product->description)
                        <div class="product-description">
                            {!! nl2br(e($product->description)) !!}
                        </div>
                    @endif

                    <!-- Variant Selection -->
                    @if($product->variants->count() > 0)
                        @php
                            // Group attributes by name to create select options
                            $attributeGroups = [];
                            foreach ($product->variants as $variant) {
                                foreach ($variant->variantAttributes as $attr) {
                                    $attrName = $attr->attribute_name;
                                    $attrValue = $attr->attribute_value;
                                    if (!isset($attributeGroups[$attrName])) {
                                        $attributeGroups[$attrName] = [];
                                    }
                                    if (!in_array($attrValue, $attributeGroups[$attrName])) {
                                        $attributeGroups[$attrName][] = $attrValue;
                                    }
                                }
                            }
                            // Sort attribute values
                            foreach ($attributeGroups as $key => $values) {
                                sort($attributeGroups[$key]);
                            }
                        @endphp

                        <div class="variant-selection" id="variantSelection">
                            @foreach($attributeGroups as $attrName => $attrValues)
                                <div class="variant-select-group">
                                    <label class="variant-select-label">{{ $attrName }}</label>
                                    <select class="variant-select" data-attribute="{{ $attrName }}" onchange="updateVariant()">
                                        <option value="">Select {{ $attrName }}</option>
                                        @foreach($attrValues as $value)
                                            <option value="{{ $value }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach

                            <!-- Display Selected Variant Info -->
                            <div class="selected-variant-info" id="selectedVariantInfo" style="display: none; margin-top: 1.5rem; padding: 1.5rem; background: #f9fafb; border-radius: 8px;">
                                <div class="product-meta-item">
                                    <span class="product-meta-label">SKU:</span>
                                    <span class="product-meta-value" id="variantSku">-</span>
                                </div>
                                <div class="product-meta-item" id="variantPriceDefault" style="display: none;">
                                    <span class="product-meta-label">Price (Default):</span>
                                    <span class="product-meta-value" style="font-size: 1.25rem; color: #f97316; font-weight: 700;">-</span>
                                </div>
                                <div class="product-meta-item" id="variantPriceSeller" style="display: none;">
                                    <span class="product-meta-label">Price (Seller Shipping):</span>
                                    <span class="product-meta-value" style="font-size: 1.25rem; color: #f97316; font-weight: 700;">-</span>
                                </div>
                                <div class="product-meta-item" id="variantPriceTiktok" style="display: none;">
                                    <span class="product-meta-label">Price (TikTok Shipping):</span>
                                    <span class="product-meta-value" style="font-size: 1.25rem; color: #f97316; font-weight: 700;">-</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="product-meta">
                            <div class="product-meta-item">
                                <span class="product-meta-label">SKU:</span>
                                <span class="product-meta-value">{{ $product->sku ?? 'N/A' }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </section>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
        <section class="related-products">
            <div class="container">
                <h2 class="related-title">Related Products</h2>
                <div class="related-grid">
                    @foreach($relatedProducts as $related)
                        @php
                            $relatedImage = $related->images->first();
                        @endphp
                        <a href="{{ route('products.show', $related->slug) }}" class="related-card">
                            @if($relatedImage)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($relatedImage->image_path) }}" 
                                     alt="{{ $related->name }}" 
                                     class="related-image">
                            @endif
                            <div class="related-info">
                                <h3 class="related-name">{{ $related->name }}</h3>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

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

    <script>
        function changeMainImage(imageSrc, thumbnail) {
            document.getElementById('mainImage').src = imageSrc;
            
            // Update active thumbnail
            document.querySelectorAll('.product-thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            thumbnail.classList.add('active');
        }

        // Variant data from server
        const variantsData = @json($variantsData);

        const currencySymbols = {
            'GBP': '£',
            'USD': '$',
            'EUR': '€',
        };

        function updateVariant() {
            const selects = document.querySelectorAll('.variant-select');
            const selectedAttributes = {};
            
            // Get all selected values
            selects.forEach(select => {
                const attrName = select.dataset.attribute;
                const attrValue = select.value;
                if (attrValue) {
                    selectedAttributes[attrName] = attrValue;
                }
            });
            
            // Find matching variant
            const matchingVariant = variantsData.find(variant => {
                const variantAttrs = variant.attributes;
                if (Object.keys(selectedAttributes).length !== Object.keys(variantAttrs).length) {
                    return false;
                }
                return Object.keys(selectedAttributes).every(attrName => {
                    return variantAttrs[attrName] === selectedAttributes[attrName];
                });
            });
            
            const variantInfo = document.getElementById('selectedVariantInfo');
            const variantSku = document.getElementById('variantSku');
            const priceDefault = document.getElementById('variantPriceDefault');
            const priceSeller = document.getElementById('variantPriceSeller');
            const priceTiktok = document.getElementById('variantPriceTiktok');
            
            if (matchingVariant) {
                // Show variant info
                variantInfo.style.display = 'block';
                variantSku.textContent = matchingVariant.sku;
                
                const symbol = currencySymbols[matchingVariant.currency] || '$';
                const marketText = matchingVariant.market ? ` (${matchingVariant.market})` : '';
                
                // Display default price
                if (matchingVariant.prices && matchingVariant.prices.default) {
                    const priceText = `${symbol}${parseFloat(matchingVariant.prices.default).toFixed(2)}${marketText}`;
                    priceDefault.querySelector('.product-meta-value').textContent = priceText;
                    priceDefault.style.display = 'flex';
                } else {
                    priceDefault.style.display = 'none';
                }
                
                // Display seller price
                if (matchingVariant.prices && matchingVariant.prices.seller) {
                    const priceText = `${symbol}${parseFloat(matchingVariant.prices.seller).toFixed(2)}${marketText}`;
                    priceSeller.querySelector('.product-meta-value').textContent = priceText;
                    priceSeller.style.display = 'flex';
                } else {
                    priceSeller.style.display = 'none';
                }
                
                // Display tiktok price
                if (matchingVariant.prices && matchingVariant.prices.tiktok) {
                    const priceText = `${symbol}${parseFloat(matchingVariant.prices.tiktok).toFixed(2)}${marketText}`;
                    priceTiktok.querySelector('.product-meta-value').textContent = priceText;
                    priceTiktok.style.display = 'flex';
                } else {
                    priceTiktok.style.display = 'none';
                }
                
                // If no prices at all
                if (!matchingVariant.prices || (!matchingVariant.prices.default && !matchingVariant.prices.seller && !matchingVariant.prices.tiktok)) {
                    priceDefault.style.display = 'flex';
                    priceDefault.querySelector('.product-meta-value').textContent = 'Contact for price';
                    priceDefault.querySelector('.product-meta-value').style.color = '#6b7280';
                }
            } else {
                // Check if all attributes are selected but no match
                const allSelected = Array.from(selects).every(select => select.value !== '');
                if (allSelected) {
                    variantInfo.style.display = 'block';
                    variantSku.textContent = 'Not available';
                    priceDefault.style.display = 'flex';
                    priceDefault.querySelector('.product-meta-value').textContent = 'Variant not found';
                    priceDefault.querySelector('.product-meta-value').style.color = '#ef4444';
                    priceSeller.style.display = 'none';
                    priceTiktok.style.display = 'none';
                } else {
                    variantInfo.style.display = 'none';
                }
            }
        }
    </script>
</body>
</html>

