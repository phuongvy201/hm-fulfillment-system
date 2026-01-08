@extends('layouts.app')

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
                <div class="flex items-center gap-6 text-sm text-gray-500">
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
            </div>
        </div>
    </div>

    <!-- Variants Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Product Variants 
                        <span class="text-sm font-normal text-gray-600">
                            ({{ $variants->total() }} total{{ $variants->hasPages() ? ' - Showing ' . $variants->firstItem() . '-' . $variants->lastItem() : '' }})
                        </span>
                    </h3>
                    @if($variants->hasPages())
            <div class="flex items-center gap-2">
                        <label class="text-xs text-gray-600">Per page:</label>
                        <select onchange="updateUrlParam('per_page', this.value)" class="px-2 py-1 text-xs border rounded" style="border-color: #D1D5DB;">
                            <option value="25" {{ request('per_page', 50) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page', 50) == 100 ? 'selected' : '' }}>100</option>
                            <option value="200" {{ request('per_page', 50) == 200 ? 'selected' : '' }}>200</option>
                        </select>
                    </div>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                @if($variants->total() > 0)
                <a href="{{ route('admin.products.variants.bulk-prices.create', $product) }}" class="px-4 py-2 rounded-lg text-sm font-medium text-white transition-all shadow-sm" style="background-color: #10B981;" onmouseover="this.style.backgroundColor='#059669';" onmouseout="this.style.backgroundColor='#10B981';">
                    ⚡ Bulk Set Prices by Tier
                </a>
                <a href="{{ route('admin.products.variants.bulk-printing-prices.create', $product) }}" class="px-4 py-2 rounded-lg text-sm font-medium text-white transition-all shadow-sm" style="background-color: #F59E0B;" onmouseover="this.style.backgroundColor='#D97706';" onmouseout="this.style.backgroundColor='#F59E0B';">
                    🖨️ Bulk Set Printing Prices
                </a>
                <a href="{{ route('admin.products.variants.user-prices.bulk-create', $product) }}" class="px-4 py-2 rounded-lg text-sm font-medium text-white transition-all shadow-sm" style="background-color: #8B5CF6;" onmouseover="this.style.backgroundColor='#7C3AED';" onmouseout="this.style.backgroundColor='#8B5CF6';">
                    👤 Bulk Set User Prices
                </a>
                @if($product->workshop)
                <a href="{{ route('admin.products.workshop-prices.bulk-create', $product) }}" class="px-4 py-2 rounded-lg text-sm font-medium text-white transition-all shadow-sm" style="background-color: #EC4899;" onmouseover="this.style.backgroundColor='#DB2777';" onmouseout="this.style.backgroundColor='#EC4899';">
                    🏭 Bulk Set Workshop Prices
                </a>
                @endif
                @endif
                <a href="{{ route('admin.products.variants.bulk-create', $product) }}" class="px-4 py-2 rounded-lg text-sm font-medium text-white transition-all shadow-sm" style="background-color: #2563EB;" onmouseover="this.style.backgroundColor='#1D4ED8';" onmouseout="this.style.backgroundColor='#2563EB';">
                    + Bulk Add Variants
                </a>
                <a href="{{ route('admin.products.variants.create', $product) }}" class="px-4 py-2 rounded-lg text-sm font-medium transition-all border" style="color: #2563EB; border-color: #DBEAFE; background-color: #EFF6FF;" onmouseover="this.style.backgroundColor='#DBEAFE';" onmouseout="this.style.backgroundColor='#EFF6FF';">
                    + Add Single Variant
                </a>
            </div>
        </div>
        
        <!-- Filter Section -->
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <form method="GET" action="{{ route('admin.products.show', $product) }}" id="filterForm" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-xs font-medium mb-1 text-gray-700">Tìm kiếm (SKU/Attribute):</label>
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="Nhập SKU hoặc attribute..."
                            class="w-full px-3 py-2 text-sm border rounded-lg"
                            style="border-color: #D1D5DB;"
                            onchange="document.getElementById('filterForm').submit()"
                        >
                    </div>
                    
                    <!-- Status Filter -->
                    <div>
                        <label class="block text-xs font-medium mb-1 text-gray-700">Status:</label>
                        <select 
                            name="status" 
                            class="w-full px-3 py-2 text-sm border rounded-lg"
                            style="border-color: #D1D5DB;"
                            onchange="document.getElementById('filterForm').submit()"
                        >
                            <option value="">Tất cả</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    
                    <!-- Attribute Filter -->
                    @if(!empty($attributesByGroup))
                    <div>
                        <label class="block text-xs font-medium mb-1 text-gray-700">Filter by Attribute:</label>
                        <select 
                            name="filter_attribute_name" 
                            id="filter_attribute_name"
                            class="w-full px-3 py-2 text-sm border rounded-lg"
                            style="border-color: #D1D5DB;"
                            onchange="updateAttributeValueOptions()"
                        >
                            <option value="">Chọn attribute...</option>
                            @foreach($attributesByGroup as $attrName => $attrValues)
                                <option value="{{ $attrName }}" {{ request('filter_attribute_name') === $attrName ? 'selected' : '' }}>{{ $attrName }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium mb-1 text-gray-700">Attribute Value:</label>
                        <select 
                            name="filter_attribute_value" 
                            id="filter_attribute_value"
                            class="w-full px-3 py-2 text-sm border rounded-lg"
                            style="border-color: #D1D5DB;"
                            onchange="document.getElementById('filterForm').submit()"
                        >
                            <option value="">Chọn value...</option>
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
                
                <!-- Clear Filters Button -->
                @if(request('search') || request('status') || request('filter_attribute_name'))
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.products.show', $product) }}{{ request('per_page') ? '?per_page=' . request('per_page') : '' }}" class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-all" style="color: #6B7280; border-color: #D1D5DB; background-color: #FFFFFF;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='#FFFFFF';">
                        ✗ Xóa bộ lọc
                    </a>
                </div>
                @endif
            </form>
        </div>
        
        <!-- Bulk Actions Bar -->
        @if($variants->total() > 0)
        <div class="px-6 py-3 border-b border-gray-100 bg-blue-50 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input 
                        type="checkbox" 
                        id="selectAllVariants"
                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
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
                    class="px-4 py-2 rounded-lg text-sm font-medium text-white transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                    style="background-color: #DC2626;"
                    onmouseover="if(!this.disabled) this.style.backgroundColor='#B91C1C';"
                    onmouseout="if(!this.disabled) this.style.backgroundColor='#DC2626';"
                >
                    🗑️ Xóa đã chọn
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
        
        <div class="divide-y divide-gray-100">
            @forelse($variants as $variant)
            <div class="p-6 hover:bg-gray-50 transition-colors">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3 flex-1">
                        <input 
                            type="checkbox" 
                            name="variant_checkbox" 
                            value="{{ $variant->id }}"
                            class="variant-checkbox w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            onchange="updateSelectedCount()"
                        >
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h4 class="text-base font-semibold text-gray-900">{{ $variant->display_name }}</h4>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full" style="background-color: {{ $variant->status === 'active' ? '#D1FAE5' : '#FEE2E2' }}; color: {{ $variant->status === 'active' ? '#065F46' : '#991B1B' }};">
                                {{ ucfirst($variant->status) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-4 text-sm text-gray-600">
                            @if($variant->sku)
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                    </svg>
                                    <span><strong>SKU:</strong> {{ $variant->sku }}</span>
                                </div>
                            @endif
                        </div>
                        @php
                            $variantPrices = $variant->tierPrices->groupBy('pricing_tier_id');
                            $variantUserPrices = $variant->userCustomPrices ?? collect();
                            $productPrintingPrices = $product->printingPrices ?? collect();
                            $variantWorkshopPrices = $variant->workshopPrices ?? collect();
                        @endphp
                        @if($variant->attributes && $variant->attributes->count() > 0)
                            <div class="flex flex-wrap items-center gap-2 mt-3">
                                @foreach($variant->attributes as $attr)
                                    <span class="px-2 py-1 text-xs font-medium rounded" style="background-color: #E0F2FE; color: #0369A1;">
                                        <strong>{{ $attr->attribute_name }}:</strong> {{ $attr->attribute_value }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                        
                        <!-- Tier Prices (Standard Prices) -->
                        @if($variantPrices->count() > 0)
                            <div class="mt-3">
                                <div class="text-xs font-semibold text-gray-700 mb-1">💰 Giá tiêu chuẩn:</div>
                                <div class="flex flex-wrap items-center gap-2">
                                @foreach($variantPrices as $tierId => $prices)
                                    @php
                                        $firstPrice = $prices->first();
                                        $tier = $firstPrice->pricingTier ?? null;
                                    @endphp
                                    @if($tier)
                                            @php
                                                $pricesByShipping = $prices->groupBy('shipping_type');
                                            @endphp
                                            <div class="flex flex-col gap-1">
                                                <span class="text-xs font-medium text-gray-600">{{ $tier->name }}:</span>
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($pricesByShipping as $shippingType => $shippingPrices)
                                                        @php
                                                            $market = $shippingPrices->first()->market ?? null;
                                                            $minPrice = $shippingPrices->min('base_price');
                                                            $maxPrice = $shippingPrices->max('base_price');
                                                            $shippingLabel = $shippingType === 'seller' ? 'Seller' : ($shippingType === 'tiktok' ? 'TikTok' : 'Standard');
                                                        @endphp
                                                        @if($market)
                                        <span class="px-2 py-1 text-xs font-medium rounded" style="background-color: #DBEAFE; color: #1E40AF;">
                                                                <strong>{{ $market->code }} ({{ $shippingLabel }}):</strong> 
                                            {{ $minPrice == $maxPrice ? number_format($minPrice, 2) : number_format($minPrice, 2) . ' - ' . number_format($maxPrice, 2) }}
                                                                {{ $market->currency }}
                                                            </span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        <!-- User Custom Prices -->
                        @if($variantUserPrices->count() > 0)
                            <div class="mt-3">
                                <div class="text-xs font-semibold text-gray-700 mb-1">👤 Giá riêng cho user:</div>
                                <div class="flex flex-wrap items-center gap-2">
                                    @foreach($variantUserPrices->groupBy('user_id') as $userId => $userPrices)
                                        @php
                                            $user = $userPrices->first()->user ?? null;
                                            $pricesByShipping = $userPrices->groupBy('shipping_type');
                                        @endphp
                                        @if($user)
                                            <div class="flex flex-col gap-1">
                                                <span class="text-xs font-medium text-gray-600">{{ $user->name }}:</span>
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($pricesByShipping as $shippingType => $shippingPrices)
                                                        @php
                                                            $market = $shippingPrices->first()->market ?? null;
                                                            $price = $shippingPrices->first()->price ?? null;
                                                            $shippingLabel = $shippingType === 'seller' ? 'Seller' : ($shippingType === 'tiktok' ? 'TikTok' : 'Standard');
                                                        @endphp
                                                        @if($market && $price)
                                                            <span class="px-2 py-1 text-xs font-medium rounded" style="background-color: #F3E8FF; color: #7C3AED;">
                                                                <strong>{{ $market->code }} ({{ $shippingLabel }}):</strong> 
                                                                {{ number_format($price, 2) }} {{ $market->currency }}
                                                            </span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        <!-- Workshop Prices -->
                        @if($product->workshop)
                            <div class="mt-3">
                                <div class="text-xs font-semibold text-gray-700 mb-1">🏭 Giá workshop:</div>
                                @if($variantWorkshopPrices->count() > 0)
                                    <div class="flex flex-wrap items-center gap-2">
                                        @foreach($variantWorkshopPrices->groupBy('workshop_id') as $workshopId => $workshopPrices)
                                            @php
                                                $workshop = $workshopPrices->first()->workshop ?? null;
                                                $pricesByShipping = $workshopPrices->groupBy('shipping_type');
                                            @endphp
                                            @if($workshop)
                                                <div class="flex flex-col gap-1">
                                                    <span class="text-xs font-medium text-gray-600">
                                                        <strong>{{ $workshop->code }}</strong>
                                                        @if($workshop->market)
                                                            ({{ $workshop->market->code }})
                                                        @endif
                                                        :
                                                    </span>
                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach($pricesByShipping as $shippingType => $shippingPrices)
                                                            @php
                                                                $wp = $shippingPrices->first();
                                                                $shippingLabel = $shippingType === 'seller' ? 'Seller' : ($shippingType === 'tiktok' ? 'TikTok' : 'Standard');
                                                            @endphp
                                                            <span class="px-2 py-1 text-xs font-medium rounded" style="background-color: #FCE7F3; color: #BE185D;">
                                                                <strong>{{ $shippingLabel }}:</strong> 
                                                                {{ number_format($wp->base_price, 2) }} {{ $wp->currency }}
                                        </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-xs text-gray-500 italic">
                                        Chưa có giá workshop được thiết lập cho variant này.
                                        <a href="{{ route('admin.products.workshop-prices.create', [$product, $variant]) }}" class="text-pink-600 hover:text-pink-700 underline ml-1">
                                            Thiết lập giá
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endif
                        
                        <!-- Printing Prices Info (from product level, shared for all variants) -->
                        @php
                            $variantPrintingPrices = $variant->printingPrices ?? collect();
                            $sharedPrintingPrices = $productPrintingPrices->whereNull('variant_id');
                        @endphp
                        @if($sharedPrintingPrices->count() > 0 || $variantPrintingPrices->count() > 0)
                            <div class="mt-3">
                                <div class="text-xs font-semibold text-gray-700 mb-1">🖨️ Giá in thêm:</div>
                                <div class="flex flex-wrap items-center gap-2">
                                    @if($sharedPrintingPrices->count() > 0)
                                        @foreach($sharedPrintingPrices->groupBy('market_id') as $marketId => $printingPrices)
                                            @php
                                                $market = $printingPrices->first()->market ?? null;
                                            @endphp
                                            @if($market)
                                                <div class="px-2 py-1 text-xs font-medium rounded" style="background-color: #FEF3C7; color: #92400E;">
                                                    <strong>{{ $market->code }} (Chung):</strong>
                                                    @php
                                                        $additionalPrice = $printingPrices->where('sides', 2)->first();
                                                    @endphp
                                                    @if($additionalPrice)
                                                        {{ number_format($additionalPrice->price ?? 0, 2) }} {{ $market->currency }}/mặt thêm
                                                    @else
                                                        @foreach($printingPrices->sortBy('sides') as $pp)
                                                            {{ $pp->sides }} mặt: {{ number_format($pp->price ?? 0, 2) }}{{ !$loop->last ? ', ' : '' }}
                                                        @endforeach
                                                        {{ $market->currency }}
                                                    @endif
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif
                                    @if($variantPrintingPrices->count() > 0)
                                        @foreach($variantPrintingPrices->groupBy('market_id') as $marketId => $printingPrices)
                                            @php
                                                $market = $printingPrices->first()->market ?? null;
                                            @endphp
                                            @if($market)
                                                <div class="px-2 py-1 text-xs font-medium rounded" style="background-color: #FCD34D; color: #78350F;">
                                                    <strong>{{ $market->code }} (Riêng):</strong>
                                                    @php
                                                        $additionalPrice = $printingPrices->where('sides', 2)->first();
                                                    @endphp
                                                    @if($additionalPrice)
                                                        {{ number_format($additionalPrice->price ?? 0, 2) }} {{ $market->currency }}/mặt thêm
                                                    @else
                                                        @foreach($printingPrices->sortBy('sides') as $pp)
                                                            {{ $pp->sides }} mặt: {{ number_format($pp->price ?? 0, 2) }}{{ !$loop->last ? ', ' : '' }}
                                                        @endforeach
                                                        {{ $market->currency }}
                                                    @endif
                                                </div>
                                    @endif
                                @endforeach
                                    @endif
                                </div>
                            </div>
                        @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2 ml-4">
                        <a href="{{ route('admin.products.variants.prices.create', [$product, $variant]) }}" class="px-3 py-1.5 rounded text-sm font-medium transition-all border" style="color: #F59E0B; border-color: #FEF3C7; background-color: #FFFBEB;" onmouseover="this.style.backgroundColor='#FEF3C7';" onmouseout="this.style.backgroundColor='#FFFBEB';">
                            Set Price
                        </a>
                        @if($product->workshop)
                        <a href="{{ route('admin.products.workshop-prices.create', [$product, $variant]) }}" class="px-3 py-1.5 rounded text-sm font-medium transition-all border" style="color: #EC4899; border-color: #FCE7F3; background-color: #FDF2F8;" onmouseover="this.style.backgroundColor='#FCE7F3';" onmouseout="this.style.backgroundColor='#FDF2F8';">
                            Workshop Price
                        </a>
                        @endif
                        <a href="{{ route('admin.workshop-skus.create', $variant) }}" class="px-3 py-1.5 rounded text-sm font-medium transition-all border" style="color: #10B981; border-color: #D1FAE5; background-color: #ECFDF5;" onmouseover="this.style.backgroundColor='#D1FAE5';" onmouseout="this.style.backgroundColor='#ECFDF5';">
                            Workshop SKUs
                        </a>
                        <a href="{{ route('admin.products.variants.edit', [$product, $variant]) }}" class="px-3 py-1.5 rounded text-sm font-medium transition-all border" style="color: #2563EB; border-color: #DBEAFE; background-color: #EFF6FF;" onmouseover="this.style.backgroundColor='#DBEAFE';" onmouseout="this.style.backgroundColor='#EFF6FF';">
                            Edit
                        </a>
                        <form action="{{ route('admin.products.variants.destroy', [$product, $variant]) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this variant?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 rounded text-sm font-medium transition-all border" style="color: #DC2626; border-color: #FEE2E2; background-color: #FEF2F2;" onmouseover="this.style.backgroundColor='#FEE2E2';" onmouseout="this.style.backgroundColor='#FEF2F2';">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-12 text-center">
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
            </div>
            @endforelse
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
        updateSelectedCount();
    }

    // Update selected count
    function updateSelectedCount() {
        const checked = document.querySelectorAll('.variant-checkbox:checked');
        const count = checked.length;
        document.getElementById('selectedCount').textContent = count + ' variants được chọn';
        
        const deleteBtn = document.getElementById('bulkDeleteBtn');
        if (deleteBtn) {
            deleteBtn.disabled = count === 0;
        }
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
@endsection

@php
    $activeMenu = 'products';
@endphp

