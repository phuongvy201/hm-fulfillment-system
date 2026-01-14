@extends('layouts.app')

@section('title', config('app.name', 'HM Fulfillment System') . ' - Modern SaaS Fulfillment')
@section('description', 'Start selling your unique print-on-demand products. Connect your store, automate production, and ship worldwide with zero inventory risk.')

@section('content')
@php
    $heroImagePath = optional($featuredProducts->first()?->images->first())->image_path ?? null;
    $heroImageUrl = $heroImagePath
        ? \Illuminate\Support\Facades\Storage::url($heroImagePath)
        : 'https://lh3.googleusercontent.com/aida-public/AB6AXuCWTYdCxKgCCqiCGFVao-HFdJc0dR6gGNVz3RliC16TcRPTP9o84qan3LUR1J_uagg3GGWTnzj_U8vTzDZn7Cnxmnzjenn8HVd_H1A9n6lsAF7O9tVhE9PtRnRM028-iIitxEpYB9eO0sGid1cnbhosoZA9ubWU8_fNyxhnUHYjnlAIFAS9ZJbC9wBSQz-3Sc53sBab_rhgiP57lvwh3OTKBWe3UsGFGpCm1P2RPkJXHTimXU3a8946qLs9y1NbgLsmOk6J7DNIuts';
