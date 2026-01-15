@extends('layouts.app')

@section('title', $product->name . ' - ' . config('app.name', 'HM Fulfillment System'))
@section('description', \Illuminate\Support\Str::limit($product->description ?? $product->name, 160))
@section('html-class', 'light')
@section('body-class', 'bg-[#F8F9FA] dark:bg-background-dark text-neutral-900 dark:text-neutral-100 antialiased')

@php
    $images = $product->images;
    $primaryImage = $images->where('is_primary', true)->first() ?? $images->first();
    $currencySymbols = ['GBP' => '£', 'USD' => '$', 'EUR' => '€'];
    $firstVariantSummary = $variantsData->first();
    $summarySymbol = $firstVariantSummary
        ? ($currencySymbols[strtoupper($firstVariantSummary['currency'] ?? '')] ?? '$')
        : '$';
    $defaultPrices = $firstVariantSummary['prices'] ?? [];
    $marketText = $firstVariantSummary && $firstVariantSummary['market'] ? $firstVariantSummary['market'] : 'Market';
    $woodPrice = $defaultPrices['wood'] ?? null;
@endphp

@section('content')
<main class="flex-1">
    <section class="max-w-[1400px] mx-auto px-6 lg:px-12 py-8 lg:py-12">
        <nav class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] font-bold uppercase tracking-widest text-neutral-400 mb-6">
            <a class="hover:text-primary transition-colors" href="{{ route('home') }}">Home</a>
            <span class="material-symbols-outlined text-[10px]">chevron_right</span>
            <a class="hover:text-primary transition-colors" href="{{ route('products.index') }}">Catalog</a>
            <span class="material-symbols-outlined text-[10px]">chevron_right</span>
            <span class="text-neutral-900 dark:text-white">{{ $product->name }}</span>
            @if($product->sku)
                <span class="mx-2 text-neutral-300">|</span>
                <span class="bg-neutral-200 dark:bg-neutral-800 px-2 py-0.5 rounded text-[10px] text-neutral-500">SKU: {{ $product->sku }}</span>
            @endif
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-16">
            <div class="lg:col-span-5 space-y-6">
                <div class="aspect-[4/5] bg-white dark:bg-neutral-900 rounded-3xl overflow-hidden relative border border-neutral-200 dark:border-neutral-800 group shadow-sm">
                    @if($primaryImage)
                        <img id="mainImage" alt="{{ $product->name }}" class="w-full h-full object-cover"
                             src="{{ \Illuminate\Support\Facades\Storage::url($primaryImage->image_path) }}">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-neutral-400">
                            <span class="material-symbols-outlined text-5xl">photo</span>
                        </div>
                    @endif
                    <div class="absolute top-6 left-6">
                        <span class="bg-black/80 backdrop-blur-md text-white text-[11px] font-black px-4 py-2 rounded-full flex items-center gap-2 uppercase tracking-widest border border-white/20">
                            <span class="material-symbols-outlined text-sm text-primary">location_on</span>
                            {{ $product->workshop->market->name ?? 'Fulfillment' }}
                        </span>
                    </div>
                </div>
                @if($images->count() > 1)
                    <div class="grid grid-cols-4 gap-4">
                        @foreach($images as $index => $image)
                            @if($index < 3)
                                <button class="aspect-square rounded-2xl border {{ $image->id === ($primaryImage->id ?? null) ? 'border-2 border-primary shadow-md' : 'border border-neutral-200 dark:border-neutral-800 opacity-70 hover:opacity-100' }} overflow-hidden cursor-pointer transition-opacity"
                                        onclick="changeMainImage('{{ \Illuminate\Support\Facades\Storage::url($image->image_path) }}', this)">
                                    <img class="w-full h-full object-cover" src="{{ \Illuminate\Support\Facades\Storage::url($image->image_path) }}" alt="{{ $product->name }}">
                                </button>
                            @elseif($index === 3)
                                <div class="aspect-square rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 flex items-center justify-center text-neutral-400 hover:text-primary transition-colors cursor-pointer">
                                    <span class="material-symbols-outlined text-3xl">play_circle</span>
                                </div>
                            @endif
                        @endforeach
                        @if($images->count() <= 3)
                            @for($i = $images->count(); $i < 4; $i++)
                                <div class="aspect-square rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 flex items-center justify-center text-neutral-400 hover:text-primary transition-colors cursor-pointer">
                                    <span class="material-symbols-outlined text-3xl">play_circle</span>
                                </div>
                            @endfor
                        @endif
                    </div>
                @endif
            </div>

            <div class="lg:col-span-7 flex flex-col gap-8">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-primary/10 text-primary text-[10px] font-extrabold uppercase tracking-widest rounded-full mb-4">
                        <span class="material-symbols-outlined text-xs">auto_awesome</span>
                        Top Rated Choice
                    </div>
                    <h2 class="text-4xl lg:text-5xl font-black text-neutral-900 dark:text-white leading-[1.1] mb-4">{{ $product->name }}</h2>
                    @if($product->description)
                        <p class="text-neutral-500 text-lg font-medium leading-relaxed max-w-2xl">{!! nl2br(e($product->description)) !!}</p>
                    @else
                        <p class="text-neutral-500 text-lg font-medium leading-relaxed max-w-2xl">{{ $product->workshop->name ?? 'POD Workshop' }}</p>
                    @endif
                </div>

            @php
                $attributeGroups = [];
                foreach ($product->variants as $variant) {
                    foreach ($variant->variantAttributes as $attr) {
                        $attrName = $attr->attribute_name;
                        $attrValue = $attr->attribute_value;
                        if ($attrName && $attrValue) {
                            $attributeGroups[$attrName] = $attributeGroups[$attrName] ?? [];
                            if (!in_array($attrValue, $attributeGroups[$attrName])) {
                                $attributeGroups[$attrName][] = $attrValue;
                            }
                        }
                    }
                }
                foreach ($attributeGroups as $key => $values) {
                    sort($attributeGroups[$key]);
                }
            @endphp

            @if($product->variants->count() > 0)
                @php
                    // Map tên màu phổ biến sang mã hex để bảo đảm hiển thị nếu dữ liệu chỉ có tên
                    $colorNameMap = [
                        'pepper' => '#3d3d3d',
                        'espresso' => '#4a2c2a',
                        'blue spruce' => '#4a5d23',
                        'crimsom' => '#dc143c',
                        'bay' => '#8b7355',
                        'moss' => '#8a9a5b',
                        'blue jean' => '#5dade2',
                        'seafoam' => '#9fe2bf',
                        'sea foam' => '#9fe2bf',
                        'sea-foam' => '#9fe2bf',
                        'black' => '#000000',
                        'white' => '#ffffff',
                        'red' => '#ff0000',
                        'green' => '#00ff00',
                        'blue' => '#0000ff',
                        'yellow' => '#ffff00',
                        'orange' => '#ffa500',
                        'pink' => '#ffc0cb',
                        'purple' => '#800080',
                        'violet' => '#8a2be2',
                        'indigo' => '#4b0082',
                        'brown' => '#8b4513',
                        'maroon' => '#800000',
                        'navy' => '#000080',
                        'navy blue' => '#000080',
                        'sky' => '#87ceeb',
                        'sky blue' => '#87ceeb',
                        'light blue' => '#add8e6',
                        'dark blue' => '#00008b',
                        'teal' => '#008080',
                        'cyan' => '#00ffff',
                        'magenta' => '#ff00ff',
                        'lime' => '#00ff00',
                        'olive' => '#808000',
                        'gold' => '#ffd700',
                        'silver' => '#c0c0c0',
                        'gray' => '#808080',
                        'grey' => '#808080',
                        'light gray' => '#d3d3d3',
                        'light grey' => '#d3d3d3',
                        'dark gray' => '#404040',
                        'dark grey' => '#404040',
                        'beige' => '#f5f5dc',
                        'ivory' => '#fffff0',
                        'evory' => '#fffff0',
                        'tan' => '#d2b48c',
                        'coral' => '#ff7f50',
                        'salmon' => '#fa8072',
                        'crimson' => '#dc143c',
                        'chocolate' => '#d2691e',
                        'khaki' => '#f0e68c',
                        'plum' => '#dda0dd',
                        'orchid' => '#da70d6',
                        'mint' => '#98ff98',
                        'mint green' => '#98ff98',
                        'forest green' => '#228b22',
                        'dark green' => '#006400',
                        'light green' => '#90ee90',
                    ];
                @endphp
                <div class="glass-container rounded-3xl overflow-hidden flex flex-col">
                    <div class="flex border-b border-white/20 dark:border-white/10 px-6 pt-2">
                        <button class="tab-btn tab-active" onclick="switchPriceTab('seller', this)">Ship by Seller</button>
                        <button class="tab-btn tab-inactive" onclick="switchPriceTab('tiktok', this)">Ship by TikTok</button>
                    </div>
                    <div class="p-8 space-y-8">
                        <!-- Ship by Seller Tab Content -->
                        <div id="priceTabSeller" class="price-tab-content">
                            <div class="grid grid-cols-2 gap-6">
                                <!-- Item 1 Card -->
                                <div class="metric-card">
                                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-neutral-400 mb-2">1 Item Order</span>
                                    <div id="sellerItem1Prices" class="space-y-2">
                                        <div class="text-neutral-400 text-sm">Select variant to see price</div>
                                    </div>
                                </div>
                                <!-- Item 2+ Card -->
                                <div id="sellerItem2Card" class="metric-card border-primary/20 bg-primary/5" style="display:none;">
                                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-primary mb-2">Bulk (2+ Items)</span>
                                    <div id="sellerItem2Prices" class="space-y-2">
                                        <!-- Prices will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Ship by TikTok Tab Content -->
                        <div id="priceTabTiktok" class="price-tab-content" style="display:none;">
                            <div class="grid grid-cols-2 gap-6">
                                <!-- Item 1 Card -->
                                <div class="metric-card">
                                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-neutral-400 mb-2">1 Item Order</span>
                                    <div id="tiktokItem1Prices" class="space-y-2">
                                        <div class="text-neutral-400 text-sm">Select variant to see price</div>
                                    </div>
                                </div>
                                <!-- Item 2+ Card -->
                                <div id="tiktokItem2Card" class="metric-card border-primary/20 bg-primary/5" style="display:none;">
                                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-primary mb-2">Bulk (2+ Items)</span>
                                    <div id="tiktokItem2Prices" class="space-y-2">
                                        <!-- Prices will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                            @if(count($attributeGroups) > 0)
                                @foreach($attributeGroups as $attrName => $attrValues)
                                    @php
                                        $attrKey = strtolower(trim($attrName));
                                        // Kiểm tra nhiều từ khóa cho color
                                        $isColor = str_contains($attrKey, 'color') || 
                                                   str_contains($attrKey, 'màu') || 
                                                   str_contains($attrKey, 'colour') ||
                                                   $attrKey === 'color' ||
                                                   $attrKey === 'màu';
                                        // Kiểm tra size
                                        $isSize = str_contains($attrKey, 'size') || 
                                                  str_contains($attrKey, 'kích cỡ') ||
                                                  $attrKey === 'size' ||
                                                  $attrKey === 'kích cỡ';
                                    @endphp
                                    @if($isColor || $isSize)
                                    <div>
                                        <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-neutral-400 mb-4">{{ $attrName }}</label>
                                        @if($isColor)
                                            @php
                                                $parsedColors = collect($attrValues)->map(function($val) use ($colorNameMap) {
                                                    $original = trim((string)$val);
                                                    $label = $original === '' ? 'N/A' : $original;
                                                    $candidate = $label;

                                                    if ($candidate === 'N/A') {
                                                        return ['css' => '#e5e7eb', 'label' => $label, 'value' => $original];
                                                    }

                                                    if (str_contains($candidate, '/')) {
                                                        $candidate = trim(explode('/', $candidate)[0]);
                                                    }

                                                    $nameKey = strtolower(trim(preg_replace('/\s+/', ' ', $candidate)));
                                                    if (isset($colorNameMap[$nameKey])) {
                                                        return ['css' => $colorNameMap[$nameKey], 'label' => $label, 'value' => $original];
                                                    }

                                                    if (preg_match('/^#?[0-9a-fA-F]{6}$/', $candidate)) {
                                                        return ['css' => '#' . ltrim($candidate, '#'), 'label' => $label, 'value' => $original];
                                                    }
                                                    if (preg_match('/^#?[0-9a-fA-F]{3}$/', $candidate)) {
                                                        $hex = ltrim($candidate, '#');
                                                        $expanded = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
                                                        return ['css' => '#' . $expanded, 'label' => $label, 'value' => $original];
                                                    }

                                                    $rgbParts = array_filter(array_map('trim', explode(',', $candidate)), 'strlen');
                                                    $isValidRgb = count($rgbParts) === 3 && collect($rgbParts)->every(function($p) {
                                                        return ctype_digit($p) && (int)$p >= 0 && (int)$p <= 255;
                                                    });
                                                    if ($isValidRgb) {
                                                        return ['css' => 'rgb(' . implode(',', $rgbParts) . ')', 'label' => $label, 'value' => $original];
                                                    }

                                                    return ['css' => $candidate ?: '#e5e7eb', 'label' => $label, 'value' => $original];
                                                });
                                            @endphp
                                            @if($parsedColors->count() > 0)
                                                <div class="flex flex-wrap gap-2.5">
                                                    @foreach($parsedColors as $color)
                                                    @php
                                                        // Kiểm tra nếu là màu trắng hoặc sáng thì thêm border
                                                        $isWhiteOrLight = false;
                                                        $colorCss = $color['css'];
                                                        $colorLower = strtolower(trim($colorCss));
                                                        
                                                        // Kiểm tra các trường hợp màu trắng
                                                        if (str_contains($colorLower, '#fff') || 
                                                            str_contains($colorLower, '#ffffff') ||
                                                            str_contains($colorLower, 'white') ||
                                                            str_contains($colorLower, 'rgb(255, 255, 255)') ||
                                                            str_contains($colorLower, 'rgb(255,255,255)') ||
                                                            $colorLower === '#fff' ||
                                                            $colorLower === '#ffffff') {
                                                            $isWhiteOrLight = true;
                                                        }
                                                        
                                                        // Kiểm tra label có chứa "white"
                                                        $labelLower = strtolower($color['label']);
                                                        if (str_contains($labelLower, 'white') || str_contains($labelLower, 'trắng')) {
                                                            $isWhiteOrLight = true;
                                                        }
                                                    @endphp
                                                    <button type="button"
                                                        class="variant-choice color-swatch"
                                                        title="{{ $color['label'] }}"
                                                        data-attribute="{{ $attrName }}"
                                                        data-value="{{ $color['value'] }}"
                                                        aria-label="{{ $color['label'] }}"
                                                        onclick="selectVariant('{{ $attrName }}', '{{ $color['value'] }}', this)"
                                                        style="border: 2px solid {{ $isWhiteOrLight ? '#d1d5db' : 'transparent' }};">
                                                        <span class="w-full h-full rounded-full" style="background-color: {{ $color['css'] }}; display: block;"></span>
                                                    </button>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-xs text-neutral-400">No colors available</div>
                                            @endif
                                        @elseif($isSize)
                                            @php
                                                // Sắp xếp size theo thứ tự: S, M, L, XL, 2XL, 3XL, 4XL, 5XL...
                                                $sizeOrder = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL', '6XL'];
                                                $sortedSizes = collect($attrValues)->sortBy(function($size) use ($sizeOrder) {
                                                    $sizeUpper = strtoupper(trim($size));
                                                    $index = array_search($sizeUpper, $sizeOrder);
                                                    return $index !== false ? $index : 999;
                                                })->values()->all();
                                            @endphp
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($sortedSizes as $value)
                                                    <button type="button"
                                                        class="variant-choice size-btn"
                                                        data-attribute="{{ $attrName }}"
                                                        data-value="{{ $value }}"
                                                        onclick="selectVariant('{{ $attrName }}', '{{ $value }}', this)">
                                                        {{ $value }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    @endif
                                @endforeach
                            @else
                                <div class="text-sm text-neutral-400">No variants available</div>
                            @endif
                        </div>
                        <div class="flex flex-col justify-end">
                            <div id="selectedVariantInfo" class="bg-neutral-900/5 dark:bg-white/5 rounded-2xl p-6 border border-white/20">
                        <div class="flex justify-between items-center mb-4">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-neutral-500">Live Estimate</span>
                                    <span class="text-[10px] font-bold text-primary px-2 py-0.5 bg-primary/10 rounded" id="variantBadge">Select variant</span>
                        </div>
                        <div class="flex justify-between items-end">
                            <div class="flex flex-col">
                                        <span class="text-xs text-neutral-500 font-medium">Total Cost (1 unit)</span>
                                        <span class="text-3xl font-black text-neutral-900 dark:text-white" id="variantTotalCost">
                                    @if($firstVariantSummary && isset($firstVariantSummary['prices']['seller']))
                                        {{ $summarySymbol }}{{ number_format($firstVariantSummary['prices']['seller'], 2) }}
                                    @elseif($firstVariantSummary && isset($firstVariantSummary['prices']['tiktok']))
                                        {{ $summarySymbol }}{{ number_format($firstVariantSummary['prices']['tiktok'], 2) }}
                                    @else
                                        -
                                    @endif
                                </span>
                                        @if($firstVariantSummary && isset($firstVariantSummary['sku']) && $firstVariantSummary['sku'] !== 'N/A')
                                            <span class="text-[10px] text-neutral-500 mt-1" id="variantSku">SKU: <span id="variantSkuValue">{{ $firstVariantSummary['sku'] }}</span></span>
                                        @else
                                            <span class="text-[10px] text-neutral-500 mt-1" id="variantSku" style="display: none;">SKU: <span id="variantSkuValue">-</span></span>
                                        @endif
                                        <span class="text-[10px] text-neutral-500 mt-1" id="priceNote" style="display: none;">Starting from</span>
                            </div>
                            <div class="text-right">
                                        <span class="text-[10px] font-bold text-emerald-500">✓ In Stock</span>
                                        <p class="text-[10px] text-neutral-400">Ready in 24-48h</p>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 bg-white/40 dark:bg-black/20 border-t border-white/20 dark:border-white/10 flex gap-4">
                        <a href="#contact" class="flex-1 btn-gradient py-5 rounded-2xl font-black text-base uppercase tracking-widest flex items-center justify-center gap-3 transition-all transform hover:scale-[1.02] active:scale-[0.98]">
                            <span class="material-symbols-outlined">brush</span>
                            DESIGN NOW
                        </a>
                        <button class="w-16 h-[64px] glass-container rounded-2xl flex items-center justify-center text-neutral-400 hover:text-rose-500 transition-all border-none">
                            <span class="material-symbols-outlined text-2xl">favorite</span>
                        </button>
                    </div>
                </div>
            @endif
            </div>
        </div>
    </section>
    
    <section class="bg-white dark:bg-neutral-900/50 py-24 border-t border-neutral-200 dark:border-neutral-800">
        <div class="max-w-[1400px] mx-auto px-6 lg:px-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <div class="space-y-6">
                    <div class="size-14 bg-primary/10 rounded-2xl flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined text-3xl">print</span>
                    </div>
                    <h4 class="text-xl font-black">HD Printing</h4>
                    <p class="text-neutral-500 text-sm leading-relaxed font-medium">
                        Industrial-grade DTG printing using Kornit Atlas MAX. 1200 DPI resolution ensures photographic quality on 100% heavy cotton.
                    </p>
                </div>
                <div class="space-y-6">
                    <div class="size-14 bg-primary/10 rounded-2xl flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined text-3xl">verified</span>
                    </div>
                    <h4 class="text-xl font-black">Quality Assurance</h4>
                    <p class="text-neutral-500 text-sm leading-relaxed font-medium">
                        Every garment passes a 5-point inspection: fiber integrity, print positioning, color calibration, and heat-set durability.
                    </p>
                </div>
                <div class="space-y-6">
                    <div class="size-14 bg-primary/10 rounded-2xl flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined text-3xl">bolt</span>
                    </div>
                    <h4 class="text-xl font-black">Rapid Fulfillment</h4>
                    <p class="text-neutral-500 text-sm leading-relaxed font-medium">
                        Orders synced before 2 PM GMT enter same-day production queue. Optimized 24-48h turnaround window for all standard orders.
                    </p>
                </div>
            </div>
        </div>
    </section>

    @if($relatedProducts->count() > 0)
        <section class="max-w-[1400px] mx-auto px-6 lg:px-12 py-16">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-black text-neutral-900 dark:text-white">Related Products</h2>
                <a href="{{ route('products.index') }}" class="text-primary font-semibold text-sm flex items-center gap-1 hover:gap-2 transition-all">
                    View all <span class="material-symbols-outlined text-sm">arrow_right_alt</span>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedProducts as $related)
                    @php $relatedImage = $related->images->first(); @endphp
                    <a href="{{ route('products.show', $related->slug) }}" class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl overflow-hidden hover:-translate-y-1 transition-all shadow-lg shadow-black/5">
                        <div class="aspect-[4/5] bg-neutral-100 dark:bg-neutral-800">
                            @if($relatedImage)
                                <img class="w-full h-full object-cover" src="{{ \Illuminate\Support\Facades\Storage::url($relatedImage->image_path) }}" alt="{{ $related->name }}">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-neutral-400">
                                    <span class="material-symbols-outlined text-4xl">photo</span>
                                </div>
                            @endif
                        </div>
                        <div class="p-4">
                            <h3 class="text-neutral-900 dark:text-white font-bold leading-snug">{{ $related->name }}</h3>
                            <p class="text-neutral-600 dark:text-neutral-400 text-sm">{{ $related->workshop->name ?? 'POD Workshop' }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</main>


@push('scripts')
<script>
    const variantsData = @json($variantsData);
    const currenciesToConvert = @json($currenciesToConvert ?? []);
    const currencySymbols = { GBP: '£', USD: '$', EUR: '€', VND: 'k' };

    function changeMainImage(src, btn) {
        const main = document.getElementById('mainImage');
        if (main) main.src = src;
        document.querySelectorAll('button[onclick^="changeMainImage"]').forEach(el => {
            el.classList.remove('border-2', 'border-primary', 'shadow-md');
            el.classList.add('border', 'border-neutral-200', 'dark:border-neutral-800', 'opacity-70');
        });
        btn.classList.remove('border', 'border-neutral-200', 'dark:border-neutral-800', 'opacity-70');
        btn.classList.add('border-2', 'border-primary', 'shadow-md', 'opacity-100');
    }

    function selectVariant(attribute, value, btn) {
        // Toggle active state within same attribute group
        document.querySelectorAll(`.variant-choice[data-attribute="${attribute}"]`).forEach(el => {
            el.classList.remove('active-swatch', 'active-size');
            if (el.classList.contains('color-swatch')) {
                // Reset border cho color swatch - giữ border xám nếu là màu trắng
                const currentBorder = el.style.borderColor;
                if (currentBorder === 'rgb(209, 213, 219)' || currentBorder === '#d1d5db') {
                    // Giữ border xám cho màu trắng
                    el.style.borderColor = '#d1d5db';
                } else {
                    el.style.borderColor = 'transparent';
                }
            } else if (el.classList.contains('size-btn')) {
                el.style.borderColor = '#e5e7eb';
                el.style.color = '';
                el.classList.remove('text-primary', 'bg-primary/5');
            }
        });
        if (btn.classList.contains('color-swatch')) {
            btn.style.borderColor = '#f7951d';
            btn.classList.add('active-swatch');
        } else if (btn.classList.contains('size-btn')) {
            btn.style.borderColor = '#f7951d';
            btn.style.color = '#f7951d';
            btn.classList.add('active-size', 'bg-primary/5');
        }
        updateVariant();
    }
    
    function switchPriceTab(type, btnElement) {
        // Update tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('tab-active');
            btn.classList.add('tab-inactive');
        });
        if (btnElement) {
            btnElement.classList.remove('tab-inactive');
            btnElement.classList.add('tab-active');
        }
        
        // Update tab content with fade effect
        document.querySelectorAll('.price-tab-content').forEach(content => {
            content.style.opacity = '0';
            setTimeout(() => {
                content.style.display = 'none';
            }, 150);
        });
        
        setTimeout(() => {
            if (type === 'seller') {
                const sellerTab = document.getElementById('priceTabSeller');
                if (sellerTab) {
                    sellerTab.style.display = 'block';
                    setTimeout(() => {
                        sellerTab.style.opacity = '1';
                    }, 10);
                }
            } else {
                const tiktokTab = document.getElementById('priceTabTiktok');
                if (tiktokTab) {
                    tiktokTab.style.display = 'block';
                    setTimeout(() => {
                        tiktokTab.style.opacity = '1';
                    }, 10);
                }
            }
        }, 150);
        
        // Update Live Estimate price based on selected tab
        updateVariantPriceForTab(type);
    }
    
    function updateVariantPriceForTab(tabType) {
        const selectedAttributes = {};
        document.querySelectorAll('.variant-select').forEach(select => {
            const val = select.value;
            if (val) selectedAttributes[select.dataset.attribute] = val;
        });
        document.querySelectorAll('.variant-choice.active-swatch, .variant-choice.active-size').forEach(btn => {
            const attr = btn.dataset.attribute;
            const val = btn.dataset.value;
            if (attr && val) selectedAttributes[attr] = val;
        });
        
        const variant = variantsData.find(v => {
            const attrs = v.attributes;
            if (Object.keys(selectedAttributes).length !== Object.keys(attrs).length) return false;
            return Object.keys(selectedAttributes).every(key => attrs[key] === selectedAttributes[key]);
        });
        
        const variantTotalCost = document.getElementById('variantTotalCost');
        const priceNote = document.getElementById('priceNote');
        
        if (variant && variantTotalCost) {
            const originalCurrency = variant.currency || 'USD';
            const originalSymbol = currencySymbols[originalCurrency] || originalCurrency;
            
            if (tabType === 'seller' && variant.prices?.seller) {
                variantTotalCost.textContent = `${originalSymbol}${parseFloat(variant.prices.seller).toFixed(2)}`;
                if (priceNote) priceNote.style.display = 'none';
            } else if (tabType === 'tiktok' && variant.prices?.tiktok) {
                variantTotalCost.textContent = `${originalSymbol}${parseFloat(variant.prices.tiktok).toFixed(2)}`;
                if (priceNote) priceNote.style.display = 'none';
            }
        }
    }
    
    function renderPriceCard(containerId, price, convertedPrices, originalCurrency) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        container.innerHTML = '';
        
        // Main price (original currency)
        const originalSymbol = currencySymbols[originalCurrency] || originalCurrency;
        const mainPriceDiv = document.createElement('div');
        mainPriceDiv.className = 'flex items-baseline gap-1';
        mainPriceDiv.innerHTML = `
            <span class="text-4xl font-black text-neutral-900 dark:text-white">${originalSymbol}${parseFloat(price).toFixed(2)}</span>
            <span class="text-xs font-bold text-neutral-400">${originalCurrency}</span>
        `;
        container.appendChild(mainPriceDiv);
        
        // Converted prices
        const convertedDiv = document.createElement('div');
        convertedDiv.className = 'mt-2 flex gap-3 text-[10px] font-bold text-neutral-500';
        const convertedTexts = [];
        
        if (convertedPrices) {
            currenciesToConvert.forEach(currency => {
                const value = convertedPrices[currency];
                if (value !== undefined) {
                    const symbol = currencySymbols[currency] || currency;
                    if (currency === 'VND') {
                        convertedTexts.push(`<span>${parseInt(value).toLocaleString()}${symbol}</span>`);
                    } else {
                        convertedTexts.push(`<span>${symbol}${parseFloat(value).toFixed(2)} ${currency}</span>`);
                    }
                }
            });
        }
        
        if (convertedTexts.length > 0) {
            convertedDiv.innerHTML = convertedTexts.join('');
            container.appendChild(convertedDiv);
        }
    }

    function updateVariant() {
        const selectedAttributes = {};

        // Read selects
        document.querySelectorAll('.variant-select').forEach(select => {
            const val = select.value;
            if (val) selectedAttributes[select.dataset.attribute] = val;
        });

        // Read active buttons (color swatches and size buttons)
        document.querySelectorAll('.variant-choice.active-swatch, .variant-choice.active-size').forEach(btn => {
            const attr = btn.dataset.attribute;
            const val = btn.dataset.value;
            if (attr && val) selectedAttributes[attr] = val;
        });

        const variant = variantsData.find(v => {
            const attrs = v.attributes;
            if (Object.keys(selectedAttributes).length !== Object.keys(attrs).length) return false;
            return Object.keys(selectedAttributes).every(key => attrs[key] === selectedAttributes[key]);
        });

        const variantInfo = document.getElementById('selectedVariantInfo');
        const variantBadge = document.getElementById('variantBadge');
        const variantTotalCost = document.getElementById('variantTotalCost');
        const priceNote = document.getElementById('priceNote');
        const variantSku = document.getElementById('variantSku');
        const variantSkuValue = document.getElementById('variantSkuValue');

        if (!variantInfo) return;

        if (variant) {
            if (variantBadge) {
                // Create badge text from selected attributes
                const selectedAttrs = Object.values(selectedAttributes);
                variantBadge.textContent = selectedAttrs.length > 0 ? selectedAttrs.join(' / ') : variant.sku;
            }
            
            // Update SKU display
            if (variantSku && variantSkuValue && variant.sku && variant.sku !== 'N/A') {
                variantSkuValue.textContent = variant.sku;
                variantSku.style.display = 'block';
            } else if (variantSku) {
                variantSku.style.display = 'none';
            }
            
            const originalCurrency = variant.currency || 'USD';
            const originalSymbol = currencySymbols[originalCurrency] || originalCurrency;
            
            // Update Live Estimate - use seller price first, then tiktok
            if (variant.prices?.seller) {
                if (variantTotalCost) {
                    variantTotalCost.textContent = `${originalSymbol}${parseFloat(variant.prices.seller).toFixed(2)}`;
                }
                if (priceNote) priceNote.style.display = 'none';
            } else if (variant.prices?.tiktok) {
                if (variantTotalCost) {
                    variantTotalCost.textContent = `${originalSymbol}${parseFloat(variant.prices.tiktok).toFixed(2)}`;
                }
                if (priceNote) priceNote.style.display = 'none';
            } else {
                if (variantTotalCost) variantTotalCost.textContent = '-';
                if (priceNote) {
                    priceNote.textContent = 'Contact for price';
                    priceNote.style.display = 'block';
                }
            }
            
            // Render Seller prices
            if (variant.prices?.seller) {
                renderPriceCard('sellerItem1Prices', variant.prices.seller, variant.convertedPrices?.seller?.base, originalCurrency);
                
                if (variant.prices?.seller_additional) {
                    const sellerItem2Card = document.getElementById('sellerItem2Card');
                    if (sellerItem2Card) sellerItem2Card.style.display = 'block';
                    renderPriceCard('sellerItem2Prices', variant.prices.seller_additional, variant.convertedPrices?.seller?.additional, originalCurrency);
                } else {
                    const sellerItem2Card = document.getElementById('sellerItem2Card');
                    if (sellerItem2Card) sellerItem2Card.style.display = 'none';
                }
            } else {
                const sellerItem1Prices = document.getElementById('sellerItem1Prices');
                if (sellerItem1Prices) sellerItem1Prices.innerHTML = '<div class="text-neutral-500 text-sm">Contact for price</div>';
            }
            
            // Render TikTok prices
            if (variant.prices?.tiktok) {
                renderPriceCard('tiktokItem1Prices', variant.prices.tiktok, variant.convertedPrices?.tiktok?.base, originalCurrency);
                
                if (variant.prices?.tiktok_additional) {
                    const tiktokItem2Card = document.getElementById('tiktokItem2Card');
                    if (tiktokItem2Card) tiktokItem2Card.style.display = 'block';
                    renderPriceCard('tiktokItem2Prices', variant.prices.tiktok_additional, variant.convertedPrices?.tiktok?.additional, originalCurrency);
                } else {
                    const tiktokItem2Card = document.getElementById('tiktokItem2Card');
                    if (tiktokItem2Card) tiktokItem2Card.style.display = 'none';
                }
            } else {
                const tiktokItem1Prices = document.getElementById('tiktokItem1Prices');
                if (tiktokItem1Prices) tiktokItem1Prices.innerHTML = '<div class="text-neutral-500 text-sm">Contact for price</div>';
            }
        } else {
            // Count total required attributes from DOM
            const allSelects = document.querySelectorAll('.variant-select');
            const allButtons = document.querySelectorAll('.variant-choice');
            const uniqueAttributes = new Set();
            allSelects.forEach(s => uniqueAttributes.add(s.dataset.attribute));
            allButtons.forEach(b => uniqueAttributes.add(b.dataset.attribute));
            const totalAttributes = uniqueAttributes.size;
            
            const selects = document.querySelectorAll('.variant-select');
            const buttons = document.querySelectorAll('.variant-choice.active-swatch, .variant-choice.active-size');
            const selectedCount = Array.from(selects).filter(s => s.value).length + buttons.length;
            
            if (selectedCount > 0 && selectedCount < totalAttributes) {
                // Partially selected - show "Select variant" message but keep default price
                if (variantBadge) variantBadge.textContent = 'Select variant';
                // Keep showing default price
                if (priceNote) priceNote.style.display = 'none';
                // Hide SKU
                const variantSku = document.getElementById('variantSku');
                if (variantSku) variantSku.style.display = 'none';
                
                // Clear price displays in cards
                const sellerItem1Prices = document.getElementById('sellerItem1Prices');
                const tiktokItem1Prices = document.getElementById('tiktokItem1Prices');
                if (sellerItem1Prices) sellerItem1Prices.innerHTML = '<div class="text-neutral-400 text-sm">Select variant to see price</div>';
                if (tiktokItem1Prices) tiktokItem1Prices.innerHTML = '<div class="text-neutral-400 text-sm">Select variant to see price</div>';
            } else if (selectedCount === totalAttributes && selectedCount > 0) {
                // All selected but variant not found
                if (variantBadge) variantBadge.textContent = 'Not available';
                if (variantTotalCost) variantTotalCost.textContent = 'N/A';
                if (priceNote) {
                    priceNote.textContent = 'Variant not available';
                    priceNote.style.display = 'block';
                }
                // Hide SKU
                const variantSku = document.getElementById('variantSku');
                if (variantSku) variantSku.style.display = 'none';
                
                const sellerItem1Prices = document.getElementById('sellerItem1Prices');
                const tiktokItem1Prices = document.getElementById('tiktokItem1Prices');
                if (sellerItem1Prices) sellerItem1Prices.innerHTML = '<div class="text-neutral-500 text-sm">Variant not available</div>';
                if (tiktokItem1Prices) tiktokItem1Prices.innerHTML = '<div class="text-neutral-500 text-sm">Variant not available</div>';
            } else {
                // Nothing selected - show default price
                if (variantBadge) variantBadge.textContent = 'Select variant';
                // Keep default price visible
                if (priceNote) priceNote.style.display = 'none';
                // Hide SKU
                const variantSku = document.getElementById('variantSku');
                if (variantSku) variantSku.style.display = 'none';
                
                const sellerItem1Prices = document.getElementById('sellerItem1Prices');
                const tiktokItem1Prices = document.getElementById('tiktokItem1Prices');
                if (sellerItem1Prices) sellerItem1Prices.innerHTML = '<div class="text-neutral-400 text-sm">Select variant to see price</div>';
                if (tiktokItem1Prices) tiktokItem1Prices.innerHTML = '<div class="text-neutral-400 text-sm">Select variant to see price</div>';
            }
        }
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Set initial tab content opacity
        document.querySelectorAll('.price-tab-content').forEach(content => {
            if (content.style.display !== 'none') {
                content.style.opacity = '1';
            } else {
                content.style.opacity = '0';
            }
        });
        
        // Show default price if available
        const variantTotalCost = document.getElementById('variantTotalCost');
        if (variantTotalCost && variantTotalCost.textContent.trim() === '-') {
            // Price will be shown from PHP template if available
        }
    });
</script>
@endpush
@endsection

