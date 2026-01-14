@extends('layouts.app')

@section('title', $product->name . ' - ' . config('app.name', 'HM Fulfillment System'))
@section('description', \Illuminate\Support\Str::limit($product->description ?? $product->name, 160))
@section('html-class', 'light')
@section('body-class', 'bg-background-light text-[#181511]')

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
<main class="flex-1 max-w-[1400px] mx-auto w-full px-4 lg:px-12 py-8">
    <nav class="flex items-center gap-2 text-sm text-neutral-500 mb-8">
        <a class="hover:text-primary" href="{{ route('home') }}">Home</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <a class="hover:text-primary" href="{{ route('products.index') }}">Catalog</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <span class="text-neutral-900 font-semibold">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <div class="space-y-4">
            <div class="aspect-4/5 bg-white rounded-2xl overflow-hidden relative group border border-neutral-200 shadow-sm">
                @if($primaryImage)
                    <img id="mainImage" alt="{{ $product->name }}" class="w-full h-full object-cover"
                         src="{{ \Illuminate\Support\Facades\Storage::url($primaryImage->image_path) }}">
                @else
                    <div class="w-full h-full flex items-center justify-center text-neutral-400">
                        <span class="material-symbols-outlined text-5xl">photo</span>
                    </div>
                @endif
                <div class="absolute top-6 left-6">
                    <span class="bg-primary text-white text-xs font-black px-3 py-1.5 rounded-full shadow-lg flex items-center gap-1.5 uppercase tracking-widest">
                        <span class="material-symbols-outlined text-sm">local_shipping</span>
                        {{ $product->workshop->market->name ?? 'Fulfillment' }}
                    </span>
                </div>
            </div>
            @if($images->count() > 1)
                <div class="grid grid-cols-4 gap-4">
                    @foreach($images as $image)
                        <button class="aspect-square rounded-xl border {{ $image->id === ($primaryImage->id ?? null) ? 'border-primary ring-2 ring-primary ring-offset-2 ring-offset-white' : 'border-neutral-200' }} overflow-hidden cursor-pointer hover:border-primary/80 transition-all"
                                onclick="changeMainImage('{{ \Illuminate\Support\Facades\Storage::url($image->image_path) }}', this)">
                            <img class="w-full h-full object-cover" src="{{ \Illuminate\Support\Facades\Storage::url($image->image_path) }}" alt="{{ $product->name }}">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="flex flex-col gap-8">
            <div>
                <h1 class="text-4xl lg:text-5xl font-black text-neutral-900 leading-tight mb-2">{{ $product->name }}</h1>
                <p class="text-neutral-600 font-medium">
                    {{ $product->workshop->name ?? 'POD Workshop' }}
                    @if($product->sku)
                        · SKU: {{ $product->sku }}
                    @endif
                </p>
            </div>

            <div class="bg-white border border-neutral-200 p-6 rounded-2xl text-neutral-900 shadow-sm">
                <p class="text-neutral-500 text-sm font-bold uppercase tracking-widest mb-4">Base cost (default tier)</p>
                <div class="grid grid-cols-1 sm:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-neutral-100">
                    <div class="py-3 pr-4">
                        <span class="text-3xl font-black text-primary">
                            {{ ($defaultPrices['default'] ?? null) ? $summarySymbol . number_format($defaultPrices['default'], 2) : 'N/A' }}
                        </span>
                        <span class="block text-xs text-neutral-500 font-bold uppercase mt-1">{{ $marketText }}</span>
                    </div>
                    <div class="py-3 px-4">
                        <span class="text-3xl font-black text-neutral-900">
                            {{ ($defaultPrices['seller'] ?? null) ? $summarySymbol . number_format($defaultPrices['seller'], 2) : '—' }}
                        </span>
                        <span class="block text-xs text-neutral-500 font-bold uppercase mt-1">Seller Ship</span>
                    </div>
                    <div class="py-3 pl-4">
                        <span class="text-3xl font-black text-neutral-900">
                            {{ ($defaultPrices['tiktok'] ?? null) ? $summarySymbol . number_format($defaultPrices['tiktok'], 2) : '—' }}
                        </span>
                        <span class="block text-xs text-neutral-500 font-bold uppercase mt-1">TikTok Ship</span>
                    </div>
                    <div class="py-3 pl-4">
                        <span class="text-3xl font-black text-neutral-900">
                            {{ ($woodPrice ?? null) ? $summarySymbol . number_format($woodPrice, 2) : '—' }}
                        </span>
                        <span class="block text-xs text-neutral-500 font-bold uppercase mt-1">Wood Tier</span>
                    </div>
                </div>
            </div>

            @if($product->description)
                <div class="space-y-2">
                    <span class="text-sm font-bold text-neutral-600 uppercase tracking-widest">Overview</span>
                    <p class="text-neutral-700 leading-relaxed">{!! nl2br(e($product->description)) !!}</p>
                </div>
            @endif

            @php
                $attributeGroups = [];
                foreach ($product->variants as $variant) {
                    foreach ($variant->variantAttributes as $attr) {
                        $attrName = $attr->attribute_name;
                        $attrValue = $attr->attribute_value;
                        $attributeGroups[$attrName] = $attributeGroups[$attrName] ?? [];
                        if (!in_array($attrValue, $attributeGroups[$attrName])) {
                            $attributeGroups[$attrName][] = $attrValue;
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
                <div class="space-y-4">
                    <span class="text-sm font-bold text-white/60 uppercase tracking-widest">Select variant</span>
                    <div class="grid grid-cols-1 gap-4">
                        @foreach($attributeGroups as $attrName => $attrValues)
                            @php
                                $attrKey = strtolower($attrName);
                                $isColor = str_contains($attrKey, 'color');
                                $isSize = str_contains($attrKey, 'size');
                            @endphp
                            <div class="flex flex-col gap-3">
                                <span class="text-sm font-semibold text-neutral-800">{{ $attrName }}</span>

                                @if($isColor)
                                    @php
                                        $parsedColors = collect($attrValues)->map(function($val) use ($colorNameMap) {
                                            $original = trim((string)$val);
                                            $label = $original === '' ? 'N/A' : $original;
                                            $candidate = $label;

                                            if ($candidate === 'N/A') {
                                                return ['css' => '#e5e7eb', 'label' => $label, 'value' => $original];
                                            }

                                            // nếu có dạng "red/blue" lấy phần trước dấu /
                                            if (str_contains($candidate, '/')) {
                                                $candidate = trim(explode('/', $candidate)[0]);
                                            }

                                            // chuẩn hóa key tên màu
                                            $nameKey = strtolower(trim(preg_replace('/\s+/', ' ', $candidate)));
                                            if (isset($colorNameMap[$nameKey])) {
                                                return ['css' => $colorNameMap[$nameKey], 'label' => $label, 'value' => $original];
                                            }

                                            // hex 6 hoặc 3 ký tự, có hoặc không có #
                                            if (preg_match('/^#?[0-9a-fA-F]{6}$/', $candidate)) {
                                                return ['css' => '#' . ltrim($candidate, '#'), 'label' => $label, 'value' => $original];
                                            }
                                            if (preg_match('/^#?[0-9a-fA-F]{3}$/', $candidate)) {
                                                $hex = ltrim($candidate, '#');
                                                $expanded = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
                                                return ['css' => '#' . $expanded, 'label' => $label, 'value' => $original];
                                            }

                                            // RGB với dấu phẩy: "255,0,0"
                                            $rgbParts = array_filter(array_map('trim', explode(',', $candidate)), 'strlen');
                                            $isValidRgb = count($rgbParts) === 3 && collect($rgbParts)->every(function($p) {
                                                return ctype_digit($p) && (int)$p >= 0 && (int)$p <= 255;
                                            });
                                            if ($isValidRgb) {
                                                return ['css' => 'rgb(' . implode(',', $rgbParts) . ')', 'label' => $label, 'value' => $original];
                                            }

                                            // fallback: dùng chính chuỗi (màu tên hợp lệ) hoặc trả về xám nhạt nếu rỗng
                                            return ['css' => $candidate ?: '#e5e7eb', 'label' => $label, 'value' => $original];
                                        });
                                    @endphp
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($parsedColors as $color)
                                            <button type="button"
                                                class="variant-choice w-10 h-10 rounded-full border border-neutral-300 hover:border-primary transition-all flex items-center justify-center"
                                                style="background: {{ $color['css'] }};"
                                                title="{{ $color['label'] }}"
                                                data-attribute="{{ $attrName }}"
                                                data-value="{{ $color['value'] }}"
                                                aria-label="{{ $color['label'] }}"
                                                onclick="selectVariant('{{ $attrName }}', '{{ $color['value'] }}', this)">
                                                <span class="sr-only">{{ $color['label'] }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @elseif($isSize)
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($attrValues as $value)
                                            <button type="button"
                                                class="variant-choice px-4 py-2 rounded-lg border border-neutral-200 text-sm font-bold text-neutral-800 hover:border-primary transition-all"
                                                data-attribute="{{ $attrName }}"
                                                data-value="{{ $value }}"
                                                onclick="selectVariant('{{ $attrName }}', '{{ $value }}', this)">
                                                {{ $value }}
                                            </button>
                                        @endforeach
                                    </div>
                                @else
                                    <select class="w-full bg-white border border-neutral-200 rounded-xl px-3 py-2 text-sm text-neutral-900 focus:ring-2 focus:ring-primary variant-select"
                                            data-attribute="{{ $attrName }}" onchange="updateVariant()">
                                        <option value="">{{ __('Select') }} {{ $attrName }}</option>
                                        @foreach($attrValues as $value)
                                            <option value="{{ $value }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div id="selectedVariantInfo" class="hidden bg-white border border-primary/40 rounded-xl p-4 space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-neutral-600 font-semibold">SKU</span>
                            <span class="font-bold" id="variantSku">-</span>
                        </div>
                        <div class="flex items-center justify-between text-sm" id="variantPriceDefault" style="display:none;">
                            <span class="text-neutral-600 font-semibold">Price (Default)</span>
                            <span class="font-bold text-primary" id="variantPriceDefaultValue">-</span>
                        </div>
                        <div class="flex items-center justify-between text-sm" id="variantPriceSeller" style="display:none;">
                            <span class="text-neutral-600 font-semibold">Price (Seller)</span>
                            <span class="font-bold text-primary" id="variantPriceSellerValue">-</span>
                        </div>
                        <div class="flex items-center justify-between text-sm" id="variantPriceTiktok" style="display:none;">
                            <span class="text-neutral-600 font-semibold">Price (TikTok)</span>
                            <span class="font-bold text-primary" id="variantPriceTiktokValue">-</span>
                        </div>
                        <div class="flex items-center justify-between text-sm" id="variantPriceWood" style="display:none;">
                            <span class="text-neutral-600 font-semibold">Price (Wood)</span>
                            <span class="font-bold text-primary" id="variantPriceWoodValue">-</span>
                        </div>
                    </div>
                </div>
            @endif

            <div class="flex gap-4 mt-2">
                <a href="#contact" class="flex-1 bg-primary hover:bg-orange-500 text-white font-black py-4 rounded-xl shadow-lg shadow-primary/20 transition-all text-lg text-center">
                    Contact to Order
                </a>
                <button class="w-14 h-14 border-2 border-neutral-200 rounded-xl flex items-center justify-center text-neutral-500 hover:text-rose-500 hover:border-rose-500 transition-all">
                    <span class="material-symbols-outlined">favorite</span>
                </button>
            </div>
        </div>
    </div>

    @if($relatedProducts->count() > 0)
            <div class="mt-16 border-t border-neutral-200 pt-10">
            <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-black text-neutral-900">Related Products</h2>
                    <a href="{{ route('products.index') }}" class="text-primary font-semibold text-sm flex items-center gap-1 hover:gap-2 transition-all">
                    View all <span class="material-symbols-outlined text-sm">arrow_right_alt</span>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedProducts as $related)
                    @php $relatedImage = $related->images->first(); @endphp
                    <a href="{{ route('products.show', $related->slug) }}" class="bg-white border border-neutral-200 rounded-2xl overflow-hidden hover:-translate-y-1 transition-all shadow-lg shadow-black/5">
                        <div class="aspect-4/5 bg-neutral-100">
                            @if($relatedImage)
                                <img class="w-full h-full object-cover" src="{{ \Illuminate\Support\Facades\Storage::url($relatedImage->image_path) }}" alt="{{ $related->name }}">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-neutral-400">
                                    <span class="material-symbols-outlined text-4xl">photo</span>
                                </div>
                            @endif
                        </div>
                        <div class="p-4">
                            <h3 class="text-neutral-900 font-bold leading-snug">{{ $related->name }}</h3>
                            <p class="text-neutral-600 text-sm">{{ $related->workshop->name ?? 'POD Workshop' }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</main>

@push('scripts')
<script>
    const variantsData = @json($variantsData);
    const currencySymbols = { GBP: '£', USD: '$', EUR: '€' };

    function changeMainImage(src, btn) {
        const main = document.getElementById('mainImage');
        if (main) main.src = src;
        document.querySelectorAll('button[onclick^="changeMainImage"]').forEach(el => {
            el.classList.remove('border-primary', 'ring-2', 'ring-primary', 'ring-offset-2', 'ring-offset-white');
            el.classList.add('border-neutral-200');
        });
        btn.classList.remove('border-neutral-200');
        btn.classList.add('border-primary', 'ring-2', 'ring-primary', 'ring-offset-2', 'ring-offset-white');
    }

    function selectVariant(attribute, value, btn) {
        // Toggle active state within same attribute group
        document.querySelectorAll(`.variant-choice[data-attribute="${attribute}"]`).forEach(el => {
            el.classList.remove('ring-2', 'ring-primary', 'ring-offset-2', 'border-primary');
            el.classList.add('border-neutral-200');
        });
        btn.classList.add('ring-2', 'ring-primary', 'ring-offset-2', 'border-primary');
        btn.classList.remove('border-neutral-200');
        updateVariant();
    }

    function updateVariant() {
        const selectedAttributes = {};

        // Read selects
        document.querySelectorAll('.variant-select').forEach(select => {
            const val = select.value;
            if (val) selectedAttributes[select.dataset.attribute] = val;
        });

        // Read active buttons
        document.querySelectorAll('.variant-choice.ring-primary').forEach(btn => {
            const attr = btn.dataset.attribute;
            const val = btn.dataset.value;
            if (attr && val) selectedAttributes[attr] = val;
        });

        const variant = variantsData.find(v => {
            const attrs = v.attributes;
            if (Object.keys(selectedAttributes).length !== Object.keys(attrs).length) return false;
            return Object.keys(selectedAttributes).every(key => attrs[key] === selectedAttributes[key]);
        });

        const info = document.getElementById('selectedVariantInfo');
        const skuEl = document.getElementById('variantSku');
        const priceDefault = document.getElementById('variantPriceDefault');
        const priceSeller = document.getElementById('variantPriceSeller');
        const priceTiktok = document.getElementById('variantPriceTiktok');
        const priceWood = document.getElementById('variantPriceWood');
        const pdVal = document.getElementById('variantPriceDefaultValue');
        const psVal = document.getElementById('variantPriceSellerValue');
        const ptVal = document.getElementById('variantPriceTiktokValue');
        const pwVal = document.getElementById('variantPriceWoodValue');

        if (!info || !skuEl) return;

        if (variant) {
            info.classList.remove('hidden');
            skuEl.textContent = variant.sku;
            const currencyCode = (variant.currency || '').toString().toUpperCase();
            const symbol = currencySymbols[currencyCode] || '$';
            const marketText = variant.market ? ` (${variant.market})` : '';

            if (variant.prices?.default) {
                priceDefault.style.display = 'flex';
                pdVal.textContent = `${symbol}${parseFloat(variant.prices.default).toFixed(2)}${marketText}`;
            } else { priceDefault.style.display = 'none'; }

            if (variant.prices?.seller) {
                priceSeller.style.display = 'flex';
                psVal.textContent = `${symbol}${parseFloat(variant.prices.seller).toFixed(2)}${marketText}`;
            } else { priceSeller.style.display = 'none'; }

            if (variant.prices?.tiktok) {
                priceTiktok.style.display = 'flex';
                ptVal.textContent = `${symbol}${parseFloat(variant.prices.tiktok).toFixed(2)}${marketText}`;
            } else { priceTiktok.style.display = 'none'; }

            if (variant.prices?.wood) {
                priceWood.style.display = 'flex';
                pwVal.textContent = `${symbol}${parseFloat(variant.prices.wood).toFixed(2)}${marketText}`;
            } else { priceWood.style.display = 'none'; }

            if (!variant.prices?.default && !variant.prices?.seller && !variant.prices?.tiktok && !variant.prices?.wood) {
                priceDefault.style.display = 'flex';
                pdVal.textContent = 'Contact for price';
            }
        } else {
            const allSelected = Array.from(selects).every(s => s.value);
            if (allSelected) {
                info.classList.remove('hidden');
                skuEl.textContent = 'Not available';
                priceDefault.style.display = 'flex';
                pdVal.textContent = 'Variant not found';
                priceSeller.style.display = 'none';
                priceTiktok.style.display = 'none';
                priceWood.style.display = 'none';
            } else {
                info.classList.add('hidden');
            }
        }
    }
</script>
@endpush
@endsection