@endphp
    <section class="px-4 py-12 lg:py-24">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="flex flex-col gap-8 order-2 lg:order-1">
                <div class="flex flex-col gap-4">
                    <span class="inline-block px-3 py-1 bg-primary/10 text-primary text-xs font-bold tracking-wider uppercase rounded-full w-fit">Global POD Platform</span>
                    <h1 class="text-[#181511] dark:text-white text-5xl lg:text-7xl font-black leading-[1.1] tracking-tight">
                        Start Selling Your <span class="text-primary">Unique</span> Print-On-Demand Products
                    </h1>
                    <p class="text-[#181511]/70 dark:text-white/70 text-lg lg:text-xl max-w-xl">
                        The ultimate SaaS fulfillment engine for global sellers. Seamlessly connect your store, automate production, and ship worldwide with zero inventory risk.
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row gap-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="bg-primary text-white h-14 px-10 rounded-xl text-lg font-bold shadow-xl shadow-primary/30 hover:scale-105 transition-transform flex items-center justify-center">
                            Start Shipping Free
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="bg-primary text-white h-14 px-10 rounded-xl text-lg font-bold shadow-xl shadow-primary/30 hover:scale-105 transition-transform flex items-center justify-center">
                            Start Shipping Free
                        </a>
                    @endauth
                    <a href="#contact" class="bg-white dark:bg-white/5 border border-[#e6e1db] dark:border-white/10 h-14 px-8 rounded-xl text-lg font-bold hover:bg-background-light dark:hover:bg-white/10 transition-colors flex items-center justify-center">
                        Book a Demo
                    </a>
                </div>
                <div class="flex items-center gap-4 text-sm text-[#181511]/60 dark:text-white/60">
                    <span class="flex -space-x-2">
                        <img alt="User" class="w-8 h-8 rounded-full border-2 border-white dark:border-background-dark" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBcShwYhX1lzLK-qDCr-CPdBE2_HFFPkowx4EMnoUMkjOSnK3D04KmHswvEUEEiFtelS85mr4Gugo6vCn58v5ZSAy6fqfZqv-PU7K9nGE1qmVhd38jIgcCBmOcQtXqDXbbnFPIyAe3ELw9WBsxERItl3fQLQBuuVz6xK_mFNcWN6z0JjLmRiBm6veEX7oaU1vSkh4yWkI8JGqiedDuBJ26de4P0ojDuDS4nvZzXkpnZujLxz0AAPCGFNUVdcySpOqH8F9a0SMSfUog">
                        <img alt="User" class="w-8 h-8 rounded-full border-2 border-white dark:border-background-dark" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBz3CQz27VX2AQX-4hnjLsn_j-beAKNYtlNguvPvmT20seCEI1x5Z1iat1WbCqPGwTfk-QPzyiyFvY-ZJ36pGDWX8liNR2_VyY8FvXICyq_t8FTQmvYwuCfc0u752DIREu--kS7-vEc0OkRTO2KVV2eZsM2599suWMDx2302D6D4_iT8RS7fq2iIkYpIkY7vBSXH2k9FI7v56BmTcop-tVIo62TNDkZs_JPw3paoG283AchMRdqt-jupK0d8kQn9SVpPDwMtGflHgU">
                        <img alt="User" class="w-8 h-8 rounded-full border-2 border-white dark:border-background-dark" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBjU4RTQz4QljTPabFwQwkhrEFHLEeBz_onSxK9CGHFm5JuL27a7tuz10M6zqF13LNoQlvaDwW9V_x5j3_DksZBDaouK0vFJXLMkhTR04oUl1n1fp0eRDCujdUPJGozfqyC4hJDjq4rslUrDjUvr66ZTRllk326yvGUAHQMoN1on3hwgLJ8z6jNOIJla0ySuunbjApXBHL8WMyf2RZwosU_KtAFzovlu0yli_opnh9stsWYLUuhKNtoxQb0D5MK_MlT_GPIBg5_XLQ">
                    </span>
                    <span>Joined by 10k+ active sellers this month</span>
                </div>
            </div>
            <div class="relative order-1 lg:order-2">
                <div class="w-full aspect-square bg-gradient-to-br from-primary/20 to-primary/5 rounded-3xl overflow-hidden shadow-2xl relative">
                    <div class="absolute inset-0 bg-center bg-no-repeat bg-cover opacity-90 mix-blend-multiply" style='background-image: url("{{ $heroImageUrl }}");'></div>
                    <div class="absolute bottom-6 right-6 bg-white dark:bg-background-dark p-4 rounded-2xl shadow-xl flex items-center gap-4">
                        <div class="bg-green-100 dark:bg-green-900/30 p-2 rounded-lg text-green-600">
                            <span class="material-symbols-outlined">local_shipping</span>
                        </div>
                        <div>
                            <p class="text-xs font-bold opacity-60">Latest Order</p>
                            <p class="text-sm font-black">Delivered to Tokyo, JP</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="px-4 py-8" id="pricing">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-white/5 p-8 rounded-2xl border border-[#e6e1db] dark:border-white/10 flex flex-col gap-2">
                <p class="text-[#181511]/60 dark:text-white/60 text-base font-medium">Sản phẩm đang bán</p>
                <p class="text-primary text-4xl font-black">{{ number_format($productsCount) }}</p>
                <div class="w-12 h-1 bg-primary rounded-full mt-2"></div>
            </div>
            <div class="bg-white dark:bg-white/5 p-8 rounded-2xl border border-[#e6e1db] dark:border-white/10 flex flex-col gap-2">
                <p class="text-[#181511]/60 dark:text-white/60 text-base font-medium">Đối tác toàn cầu</p>
                <p class="text-primary text-4xl font-black">200+</p>
                <div class="w-12 h-1 bg-primary rounded-full mt-2"></div>
            </div>
            <div class="bg-white dark:bg-white/5 p-8 rounded-2xl border border-[#e6e1db] dark:border-white/10 flex flex-col gap-2">
                <p class="text-[#181511]/60 dark:text-white/60 text-base font-medium">Tỉ lệ giao đúng hẹn</p>
                <p class="text-primary text-4xl font-black">99.9%</p>
                <div class="w-12 h-1 bg-primary rounded-full mt-2"></div>
            </div>
        </div>
    </section>

    <section class="px-4 py-16 text-center" id="integrations">
        <p class="text-sm font-bold uppercase tracking-widest opacity-50 mb-8">Integrate with your favorite platforms</p>
        <div class="flex flex-wrap justify-center items-center gap-12 grayscale opacity-60 hover:grayscale-0 transition-all">
            <div class="flex items-center gap-2 font-black text-2xl">SHOPIFY</div>
            <div class="flex items-center gap-2 font-black text-2xl">ETSY</div>
            <div class="flex items-center gap-2 font-black text-2xl">WIX</div>
            <div class="flex items-center gap-2 font-black text-2xl">TIKTOK</div>
            <div class="flex items-center gap-2 font-black text-2xl">WOO</div>
        </div>
    </section>

    <section class="px-4 py-16" id="products">
        <div class="flex justify-between items-end mb-10">
            <div class="flex flex-col gap-2">
                <h2 class="text-3xl font-black">Explore Our Catalog</h2>
                <p class="opacity-60">High-quality blanks ready for your designs</p>
            </div>
            <a href="{{ route('products.index') }}" class="text-primary font-bold flex items-center gap-1 hover:gap-2 transition-all">
                View Full Catalog <span class="material-symbols-outlined">arrow_right_alt</span>
            </a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
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

                    $currencySymbol = match($currency) {
                        'GBP' => '£',
                        'USD' => '$',
                        'EUR' => '€',
                        default => '$'
                    };
                @endphp
                <a href="{{ route('products.show', $product->slug) }}" class="group cursor-pointer" style="text-decoration: none; color: inherit;">
                    <div class="relative aspect-[3/4] bg-white dark:bg-white/5 rounded-2xl overflow-hidden mb-4 border border-[#e6e1db] dark:border-white/10">
                        @if($primaryImage)
                            <div class="absolute inset-0 bg-center bg-cover transition-transform group-hover:scale-105" style='background-image: url("{{ \Illuminate\Support\Facades\Storage::url($primaryImage->image_path) }}");'></div>
                        @else
                            <div class="absolute inset-0 bg-gradient-to-br from-background-light to-white dark:from-background-dark dark:to-[#2f2417] flex items-center justify-center text-[#6b7280]">
                                <span class="material-symbols-outlined text-4xl">photo</span>
                            </div>
                        @endif
                        @if($product->status === 'active')
                            <div class="absolute top-4 left-4 bg-primary text-white text-[10px] font-black px-2 py-1 rounded-full">ACTIVE</div>
                        @endif
                    </div>
                    <div class="flex flex-col gap-1">
                        <h3 class="font-bold text-lg">{{ $product->name }}</h3>
                        <div class="flex items-center justify-between">
                            <p class="opacity-60 text-sm">{{ $product->workshop->name ?? 'POD Workshop' }}</p>
                            @if($price)
                                <p class="text-primary font-black">{{ $currencySymbol }}{{ number_format($price, 2) }} {{ $marketCode }}</p>
                            @else
                                <p class="text-primary font-black text-sm">Contact for price</p>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-4 text-center text-[#6b7280] py-12">
                    Hiện chưa có sản phẩm để hiển thị.
                </div>
            @endforelse
        </div>
    </section>

    <section class="px-4 py-20 bg-[#181511] rounded-[2rem] text-white overflow-hidden relative" id="resources">
        <div class="absolute top-0 right-0 w-96 h-96 bg-primary/20 blur-[100px] rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-20">
            <div class="flex flex-col gap-6">
                <h2 class="text-4xl lg:text-5xl font-black">Built for Reliability at Scale</h2>
                <p class="text-lg opacity-70">We handle the complex logistics so you can focus on building your brand. From first stitch to doorstep delivery, we've got you covered.</p>
                <div class="flex flex-col gap-8 mt-4">
                    <div class="flex gap-6">
                        <div class="w-12 h-12 shrink-0 bg-primary/20 flex items-center justify-center rounded-xl">
                            <span class="material-symbols-outlined text-primary">verified</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <h4 class="text-xl font-bold">World-class Quality Control</h4>
                            <p class="opacity-60">Every single product undergoes a 3-step inspection process before leaving our facilities.</p>
                        </div>
                    </div>
                    <div class="flex gap-6">
                        <div class="w-12 h-12 shrink-0 bg-primary/20 flex items-center justify-center rounded-xl">
                            <span class="material-symbols-outlined text-primary">public</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <h4 class="text-xl font-bold">Fast Global Shipping</h4>
                            <p class="opacity-60">With over 200 partner hubs, we produce and ship orders locally to reduce transit times and carbon footprint.</p>
                        </div>
                    </div>
                    <div class="flex gap-6">
                        <div class="w-12 h-12 shrink-0 bg-primary/20 flex items-center justify-center rounded-xl">
                            <span class="material-symbols-outlined text-primary">monitoring</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <h4 class="text-xl font-bold">Real-time Analytics</h4>
                            <p class="opacity-60">Track your profit margins, production status, and delivery times from a single intuitive dashboard.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex flex-col justify-center items-center lg:items-end">
                <div class="bg-white/5 p-8 rounded-3xl border border-white/10 max-w-sm backdrop-blur-sm">
                    <div class="flex gap-2 text-primary mb-4">
                        <span class="material-symbols-outlined fill-1">star</span>
                        <span class="material-symbols-outlined fill-1">star</span>
                        <span class="material-symbols-outlined fill-1">star</span>
                        <span class="material-symbols-outlined fill-1">star</span>
                        <span class="material-symbols-outlined fill-1">star</span>
                    </div>
                    <p class="text-xl italic font-medium mb-6 leading-relaxed">"Switching to HMFULFILL was the best decision for my Etsy shop. Production is faster, and my customers love the premium quality."</p>
                    <div class="flex items-center gap-4">
                        <img alt="Sarah Jenkins" class="w-12 h-12 rounded-full" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCwEmfRuK6GZMcLRI3FDNa_TmZh7l0_VPqVbzQ8gwYeiDu_u_74-7xny5HnwhfzWIGkeWnKznFNTNm6nD08Jzdi6oyDYMMZ2J7DyncKAUslGMj0yvf0bx3KCx-AUPgLhPtwIgFq3b6ML-Rjgef2m4v05HB0ewsJN6Ph0VSChO1o7bDlZW5ssNhZneAwVFjXaPUioyPJml7ou2JWgqgyBTVDRa9u4OLDJOf-x1PuyemATkY4GpVldQ9_csuyIHgPd8GcalVTt6_QPqA">
                        <div>
                            <p class="font-bold">Sarah Jenkins</p>
                            <p class="text-sm opacity-50">Top 1% Etsy Seller</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="px-4 py-24 text-center">
        <div class="max-w-3xl mx-auto flex flex-col gap-8">
            <h2 class="text-4xl lg:text-5xl font-black">Ready to launch your empire?</h2>
            <p class="text-xl opacity-60">Join thousands of entrepreneurs who trust HMFULFILL to power their global commerce stores.</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="bg-primary text-white h-16 px-12 rounded-2xl text-xl font-black shadow-2xl shadow-primary/40 hover:scale-105 transition-all flex items-center justify-center">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="bg-primary text-white h-16 px-12 rounded-2xl text-xl font-black shadow-2xl shadow-primary/40 hover:scale-105 transition-all flex items-center justify-center">
                        Create Your Account
                    </a>
                @endauth
                <a href="#pricing" class="bg-transparent border-2 border-[#181511] dark:border-white h-16 px-10 rounded-2xl text-xl font-black hover:bg-[#181511] hover:text-white dark:hover:bg-white dark:hover:text-[#181511] transition-all flex items-center justify-center">
                    View Pricing
                </a>
            </div>
            <p class="text-sm opacity-50">Free to start. Pay only when you sell.</p>
        </div>
    </section>
</main>
@endsection

