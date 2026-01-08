<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'HM Fulfillment System') }} - Print-On-Demand Fulfillment Services</title>
    <meta name="description" content="Start selling your unique print-on-demand products. Our advanced printing and fulfillment system helps you create and sell POD products, delivering them directly to customers across the globe.">
    
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
        
        /* Hero Section */
        .hero {
            padding: 4rem 2rem 6rem;
            background: #ffffff;
        }
        
        .hero-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .hero-text {
            padding: 2rem 0;
        }
        
        .hero-text h1 {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            color: #1f2937;
            font-family: Georgia, 'Times New Roman', serif;
        }
        
        .hero-text p {
            font-size: 1.125rem;
            color: #4b5563;
            margin-bottom: 2.5rem;
            line-height: 1.7;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .btn-get-started {
            background: #e0f2fe;
            color: #0284c7;
            border: 1px solid #0284c7;
            padding: 1rem 2rem;
            font-size: 1rem;
        }
        
        .btn-get-started:hover {
            background: #0284c7;
            color: white;
        }
        
        .btn-get-started::after {
            content: '→';
            font-size: 1.25rem;
            margin-left: 0.5rem;
        }
        
        .hero-visuals {
            position: relative;
            height: 600px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .webdecor-container {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        
        .webdecor-wrapper {
            display: flex;
            flex-direction: column;
            animation: scrollVertical 90s linear infinite;
            will-change: transform;
        }
        
        .webdecor-wrapper img {
            width: 100%;
            height: auto;
            display: block;
            flex-shrink: 0;
        }
        
        @keyframes scrollVertical {
            0% {
                transform: translateY(0);
            }
            100% {
                transform: translateY(-50%);
            }
        }
        
        /* Ultimate Platform Section */
        .ultimate-platform {
            padding: 6rem 2rem;
            background: #ffffff;
            text-align: center;
        }
        
        .platform-title {
            font-size: 3rem;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 2rem;
            font-family: Georgia, 'Times New Roman', serif;
            line-height: 1.2;
        }
        
        .platform-description {
            font-size: 1.125rem;
            color: #4b5563;
            line-height: 1.8;
            max-width: 900px;
            margin: 0 auto 4rem;
        }
        
        /* Product Carousel - New Design */
        .product-carousel {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .carousel-wrapper {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            background: #f9fafb;
        }
        
        .carousel-slides {
            display: flex;
            transition: transform 0.6s ease-in-out;
        }
        
        .carousel-slide {
            min-width: 100%;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .carousel-image-wrapper {
            width: 100%;
            padding: 2rem;
        }
        
        .carousel-image {
            width: 100%;
            height: 500px;
            object-fit: contain;
            border-radius: 12px;
            background: white;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .carousel-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 50px;
            height: 50px;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            z-index: 10;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .carousel-nav:hover {
            background: #f97316;
            border-color: #f97316;
            color: white;
            transform: translateY(-50%) scale(1.1);
        }
        
        .carousel-nav-prev {
            left: 20px;
        }
        
        .carousel-nav-next {
            right: 20px;
        }
        
        .carousel-dots {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 2rem;
        }
        
        .carousel-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #d1d5db;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .carousel-dot.active {
            background: #f97316;
            width: 30px;
            border-radius: 5px;
        }
        
        @media (max-width: 768px) {
            .carousel-image {
                height: 350px;
            }
            
            .carousel-nav {
                width: 40px;
                height: 40px;
            }
            
            .carousel-nav-prev {
                left: 10px;
            }
            
            .carousel-nav-next {
                right: 10px;
            }
        }
        
        /* Statistics & POD Solution Section */
        .stats-pod {
            padding: 6rem 2rem;
            background: #f9fafb;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 3rem;
            margin-bottom: 5rem;
        }
        
        .stat-card {
            text-align: center;
        }
        
        .stat-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 2rem;
            color: #0284c7;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .stat-icon svg {
            width: 100%;
            height: 100%;
        }
        
        .stat-text {
            font-size: 1rem;
            color: #4b5563;
            line-height: 1.7;
        }
        
        .stat-text strong {
            color: #1f2937;
            font-weight: 700;
        }
        
        .pod-solution {
            text-align: center;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .pod-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1.5rem;
        }
        
        .pod-description {
            font-size: 1.125rem;
            color: #4b5563;
            line-height: 1.8;
        }
        
        /* Products Grid Section */
        .products-grid-section {
            padding: 6rem 2rem;
            background: #ffffff;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }
        
        .product-card {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
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
        
        /* Platform Integration Section */
        .platform-integration {
            padding: 6rem 2rem;
            background: #f9fafb;
        }
        
        .integration-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 3rem;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .platforms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            max-width: 1000px;
            margin: 0 auto 5rem;
        }
        
        .platform-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            transition: all 0.3s ease;
        }
        
        .platform-card:hover {
            border-color: #f97316;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.1);
            transform: translateY(-2px);
        }
        
        .platform-logo {
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .platform-logo img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }
        
        .platform-name {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1f2937;
        }
        
        .platform-name.shopify {
            color: #96bf48;
        }
        
        .platform-name.etsy {
            color: #f16521;
        }
        
        .platform-name.woocommerce {
            color: #96588a;
        }
        
        .platform-name.tiktok {
            color: #000000;
        }
        
        .best-choice-section {
            text-align: center;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .best-choice-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 2rem;
        }
        
        .best-choice-title .highlight-orange {
            color: #f97316;
        }
        
        .best-choice-title .highlight-teal {
            color: #14b8a6;
        }
        
        .best-choice-description {
            font-size: 1.125rem;
            color: #4b5563;
            line-height: 1.8;
        }
        
        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 1.5rem;
            }
            
            .platforms-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
            }
            
            .integration-title,
            .best-choice-title {
                font-size: 2rem;
            }
        }
        
        /* Features Section */
        .features {
            padding: 6rem 2rem;
            background: #f9fafb;
        }
        
        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1f2937;
        }
        
        .section-subtitle {
            text-align: center;
            font-size: 1.125rem;
            color: #6b7280;
            margin-bottom: 4rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .feature-card {
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }
        
        .feature-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            font-size: 1.75rem;
        }
        
        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1f2937;
        }
        
        .feature-card p {
            color: #6b7280;
            line-height: 1.7;
        }
        
        /* Products Section */
        .products {
            padding: 6rem 2rem;
            background: white;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }
        
        .product-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }
        
        .product-image {
            width: 100%;
            height: 240px;
            object-fit: cover;
            background: #f3f4f6;
        }
        
        .product-info {
            padding: 1.5rem;
        }
        
        .product-info h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #1f2937;
        }
        
        .product-info p {
            color: #6b7280;
            font-size: 0.9375rem;
            margin-bottom: 1rem;
            line-height: 1.6;
        }
        
        .product-workshop {
            display: inline-block;
            padding: 0.375rem 0.875rem;
            background: #fef3c7;
            color: #92400e;
            border-radius: 8px;
            font-size: 0.8125rem;
            font-weight: 600;
        }
        
        /* CTA Section */
        .cta {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
            padding: 5rem 2rem;
            text-align: center;
        }
        
        .cta h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .cta p {
            font-size: 1.125rem;
            margin-bottom: 2rem;
            opacity: 0.95;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Footer */
        footer {
            background: #1f2937;
            color: white;
            padding: 4rem 2rem 2rem;
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
            .hero-content {
                grid-template-columns: 1fr;
                gap: 3rem;
            }
            
            .hero-visuals {
                height: 400px;
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
            
            .hero-text h1 {
                font-size: 2.5rem;
            }
            
            .hero-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .hero-visuals {
                height: 300px;
            }
            
            .platform-title {
                font-size: 2rem;
            }
            
            .carousel-slide {
                min-width: 100%;
            }
            
            .pod-title {
                font-size: 2rem;
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
                    <img src="{{ \Illuminate\Support\Facades\Storage::url('images/logo HM-02.png') }}" alt="HM FULFILL" class="logo-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="logo-fallback" style="display: none; align-items: center; gap: 0.5rem;">
                        <div class="logo-icon">HM</div>
                        <span class="logo-text">FULFILL</span>
                    </div>
                </div>
                
            </div>
            <div class="nav-menu">
                <a href="#how-it-works">How it works</a>
                <a href="{{ route('products.index') }}">All products</a>
                <a href="#sku">SKU</a>
                <a href="#blogs">Blogs</a>
                <a href="#contact">Contact Us</a>
                <a href="#help">Help Center</a>
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-text">
                <h1>Start Selling Your Unique Print-On-Demand Products</h1>
                <p>Our advanced printing and fulfillment system helps you create and sell POD products, delivering them directly to customers across the globe.</p>
                <div class="hero-buttons">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-get-started">Go to Dashboard</a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn-get-started">Get started</a>
                    @endauth
                </div>
            </div>
            <div class="hero-visuals">
                <div class="webdecor-container">
                    <div class="webdecor-wrapper">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url('images/webdecor.png') }}" alt="Web Decor" class="webdecor-image">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url('images/webdecor.png') }}" alt="Web Decor" class="webdecor-image">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Ultimate Platform Section -->
    <section class="ultimate-platform">
        <div class="container">
            <h2 class="platform-title">The ultimate commerce platform for print-on-demand</h2>
            <p class="platform-description">
                Sell custom products online and in person with access to both global and local markets. Flexibly offer direct and wholesale printing services to meet diverse customer needs. Our system helps you manage orders effortlessly, optimizing the process from design to delivery. Seamlessly sell across all devices, expanding your brand and boosting sales effectively.
            </p>
            
            <!-- Product Carousel -->
            <div class="product-carousel">
                <div class="carousel-wrapper">
                    <button class="carousel-nav carousel-nav-prev" onclick="prevSlide()" aria-label="Previous">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    
                    <div class="carousel-slides" id="carouselSlides">
                        @php
                            $carouselImages = [
                                'images/bank-phrom-Tzm3Oyu_6sk-unsplash.jpg',
                                'images/james-inigo-beK1tm4ufpU-unsplash.jpg',
                                'images/jonny-caspari-KuudDjBHIlA-unsplash.jpg',
                                'images/elena-mozhvilo-lfeSPLBxcKU-unsplash.jpg',
                            ];
                        @endphp
                        @foreach($carouselImages as $index => $imagePath)
                        <div class="carousel-slide">
                            <div class="carousel-image-wrapper">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($imagePath) }}" alt="POD Product {{ $index + 1 }}" class="carousel-image" loading="lazy">
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <button class="carousel-nav carousel-nav-next" onclick="nextSlide()" aria-label="Next">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="carousel-dots" id="carouselDots">
                    @foreach($carouselImages as $index => $imagePath)
                        <span class="carousel-dot {{ $index === 0 ? 'active' : '' }}" onclick="goToSlide({{ $index }})"></span>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- Products Grid Section -->
    <section class="products-grid-section" id="products">
        <div class="container">
            <h2 class="section-title">Our Products</h2>
            <p class="section-subtitle">Explore our wide range of print-on-demand products</p>
            
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
                    <a href="{{ route('products.show', $product->slug) }}" class="product-card" style="text-decoration: none; color: inherit;">
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
                    <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: #6b7280;">
                        <p>No products available at the moment.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Statistics & POD Solution Section -->
    <section class="stats-pod">
        <div class="container">
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                        </svg>
                    </div>
                    <p class="stat-text">
                        Since 2013, we have successfully delivered more than <strong>102M+</strong> products to customers around the world, ensuring quality and reliability in every order.
                    </p>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 3h12l4 6-4 6H6l-4-6z"></path>
                            <path d="M6 9h12"></path>
                        </svg>
                    </div>
                    <p class="stat-text">
                        Our commitment to quality ensures that <strong>99%</strong> of customers are satisfied with the products they receive.
                    </p>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                            <path d="M8 6h8"></path>
                            <path d="M8 10h8"></path>
                            <path d="M8 14h8"></path>
                            <path d="M8 18h8"></path>
                        </svg>
                    </div>
                    <p class="stat-text">
                        Every month, we successfully fulfill over <strong>1M+</strong> orders, delivering excellence at scale.
                    </p>
                </div>
            </div>
            
            <!-- POD Solution Description -->
            <div class="pod-solution">
                <h2 class="pod-title">The perfect POD solution for your business</h2>
                <p class="pod-description">
                    Turn your creative ideas into real products without worrying about production or shipping. With the POD platform, you can easily customize over 100 products such as T-shirts, mugs, hoodies, posters, and more. Orders are processed automatically, from printing to delivery to customers worldwide. All you need to do is focus on design and marketing—we'll take care of the rest!
                </p>
            </div>
        </div>
    </section>

    <!-- Platform Integration Section -->
    <section class="platform-integration">
        <div class="container">
            <h2 class="integration-title">Seamlessly connect HMFULFILL with your favorite platform or marketplace.</h2>
            
            <div class="platforms-grid">
                <div class="platform-card">
                    <div class="platform-logo">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url('images/shopify.67e3df8f.png') }}" alt="Shopify" width="60" height="60">
                    </div>
                    <div class="platform-name shopify">shopify</div>
                </div>
                
                <div class="platform-card">
                    <div class="platform-logo">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url('images/etsy.431beca9.png') }}" alt="Etsy" width="60" height="60">
                    </div>
                    <div class="platform-name etsy">Etsy</div>
                </div>
                
                <div class="platform-card">
                    <div class="platform-logo">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url('images/wooCommerce.74a1ee4a.png') }}" alt="WooCommerce" width="60" height="60">
                    </div>
                    <div class="platform-name woocommerce">WooCommerce</div>
                </div>
                
                <div class="platform-card">
                    <div class="platform-logo">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url('images/download.png') }}" alt="TikTok" width="60" height="60">
                    </div>
                    <div class="platform-name tiktok">TikTok</div>
                </div>
            </div>
            
            <div class="best-choice-section">
                <h2 class="best-choice-title">
                    What Makes <span class="highlight-orange">HMFULFILL</span> the <span class="highlight-teal">Best Choice?</span>
                </h2>
                <p class="best-choice-description">
                    At HMFULFILL, we believe that everyone deserves access to a diverse range of printing solutions that are affordable yet high-quality. We are committed to delivering exceptional printing services that meet personal, business, and creative needs, allowing customers to explore the world of printing without worrying about cost or quality.
                </p>
            </div>
        </div>
    </section>

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
                    <a href="#how-it-works">How it works</a>
                    <a href="{{ route('products.index') }}">All products</a>
                    <a href="#sku">SKU Management</a>
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
        let currentSlide = 0;
        let autoPlayInterval;
        const slides = document.querySelectorAll('.carousel-slide');
        const dots = document.querySelectorAll('.carousel-dot');
        const totalSlides = slides.length;

        function showSlide(index) {
            if (index < 0) {
                currentSlide = totalSlides - 1;
            } else if (index >= totalSlides) {
                currentSlide = 0;
            } else {
                currentSlide = index;
            }

            const slidesContainer = document.getElementById('carouselSlides');
            slidesContainer.style.transform = `translateX(-${currentSlide * 100}%)`;

            // Update dots
            dots.forEach((dot, index) => {
                if (index === currentSlide) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }

        function nextSlide() {
            showSlide(currentSlide + 1);
        }

        function prevSlide() {
            showSlide(currentSlide - 1);
        }

        function goToSlide(index) {
            showSlide(index);
        }

        function startAutoPlay() {
            autoPlayInterval = setInterval(() => {
                nextSlide();
            }, 5000);
        }

        function stopAutoPlay() {
            clearInterval(autoPlayInterval);
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            if (totalSlides > 0) {
                showSlide(0);
                startAutoPlay();

                // Pause on hover
                const carousel = document.querySelector('.product-carousel');
                if (carousel) {
                    carousel.addEventListener('mouseenter', stopAutoPlay);
                    carousel.addEventListener('mouseleave', startAutoPlay);
                }
            }
        });
    </script>
</body>
</html>
