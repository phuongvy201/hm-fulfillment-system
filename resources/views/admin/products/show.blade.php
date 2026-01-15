@extends('layouts.admin-dashboard')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('title', $product->name . ' - ' . config('app.name', 'Laravel'))

@section('header-title', $product->name)
@section('header-subtitle', 'Product details and pricing')

@section('header-actions')
<a href="{{ route('admin.products.edit', $product) }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #2563EB; border-color: #DBEAFE; background-color: #EFF6FF;" onmouseover="this.style.backgroundColor='#DBEAFE';" onmouseout="this.style.backgroundColor='#EFF6FF';">
    Edit Product
</a>
<a href="{{ route('admin.products.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
    ← Back to Products
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Product Images -->
    @if($product->images->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Product Images</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($product->images as $image)
            <div class="relative group">
                <img src="{{ Storage::url($image->image_path) }}" alt="Product Image" class="w-full h-32 object-cover rounded-lg border" style="border-color: #E5E7EB;">
                @if($image->is_primary)
                <span class="absolute top-2 left-2 px-2 py-1 text-xs font-semibold rounded" style="background-color: #10B981; color: white;">Primary</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Product Info Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-start gap-4">
            @php
                $primaryImage = $product->images->where('is_primary', true)->first() ?? $product->images->first();
            @endphp
            @if($primaryImage)
                <img src="{{ Storage::url($primaryImage->image_path) }}" alt="{{ $product->name }}" class="w-16 h-16 rounded-xl object-cover border shadow-md" style="border-color: #E5E7EB;">
            @else
            <div class="w-16 h-16 rounded-xl flex items-center justify-center font-bold text-white text-xl shadow-md" style="background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
            @endif
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $product->name }}</h2>
                    @if($product->status === 'active')
                        <span class="px-3 py-1 text-xs font-semibold rounded-full" style="background-color: #D1FAE5; color: #065F46;">
                            Active
                        </span>
                    @elseif($product->status === 'inactive')
                        <span class="px-3 py-1 text-xs font-semibold rounded-full" style="background-color: #FEE2E2; color: #991B1B;">
                            Inactive
                        </span>
                    @else
                        <span class="px-3 py-1 text-xs font-semibold rounded-full" style="background-color: #F3F4F6; color: #6B7280;">
                            Draft
                        </span>
                    @endif
                </div>
                @if($product->sku)
                    <p class="text-sm text-gray-600 mb-2"><strong>SKU:</strong> {{ $product->sku }}</p>
                @endif
                @if($product->description)
                    <p class="text-gray-600 mb-4">{{ $product->description }}</p>
                @endif
                <div class="flex items-center gap-6 text-sm text-gray-500 mb-4">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>Created {{ $product->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        <span>{{ $product->variants->count() }} {{ $product->variants->count() === 1 ? 'variant' : 'variants' }}</span>
                    </div>
                </div>
                
                <!-- Printing Prices -->
                @php
                    $productPrintingPrices = $product->printingPrices ?? collect();
                    $sharedPrintingPrices = $productPrintingPrices->whereNull('variant_id');
                @endphp
                @if($sharedPrintingPrices->count() > 0)
                    <div class="mt-4 pt-4 border-t" style="border-color: #E5E7EB;">
                        <h3 class="text-sm font-semibold mb-2" style="color: #111827;">Printing Prices (Shared)</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($sharedPrintingPrices->groupBy('market_id') as $marketId => $printingPrices)
                                @php
                                    $market = $printingPrices->first()->market ?? null;
                                @endphp
                                @if($market)
                                    @php
                                        $additionalPrice = $printingPrices->where('sides', 2)->first();
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded" style="background-color: #FEF3C7; color: #92400E; border: 1px solid #FDE68A;">
                                        {{ $market->code }}: {{ $additionalPrice ? number_format($additionalPrice->price ?? 0, 2) . ' ' . $market->currency . '/mặt' : 'N/A' }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Variants Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">SKU Management</h2>
                    <p class="text-sm text-gray-500 mt-1">Oversee product variants, streamline pricing, and monitor stock levels.</p>
                </div>
                <div class="flex items-center gap-3">
                    @if($variants->total() > 0)
                    <!-- Bulk Actions Dropdown -->
                    <div class="relative" id="bulkActionsDropdown">
                        <button type="button" onclick="toggleBulkActionsDropdown()" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-all font-semibold text-sm">
                            <span class="material-symbols-outlined text-lg">layers</span>
                            <span>Bulk Actions</span>
                            <span class="material-symbols-outlined text-base">expand_more</span>
                        </button>
                        <div id="bulkActionsMenu" class="hidden absolute right-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-50 min-w-[220px]">
                            <a href="{{ route('admin.products.variants.bulk-prices.create', $product) }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <span class="material-symbols-outlined text-base">payments</span>
                                Bulk Set Prices by Tier
                            </a>
                            <a href="{{ route('admin.products.variants.bulk-printing-prices.create', $product) }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <span class="material-symbols-outlined text-base">print</span>
                                Bulk Set Printing Prices
                            </a>
                            <a href="{{ route('admin.products.variants.user-prices.bulk-create', $product) }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <span class="material-symbols-outlined text-base">person</span>
                                Bulk Set User Prices
                            </a>
                            @if($product->workshop)
                            <a href="{{ route('admin.products.workshop-prices.bulk-create', $product) }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <span class="material-symbols-outlined text-base">factory</span>
                                Bulk Set Workshop Prices
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif
                    <!-- Add Single Variant Button -->
                    <a href="{{ route('admin.products.variants.create', $product) }}" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-all font-semibold text-sm">
                        <span class="material-symbols-outlined text-base">add</span>
                        Add Single Variant
                    </a>
                    <!-- Primary Action Button -->
                    <a href="{{ route('admin.products.variants.bulk-create', $product) }}" class="flex items-center gap-2 px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-all font-bold text-sm shadow-sm shadow-orange-500/20">
                        <span class="material-symbols-outlined text-lg">add</span>
                        ADD VARIANTS
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Filter Section -->
        <div class="px-6 py-4 border-b border-gray-200 bg-white">
            <form method="GET" action="{{ route('admin.products.show', $product) }}" id="filterForm">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <!-- Search -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">SEARCH PRODUCT OR SKU</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                            <input 
                                type="text" 
                                name="search" 
                                value="{{ request('search') }}"
                                placeholder="Enter SKU, variant name..."
                                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all text-sm"
                            >
                        </div>
                    </div>
                    
                    <!-- Status Filter -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">STATUS</label>
                        <select 
                            name="status" 
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all text-sm appearance-none"
                            onchange="document.getElementById('filterForm').submit()"
                        >
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    
                    <!-- Attribute Filter -->
                    @if(!empty($attributesByGroup))
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">ATTRIBUTE</label>
                        <select 
                            name="filter_attribute_name" 
                            id="filter_attribute_name"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all text-sm appearance-none"
                            onchange="updateAttributeValueOptions()"
                        >
                            <option value="">Select Attribute</option>
                            @foreach($attributesByGroup as $attrName => $attrValues)
                                <option value="{{ $attrName }}" {{ request('filter_attribute_name') === $attrName ? 'selected' : '' }}>{{ $attrName }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">ATTRIBUTE VALUE</label>
                        <select 
                            name="filter_attribute_value" 
                            id="filter_attribute_value"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all text-sm appearance-none"
                            onchange="document.getElementById('filterForm').submit()"
                        >
                            <option value="">Select Value</option>
                            @if(request('filter_attribute_name') && isset($attributesByGroup[request('filter_attribute_name')]))
                                @foreach($attributesByGroup[request('filter_attribute_name')] as $attrValue)
                                    <option value="{{ $attrValue }}" {{ request('filter_attribute_value') === $attrValue ? 'selected' : '' }}>{{ $attrValue }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    @endif
                </div>
                
                <!-- Preserve per_page in filter form -->
                @if(request('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif
                
                <!-- Apply Filters Button -->
                @if(request('search') || request('status') || request('filter_attribute_name'))
                <div class="flex items-center justify-end gap-2 mt-4">
                    <a href="{{ route('admin.products.show', $product) }}{{ request('per_page') ? '?per_page=' . request('per_page') : '' }}" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                        Clear Filters
                    </a>
                    <button type="submit" class="flex items-center gap-2 px-4 py-2 bg-gray-900 hover:bg-black text-white rounded-lg transition-all font-semibold text-sm">
                        <span class="material-symbols-outlined text-base">filter_alt</span>
                        Apply Filters
                    </button>
                </div>
                @else
                <div class="flex items-center justify-end gap-2 mt-4">
                    <button type="submit" class="flex items-center gap-2 px-4 py-2 bg-gray-900 hover:bg-black text-white rounded-lg transition-all font-semibold text-sm">
                        <span class="material-symbols-outlined text-base">filter_alt</span>
                        Apply Filters
                    </button>
                </div>
                @endif
            </form>
        </div>
        
        <!-- Bulk Actions Bar -->
        @if($variants->total() > 0)
        <div class="px-6 py-3 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input 
                        type="checkbox" 
                        id="selectAllVariants"
                        class="w-4 h-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                        onchange="toggleSelectAll(this)"
                    >
                    <span class="text-sm font-medium text-gray-700">Chọn tất cả</span>
                </label>
                <span class="text-sm text-gray-600" id="selectedCount">0 variants được chọn</span>
            </div>
            <div class="flex items-center gap-2">
                <button 
                    type="button"
                    onclick="bulkDeleteVariants()"
                    id="bulkDeleteBtn"
                    disabled
                    class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed bg-gray-900 hover:bg-black"
                >
                    <span class="material-symbols-outlined text-base">delete</span>
                    Xóa đã chọn
                </button>
            </div>
        </div>
        @endif
        
        <!-- Bulk Delete Form (hidden) -->
        <form id="bulkDeleteForm" method="POST" action="{{ route('admin.products.variants.bulk-destroy', $product) }}" style="display: none;">
            @csrf
            @method('DELETE')
            <input type="hidden" name="variant_ids" id="bulkDeleteVariantIds">
        </form>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider w-8">
                            <input 
                                type="checkbox" 
                                id="selectAllVariantsTable"
                                class="rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                                onchange="toggleSelectAll(this)"
                            >
                        </th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Product Variant</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Attributes</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Pricing ($)</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Workshop Price</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($variants as $variant)
                    @php
                        $variantPrices = $variant->tierPrices->groupBy('pricing_tier_id');
                        $variantUserPrices = $variant->userCustomPrices ?? collect();
                        $variantWorkshopPrices = $variant->workshopPrices ?? collect();
                        $workshopSkus = $variant->workshopSkus ?? collect();
                        $primaryImage = $product->images->where('is_primary', true)->first() ?? $product->images->first();
                        
                        // Find Wood tier
                        $woodTier = null;
                        foreach($variantPrices as $tierId => $prices) {
                            $firstPrice = $prices->first();
                            $tier = $firstPrice->pricingTier ?? null;
                            if($tier && strtolower($tier->name) === 'wood') {
                                $woodTier = $tier;
                                break;
                            }
                        }
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-5 align-top">
                            <input 
                                type="checkbox" 
                                name="variant_checkbox" 
                                value="{{ $variant->id }}"
                                class="variant-checkbox rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                                onchange="updateSelectedCount()"
                            >
                        </td>
                        <td class="px-6 py-5 align-top">
                            <div class="flex gap-4">
                                @if($primaryImage)
                                    <div class="h-14 w-14 rounded-lg bg-gray-100 overflow-hidden shrink-0 border border-gray-200">
                                        <img alt="Product Image" class="h-full w-full object-cover" src="{{ Storage::url($primaryImage->image_path) }}">
                                    </div>
                                @else
                                    <div class="h-14 w-14 rounded-lg bg-gray-100 shrink-0 border border-gray-200 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-gray-400">inventory_2</span>
                                    </div>
                                @endif
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-gray-900">{{ $variant->display_name }}</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $variant->status === 'active' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                                            {{ strtoupper($variant->status) }}
                                        </span>
                                    </div>
                                    <div class="flex flex-col">
                                        @if($variant->sku)
                                            <span class="text-xs font-mono text-gray-400 uppercase">{{ $variant->sku }}</span>
                                        @endif
                                        @if($workshopSkus->count() > 0)
                                            @foreach($workshopSkus->take(1) as $wsSku)
                                                <span class="text-[10px] text-gray-400">
                                                    @if($wsSku->workshop)
                                                        {{ $wsSku->workshop->name }}: {{ $wsSku->sku }}
                                                    @else
                                                        {{ $wsSku->sku }}
                                                    @endif
                                                </span>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5 align-top">
                            @if($variant->attributes && $variant->attributes->count() > 0)
                                <div class="flex flex-wrap gap-2">
                                    @foreach($variant->attributes as $attr)
                                        <div class="flex items-center px-2 py-1 rounded bg-blue-50 border border-blue-100">
                                            <span class="text-[10px] font-bold text-blue-700 mr-1">{{ $attr->attribute_name }}:</span>
                                            <span class="text-[10px] text-blue-600">{{ $attr->attribute_value }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-5 align-top">
                            @if($variantPrices->count() > 0)
                                @php
                                    // Organize all prices by tier, market and shipping type
                                    $allPricingData = [];
                                    foreach($variantPrices as $tierId => $tierPrices) {
                                        $firstPrice = $tierPrices->first();
                                    $tier = $firstPrice->pricingTier ?? null;
                                        $pricesByMarket = $tierPrices->groupBy('market_id');
                                    
                                    foreach($pricesByMarket as $marketId => $marketPrices) {
                                        $market = $marketPrices->first()->market ?? null;
                                        if($market) {
                                                $sellerPrice = $marketPrices->where('shipping_type', 'seller')->first();
                                                $tiktokPrice = $marketPrices->where('shipping_type', 'tiktok')->first();
                                                
                                                if($sellerPrice) {
                                                    $allPricingData[] = [
                                                        'tier' => ['id' => $tier?->id, 'name' => $tier?->name ?? 'Default'],
                                                        'market' => ['id' => $market->id, 'name' => $market->name, 'code' => $market->code, 'currency' => $market->currency ?? 'USD'],
                                                        'shipping_type' => 'seller',
                                                        'base_price' => $sellerPrice->base_price,
                                                        'additional_item_price' => $sellerPrice->additional_item_price,
                                                    ];
                                                }
                                                
                                                if($tiktokPrice) {
                                                    $allPricingData[] = [
                                                        'tier' => ['id' => $tier?->id, 'name' => $tier?->name ?? 'Default'],
                                                        'market' => ['id' => $market->id, 'name' => $market->name, 'code' => $market->code, 'currency' => $market->currency ?? 'USD'],
                                                        'shipping_type' => 'tiktok',
                                                        'base_price' => $tiktokPrice->base_price,
                                                        'additional_item_price' => $tiktokPrice->additional_item_price,
                                            ];
                                        }
                                    }
                                        }
                                    }
                                    
                                    // Get first 2 items for preview
                                    $previewPrices = array_slice($allPricingData, 0, 2);
                                    $hasMore = count($allPricingData) > 2;
                                @endphp
                                @if(count($allPricingData) > 0)
                                    <div class="space-y-1.5">
                                        @foreach($previewPrices as $priceItem)
                                                    <div class="flex items-baseline gap-2">
                                                <span class="text-[10px] font-bold text-gray-400 uppercase">{{ $priceItem['market']['code'] }} ({{ ucfirst($priceItem['shipping_type']) }}):</span>
                                                <span class="text-xs text-blue-600 font-semibold">Item 1: <span class="text-gray-900 font-bold">{{ number_format($priceItem['base_price'], 2) }}</span></span>
                                                @if($priceItem['additional_item_price'])
                                                    <span class="text-xs text-blue-600 font-semibold">2+: <span class="text-gray-900 font-bold">{{ number_format($priceItem['additional_item_price'], 2) }}</span></span>
                                                        @endif
                                                    </div>
                                        @endforeach
                                        @if($hasMore)
                                            <button 
                                                type="button"
                                                onclick="showPricingModal({{ $variant->id }})"
                                                class="text-xs text-orange-600 hover:text-orange-700 font-semibold underline mt-1"
                                            >
                                                See more ({{ count($allPricingData) - 2 }} more)
                                            </button>
                                                        @endif
                                                    </div>
                                    
                                    <!-- Store full pricing data for modal -->
                                    <script>
                                        if (typeof variantPricingData === 'undefined') {
                                            variantPricingData = {};
                                        }
                                        variantPricingData[{{ $variant->id }}] = @json($allPricingData);
                                    </script>
                                @else
                                    <span class="text-xs text-gray-400">Chưa có giá</span>
                                @endif
                            @else
                                <span class="text-xs text-gray-400">Chưa có giá</span>
                            @endif
                        </td>
                        <td class="px-6 py-5 align-top">
                            @if($product->workshop)
                                @if($variantWorkshopPrices->count() > 0)
                                    <div class="space-y-2">
                                        @foreach($variantWorkshopPrices->groupBy('workshop_id') as $workshopId => $workshopPrices)
                                            @php
                                                $workshop = $workshopPrices->first()->workshop ?? null;
                                                $pricesByShipping = $workshopPrices->groupBy('shipping_type');
                                            @endphp
                                            @if($workshop)
                                                <div class="text-xs font-medium text-gray-600">
                                                    <strong>{{ $workshop->code }}</strong>
                                                    @if($workshop->market)
                                                        ({{ $workshop->market->code }})
                                                    @endif
                                                </div>
                                                <div class="flex flex-wrap gap-1 ml-2">
                                                    @foreach($pricesByShipping as $shippingType => $shippingPrices)
                                                        @php
                                                            $wp = $shippingPrices->first();
                                                            $shippingLabel = $shippingType === 'seller' ? 'Seller' : ($shippingType === 'tiktok' ? 'TikTok' : 'Standard');
                                                        @endphp
                                                        <span class="px-1.5 py-0.5 text-xs font-medium rounded bg-pink-50 text-pink-700 border border-pink-100">
                                                            {{ $shippingLabel }}: {{ number_format($wp->base_price, 2) }} {{ $wp->currency }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-xs text-gray-500">
                                        Chưa có giá
                                        <a href="{{ route('admin.products.workshop-prices.create', [$product, $variant]) }}" class="text-pink-600 hover:text-pink-700 underline ml-1">
                                            Thiết lập
                                        </a>
                                    </div>
                                @endif
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-5 align-top text-right">
                            <div class="flex justify-end items-center gap-1">
                                <a href="{{ route('admin.products.variants.prices.create', [$product, $variant]) }}" class="p-2 rounded-lg text-gray-400 hover:bg-orange-50 hover:text-orange-600 transition-colors" title="Set Price">
                                    <span class="material-symbols-outlined text-xl">payments</span>
                                </a>
                                @if($product->workshop)
                                <a href="{{ route('admin.products.workshop-prices.create', [$product, $variant]) }}" class="p-2 rounded-lg text-gray-400 hover:bg-pink-50 hover:text-pink-600 transition-colors" title="Workshop Price">
                                    <span class="material-symbols-outlined text-xl">factory</span>
                                </a>
                                @endif
                                <a href="{{ route('admin.workshop-skus.create', $variant) }}" class="p-2 rounded-lg text-gray-400 hover:bg-green-50 hover:text-green-600 transition-colors" title="Workshop SKUs">
                                    <span class="material-symbols-outlined text-xl">qr_code</span>
                                </a>
                                <a href="{{ route('admin.products.variants.edit', [$product, $variant]) }}" class="p-2 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-blue-600 transition-colors" title="Edit">
                                    <span class="material-symbols-outlined text-xl">edit_square</span>
                                </a>
                                <form action="{{ route('admin.products.variants.destroy', [$product, $variant]) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this variant?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-600 transition-colors" title="Delete">
                                        <span class="material-symbols-outlined text-xl">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            <p class="text-sm text-gray-600 mb-4">No variants for this product yet.</p>
                            <a href="{{ route('admin.products.variants.create', $product) }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm" style="background-color: #2563EB;" onmouseover="this.style.backgroundColor='#1D4ED8';" onmouseout="this.style.backgroundColor='#2563EB';">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add First Variant
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($variants->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Showing {{ $variants->firstItem() }} to {{ $variants->lastItem() }} of {{ $variants->total() }} variants
                </div>
                <div class="flex items-center gap-2">
                    {{ $variants->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Workshop Information Section -->
    @if($product->workshop)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Workshop Information</h3>
            <p class="text-sm text-gray-600 mt-1">
                Product belongs to workshop: <strong>{{ $product->workshop->name }} ({{ $product->workshop->code }})</strong>
                @if($product->workshop->market)
                    - Market: <strong>{{ $product->workshop->market->name }} ({{ $product->workshop->market->code }})</strong> - Currency: <strong>{{ $product->workshop->market->currency }}</strong>
                @endif
            </p>
        </div>
    </div>
    @endif
</div>

@php
    // Use allVariantsForAttributes for JavaScript filtering (needed for bulk operations)
    $variantsForJs = $allVariantsForAttributes->map(function($variant) {
        return [
            'id' => $variant->id,
            'display_name' => $variant->display_name,
            'attributes' => $variant->attributes->pluck('attribute_value', 'attribute_name')->toArray()
        ];
    })->values()->all();
@endphp

<script>
    // Variants data for filtering
    const variantsData = @json($variantsForJs ?? []);

    function updateVariantPreview() {
        const form = document.getElementById('bulkPriceForm');
        if (!form) return;
        const formData = new FormData(form);
        
        // Get selected attributes
        const selectedAttributes = {};
        const checkboxes = form.querySelectorAll('.attribute-filter:checked');
        checkboxes.forEach(checkbox => {
            const attrName = checkbox.getAttribute('data-attr-name');
            const attrValue = checkbox.value;
            if (!selectedAttributes[attrName]) {
                selectedAttributes[attrName] = [];
            }
            selectedAttributes[attrName].push(attrValue);
        });

        // Get matching logic
        const matchingLogic = form.querySelector('input[name="matching_logic"]:checked')?.value || 'and';

        // Filter variants
        const matchingVariants = variantsData.filter(variant => {
            if (Object.keys(selectedAttributes).length === 0) {
                return false;
            }

            // Filter out empty attribute groups
            const nonEmptyAttributes = Object.fromEntries(
                Object.entries(selectedAttributes).filter(([key, values]) => values && values.length > 0)
            );

            if (Object.keys(nonEmptyAttributes).length === 0) {
                return false;
            }

            if (matchingLogic === 'and') {
                // AND: Variant must have at least one selected value from EACH attribute group
                for (const [attrName, attrValues] of Object.entries(nonEmptyAttributes)) {
                    const variantValue = variant.attributes[attrName];
                    if (!variantValue || !attrValues.includes(variantValue)) {
                        return false;
                    }
                }
                return true;
            } else {
                // OR: Variant must have at least one selected value from ANY attribute group
                for (const [attrName, attrValues] of Object.entries(nonEmptyAttributes)) {
                    const variantValue = variant.attributes[attrName];
                    if (variantValue && attrValues.includes(variantValue)) {
                        return true;
                    }
                }
                return false;
            }
        });

        // Update preview
        const previewDiv = document.getElementById('variantPreview');
        const previewCount = document.getElementById('previewCount');
        const previewVariants = document.getElementById('previewVariants');

        if (matchingVariants.length > 0) {
            previewDiv.classList.remove('hidden');
            previewCount.textContent = matchingVariants.length;
            
            // Show first 10 variants
            const displayVariants = matchingVariants.slice(0, 10);
            previewVariants.innerHTML = displayVariants.map(v => v.display_name).join(', ') + 
                (matchingVariants.length > 10 ? ` ... và ${matchingVariants.length - 10} variants khác` : '');
        } else {
            previewDiv.classList.add('hidden');
        }
    }

    // Select all attributes
    function selectAllAttributes() {
        const checkboxes = document.querySelectorAll('.attribute-filter');
        checkboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        updateVariantPreview();
    }

    // Deselect all attributes
    function deselectAllAttributes() {
        const checkboxes = document.querySelectorAll('.attribute-filter');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        updateVariantPreview();
    }

    // Select all values in a specific attribute group
    function selectAttributeGroup(attrName) {
        const checkboxes = document.querySelectorAll(`.attribute-filter[data-attr-name="${attrName}"]`);
        checkboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        updateVariantPreview();
    }

    // Deselect all values in a specific attribute group
    function deselectAttributeGroup(attrName) {
        const checkboxes = document.querySelectorAll(`.attribute-filter[data-attr-name="${attrName}"]`);
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        updateVariantPreview();
    }


    function togglePricingMode() {
        const mode = document.querySelector('input[name="pricing_mode"]:checked')?.value || 'incremental';
        const markets = document.querySelectorAll('[id^="incremental-mode-"], [id^="per-side-mode-"]');
        
        markets.forEach(el => {
            if (mode === 'incremental' && el.id.startsWith('incremental-mode-')) {
                el.classList.remove('hidden');
            } else if (mode === 'incremental' && el.id.startsWith('per-side-mode-')) {
                el.classList.add('hidden');
            } else if (mode === 'per_side' && el.id.startsWith('per-side-mode-')) {
                el.classList.remove('hidden');
            } else if (mode === 'per_side' && el.id.startsWith('incremental-mode-')) {
                el.classList.add('hidden');
            }
        });
        
        // Disable/enable inputs based on mode to prevent submission
        const form = document.getElementById('bulkPrintingPriceForm');
        if (form) {
            if (mode === 'incremental') {
                // Disable all per-side inputs (disabled inputs won't be submitted)
                form.querySelectorAll('[id^="per-side-mode-"] input').forEach(input => {
                    input.disabled = true;
                });
                // Enable incremental inputs
                form.querySelectorAll('[id^="incremental-mode-"] input').forEach(input => {
                    input.disabled = false;
                });
            } else {
                // Disable all incremental inputs
                form.querySelectorAll('[id^="incremental-mode-"] input').forEach(input => {
                    input.disabled = true;
                });
                // Enable per-side inputs
                form.querySelectorAll('[id^="per-side-mode-"] input').forEach(input => {
                    input.disabled = false;
                });
            }
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize selected count
        updateSelectedCount();
    });

    // Bulk Actions Dropdown
    function toggleBulkActionsDropdown() {
        const menu = document.getElementById('bulkActionsMenu');
        if (menu) {
            menu.classList.toggle('hidden');
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('bulkActionsDropdown');
        const menu = document.getElementById('bulkActionsMenu');
        if (dropdown && menu && !dropdown.contains(event.target)) {
            menu.classList.add('hidden');
        }
    });

    // Filter and Bulk Delete Functions
    function updateUrlParam(param, value) {
        const url = new URL(window.location.href);
        if (value) {
            url.searchParams.set(param, value);
        } else {
            url.searchParams.delete(param);
        }
        window.location.href = url.toString();
    }

    // Update attribute value options when attribute name changes
    const attributeOptions = @json($attributesByGroup ?? []);
    
    function updateAttributeValueOptions() {
        const attrName = document.getElementById('filter_attribute_name').value;
        const valueSelect = document.getElementById('filter_attribute_value');
        
        // Clear existing options
        valueSelect.innerHTML = '<option value="">Chọn value...</option>';
        
        if (attrName && attributeOptions[attrName]) {
            attributeOptions[attrName].forEach(value => {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = value;
                valueSelect.appendChild(option);
            });
        }
    }

    // Select/Deselect All Variants
    function toggleSelectAll(checkbox) {
        const checkboxes = document.querySelectorAll('.variant-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = checkbox.checked;
        });
        
        // Sync both select all checkboxes
        const selectAllBar = document.getElementById('selectAllVariants');
        const selectAllTable = document.getElementById('selectAllVariantsTable');
        if (selectAllBar) selectAllBar.checked = checkbox.checked;
        if (selectAllTable) selectAllTable.checked = checkbox.checked;
        
        updateSelectedCount();
    }

    // Update selected count
    function updateSelectedCount() {
        const checked = document.querySelectorAll('.variant-checkbox:checked');
        const allCheckboxes = document.querySelectorAll('.variant-checkbox');
        const count = checked.length;
        const total = allCheckboxes.length;
        
        document.getElementById('selectedCount').textContent = count + ' variants được chọn';
        
        const deleteBtn = document.getElementById('bulkDeleteBtn');
        if (deleteBtn) {
            deleteBtn.disabled = count === 0;
        }
        
        // Update select all checkboxes
        const selectAllBar = document.getElementById('selectAllVariants');
        const selectAllTable = document.getElementById('selectAllVariantsTable');
        const allSelected = total > 0 && count === total;
        if (selectAllBar) selectAllBar.checked = allSelected;
        if (selectAllTable) selectAllTable.checked = allSelected;
    }

    // Bulk Delete Variants
    function bulkDeleteVariants() {
        const checked = document.querySelectorAll('.variant-checkbox:checked');
        if (checked.length === 0) {
            alert('Vui lòng chọn ít nhất một variant để xóa.');
            return;
        }

        const variantIds = Array.from(checked).map(cb => cb.value);
        const count = variantIds.length;

        if (!confirm(`Bạn có chắc chắn muốn xóa ${count} variant(s) đã chọn? Hành động này không thể hoàn tác!`)) {
            return;
        }

        // Set variant IDs and submit form
        document.getElementById('bulkDeleteVariantIds').value = JSON.stringify(variantIds);
        document.getElementById('bulkDeleteForm').submit();
    }
</script>

<!-- Pricing Modal -->
<div id="pricingModal" class="hidden fixed inset-0 z-50 overflow-y-auto" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto" style="border: 1px solid #E2E8F0;">
            <!-- Modal Header -->
            <div class="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between" style="border-color: #E2E8F0;">
                <div>
                    <h3 class="text-xl font-bold" style="color: #0F172A;">Pricing Details</h3>
                    <p class="text-sm mt-1" style="color: #64748B;" id="modalVariantName"></p>
                </div>
                <button 
                    type="button"
                    onclick="closePricingModal()"
                    class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
                    style="color: #64748B;"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6">
                <div id="modalPricingContent" class="space-y-6">
                    <!-- Content will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function showPricingModal(variantId) {
        const pricingData = variantPricingData[variantId];
        if (!pricingData || pricingData.length === 0) {
            alert('No pricing data available');
            return;
        }
        
        const modal = document.getElementById('pricingModal');
        const content = document.getElementById('modalPricingContent');
        const variantName = document.getElementById('modalVariantName');
        
        // Get variant name
        const variantRow = document.querySelector(`input.variant-checkbox[value="${variantId}"]`)?.closest('tr');
        const variantNameText = variantRow?.querySelector('.text-sm.font-bold')?.textContent || 'Variant';
        variantName.textContent = variantNameText;
        
        // Build pricing content
        let html = '';
        
        // Group by tier, then by market
        const byTier = {};
        pricingData.forEach(item => {
            const tierName = item.tier?.name || 'Default Tier';
            if (!byTier[tierName]) {
                byTier[tierName] = {};
            }
            const marketKey = item.market.code;
            if (!byTier[tierName][marketKey]) {
                byTier[tierName][marketKey] = {
                    market: item.market,
                    seller: null,
                    tiktok: null
                };
            }
            if (item.shipping_type === 'seller') {
                byTier[tierName][marketKey].seller = item;
            } else if (item.shipping_type === 'tiktok') {
                byTier[tierName][marketKey].tiktok = item;
            }
        });
        
        Object.keys(byTier).forEach(tierName => {
            html += `<div class="border rounded-lg p-4 mb-4" style="border-color: #E2E8F0;">`;
            html += `<h4 class="text-sm font-bold mb-3" style="color: #0F172A;">${tierName}</h4>`;
            html += `<div class="space-y-3">`;
            
            Object.values(byTier[tierName]).forEach(marketData => {
                html += `<div class="bg-gray-50 rounded-lg p-3" style="background-color: #F8FAFC;">`;
                html += `<div class="text-xs font-semibold mb-2" style="color: #475569;">${marketData.market.name} (${marketData.market.code})</div>`;
                
                if (marketData.seller) {
                    html += `<div class="flex items-center justify-between py-1.5 border-b" style="border-color: #E2E8F0;">`;
                    html += `<span class="text-sm" style="color: #64748B;">Seller Shipping:</span>`;
                    html += `<div class="flex items-center gap-3">`;
                    html += `<span class="text-sm font-semibold" style="color: #0F172A;">Item 1: <span class="font-bold" style="color: #F7961D;">${parseFloat(marketData.seller.base_price).toFixed(2)} ${marketData.market.currency || 'USD'}</span></span>`;
                    if (marketData.seller.additional_item_price) {
                        html += `<span class="text-sm font-semibold" style="color: #0F172A;">Item 2+: <span class="font-bold" style="color: #F7961D;">${parseFloat(marketData.seller.additional_item_price).toFixed(2)} ${marketData.market.currency || 'USD'}</span></span>`;
                    }
                    html += `</div>`;
                    html += `</div>`;
                }
                
                if (marketData.tiktok) {
                    html += `<div class="flex items-center justify-between py-1.5">`;
                    html += `<span class="text-sm" style="color: #64748B;">TikTok Shipping:</span>`;
                    html += `<div class="flex items-center gap-3">`;
                    html += `<span class="text-sm font-semibold" style="color: #0F172A;">Item 1: <span class="font-bold" style="color: #F7961D;">${parseFloat(marketData.tiktok.base_price).toFixed(2)} ${marketData.market.currency || 'USD'}</span></span>`;
                    if (marketData.tiktok.additional_item_price) {
                        html += `<span class="text-sm font-semibold" style="color: #0F172A;">Item 2+: <span class="font-bold" style="color: #F7961D;">${parseFloat(marketData.tiktok.additional_item_price).toFixed(2)} ${marketData.market.currency || 'USD'}</span></span>`;
                    }
                    html += `</div>`;
                    html += `</div>`;
                }
                
                html += `</div>`;
            });
            
            html += `</div>`;
            html += `</div>`;
        });
        
        content.innerHTML = html;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    function closePricingModal() {
        const modal = document.getElementById('pricingModal');
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
    
    // Close modal when clicking outside
    document.getElementById('pricingModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closePricingModal();
        }
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePricingModal();
        }
    });
</script>
@endsection

@php
    $activeMenu = 'products';
@endphp

