@extends('layouts.app')

@section('title', 'Set Price for Variant - ' . config('app.name', 'Laravel'))

@section('header-title', 'Set Price for Variant: ' . $variant->display_name)
@section('header-subtitle', 'Configure pricing for this variant by market')

@section('header-actions')
<a href="{{ route('admin.products.show', $product) }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
    ← Back to Product
</a>
@endsection

@section('content')
<div class="max-w-5xl">
    <div class="bg-white rounded-lg shadow-sm p-8">
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-lg" style="background-color: #FEE2E2; border: 1px solid #EF4444;">
                <ul class="text-sm" style="color: #991B1B;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-6 p-4 rounded-lg" style="background-color: #EFF6FF; border: 1px solid #DBEAFE;">
            <div class="flex items-center gap-4">
                <div>
                    <h4 class="font-semibold text-gray-900">{{ $variant->display_name }}</h4>
                    <div class="flex items-center gap-4 mt-1 text-sm text-gray-600">
                        @if($variant->sku)
                            <span><strong>SKU:</strong> <code class="px-2 py-1 rounded bg-white">{{ $variant->sku }}</code></span>
                        @endif
                        @if($variant->attributes && $variant->attributes->count() > 0)
                            @foreach($variant->attributes as $attr)
                                <span><strong>{{ $attr->attribute_name }}:</strong> {{ $attr->attribute_value }}</span>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.products.variants.prices.store', [$product, $variant]) }}">
            @csrf

            <div class="space-y-6">
                <!-- Pricing Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse">
                        <thead>
                            <tr class="border-b-2" style="border-color: #E5E7EB;">
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 bg-gray-50">Market</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700 bg-gray-50 min-w-[200px]">Price</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700 bg-gray-50 min-w-[150px]">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="divide-color: #E5E7EB;">
                            @foreach($markets as $marketItem)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4 align-top">
                                    <div class="font-medium text-gray-900">{{ $marketItem->name }}</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $marketItem->code }} - {{ $marketItem->currency }}
                                        @if($market == $marketItem)
                                            <span class="ml-2 px-2 py-0.5 rounded text-xs" style="background-color: #D1FAE5; color: #065F46;">Default</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    @php
                                        $existingPrice = $existingPrices[$marketItem->id] ?? null;
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-gray-600">{{ $marketItem->currency_symbol ?? $marketItem->currency }}</span>
                                        <input 
                                            type="number" 
                                            name="prices[{{ $marketItem->id }}][base_price]" 
                                            value="{{ old("prices.{$marketItem->id}.base_price", $existingPrice ? $existingPrice->base_price : '') }}"
                                            step="0.01"
                                            min="0"
                                            placeholder="0.00"
                                            class="flex-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-1 transition-all text-sm"
                                            style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                                            onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 2px rgba(37, 99, 235, 0.1)';"
                                            onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                                        >
                                    </div>
                                    @if($existingPrice)
                                    <div class="text-xs text-green-600 mt-1">✓ Saved</div>
                                    @endif
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <input 
                                        type="hidden" 
                                        name="prices[{{ $marketItem->id }}][market_id]" 
                                        value="{{ $marketItem->id }}"
                                    >
                                    <select 
                                        name="prices[{{ $marketItem->id }}][status]"
                                        class="w-full px-2 py-1.5 border rounded-lg focus:outline-none focus:ring-1 transition-all text-xs"
                                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                                    >
                                        <option value="active" {{ old("prices.{$marketItem->id}.status", $existingPrice ? $existingPrice->status : 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old("prices.{$marketItem->id}.status", $existingPrice ? $existingPrice->status : 'active') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center gap-4 pt-4 border-t" style="border-color: #E5E7EB;">
                    <button 
                        type="submit"
                        class="px-6 py-3 rounded-lg font-semibold text-white transition-all"
                        style="background-color: #2563EB;"
                        onmouseover="this.style.backgroundColor='#1D4ED8';"
                        onmouseout="this.style.backgroundColor='#2563EB';"
                    >
                        Save Prices
                    </button>
                    <a href="{{ route('admin.products.show', $product) }}" class="px-6 py-3 rounded-lg font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@php
    $activeMenu = 'products';
@endphp
