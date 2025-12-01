@extends('layouts.app')

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
    <!-- Product Info Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-start gap-4">
            <div class="w-16 h-16 rounded-xl flex items-center justify-center font-bold text-white text-xl shadow-md" style="background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
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
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Product Variants ({{ $product->variants->count() }})</h3>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.products.variants.bulk-create', $product) }}" class="px-4 py-2 rounded-lg text-sm font-medium text-white transition-all shadow-sm" style="background-color: #2563EB;" onmouseover="this.style.backgroundColor='#1D4ED8';" onmouseout="this.style.backgroundColor='#2563EB';">
                    + Bulk Add Variants
                </a>
                <a href="{{ route('admin.products.variants.create', $product) }}" class="px-4 py-2 rounded-lg text-sm font-medium transition-all border" style="color: #2563EB; border-color: #DBEAFE; background-color: #EFF6FF;" onmouseover="this.style.backgroundColor='#DBEAFE';" onmouseout="this.style.backgroundColor='#EFF6FF';">
                    + Add Single Variant
                </a>
            </div>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($product->variants as $variant)
            <div class="p-6 hover:bg-gray-50 transition-colors">
                <div class="flex items-center justify-between">
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
                        @if($variantPrices->count() > 0)
                            <div class="flex flex-wrap items-center gap-2 mt-3">
                                @foreach($variantPrices as $tierId => $prices)
                                    @php
                                        $firstPrice = $prices->first();
                                        $tier = $firstPrice->pricingTier ?? null;
                                        $minPrice = $prices->min('base_price');
                                        $maxPrice = $prices->max('base_price');
                                    @endphp
                                    @if($tier)
                                        <span class="px-2 py-1 text-xs font-medium rounded" style="background-color: #DBEAFE; color: #1E40AF;">
                                            <strong>{{ $tier->name }}:</strong> 
                                            {{ $minPrice == $maxPrice ? number_format($minPrice, 2) : number_format($minPrice, 2) . ' - ' . number_format($maxPrice, 2) }}
                                            {{ $firstPrice->currency ?? '' }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 ml-4">
                        <a href="{{ route('admin.products.variants.prices.create', [$product, $variant]) }}" class="px-3 py-1.5 rounded text-sm font-medium transition-all border" style="color: #F59E0B; border-color: #FEF3C7; background-color: #FFFBEB;" onmouseover="this.style.backgroundColor='#FEF3C7';" onmouseout="this.style.backgroundColor='#FFFBEB';">
                            Set Price
                        </a>
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
    </div>

    <!-- Workshop & Pricing Section -->
    @if($product->workshop)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Workshop & Pricing Information</h3>
            <p class="text-sm text-gray-600 mt-1">
                Product belongs to workshop: <strong>{{ $product->workshop->name }} ({{ $product->workshop->code }})</strong>
                @if($product->workshop->market)
                    - Market: <strong>{{ $product->workshop->market->name }} ({{ $product->workshop->market->code }})</strong> - Currency: <strong>{{ $product->workshop->market->currency }}</strong>
                @endif
            </p>
        </div>
        <div class="p-6">
            @if($product->tierPrices && $product->tierPrices->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Variant</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Market</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Tier</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700">Base Price</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($product->tierPrices as $tierPrice)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    @if($tierPrice->variant)
                                        <span class="font-medium text-gray-900">{{ $tierPrice->variant->display_name }}</span>
                                    @else
                                        <span class="text-gray-500 italic">Base Product</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-medium text-gray-900">{{ $tierPrice->market->name }}</span>
                                    <span class="text-gray-500 text-xs ml-2">({{ $tierPrice->market->code }})</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full" style="background-color: #DBEAFE; color: #2563EB;">
                                        {{ $tierPrice->pricingTier->name }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="font-semibold text-gray-900">{{ number_format($tierPrice->base_price, 2) }}</span>
                                    <span class="text-gray-500 text-xs ml-1">{{ $tierPrice->currency }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full" style="background-color: {{ $tierPrice->status === 'active' ? '#D1FAE5' : '#FEE2E2' }}; color: {{ $tierPrice->status === 'active' ? '#065F46' : '#991B1B' }};">
                                        {{ ucfirst($tierPrice->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm text-gray-600">No tier pricing configured for this product yet.</p>
                </div>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection

@php
    $activeMenu = 'products';
@endphp

