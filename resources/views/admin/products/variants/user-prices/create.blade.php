@extends('layouts.admin-dashboard') 

@section('title', 'Set Custom Price for User - ' . config('app.name', 'Laravel'))

@section('header-title', 'Set Custom Price for User: ' . $user->name)
@section('header-subtitle', 'Variant: ' . $variant->display_name)

@section('header-actions')
<a href="{{ route('admin.products.show', $product) }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
    ← Back to Product
</a>
@endsection

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">
    @if ($errors->any())
        <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
            <ul class="text-sm text-red-800">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.products.variants.user-prices.store', [$product, $variant, $user]) }}">
        @csrf

        <div class="space-y-6">
            <!-- User & Variant Info Section -->
            <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-orange-600">info</span>
                    <h2 class="text-lg font-semibold text-gray-900">Thông tin</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="material-symbols-outlined text-blue-600">person</span>
                            <h4 class="font-semibold text-gray-900">{{ $user->name }}</h4>
                        </div>
                        <div class="text-sm text-gray-600 ml-9">{{ $user->email }}</div>
                    </div>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="material-symbols-outlined text-green-600">inventory_2</span>
                            <h4 class="font-semibold text-gray-900">{{ $variant->display_name }}</h4>
                        </div>
                        <div class="flex items-center gap-4 mt-1 text-sm text-gray-600 ml-9">
                            @if($variant->sku)
                                <span><strong>SKU:</strong> <code class="px-2 py-1 rounded bg-white text-xs">{{ $variant->sku }}</code></span>
                            @endif
                            @if($variant->attributes && $variant->attributes->count() > 0)
                                @foreach($variant->attributes as $attr)
                                    <span><strong>{{ $attr->attribute_name }}:</strong> {{ $attr->attribute_value }}</span>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </section>

            <!-- Info Section -->
            <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-orange-600">lightbulb</span>
                    <h2 class="text-lg font-semibold text-gray-900">Lưu ý</h2>
                </div>
                <div class="p-6">
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <p class="text-sm text-amber-800">
                            <strong>💡 Lưu ý:</strong> Giá bạn nhập ở đây sẽ áp dụng riêng cho user <strong>{{ $user->name }}</strong> và variant <strong>{{ $variant->display_name }}</strong>.<br>
                            Giá này sẽ có độ ưu tiên cao nhất (cao hơn giá tier, giá team, giá mặc định).
                        </p>
                    </div>
                </div>
            </section>

            <!-- Pricing Table Section -->
            <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-orange-600">edit_note</span>
                    <h2 class="text-lg font-semibold text-gray-900">Thiết lập giá</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Market</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center min-w-[200px]">Ship by Seller</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center min-w-[200px]">Ship by TikTok</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center min-w-[120px]">Valid From</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center min-w-[120px]">Valid To</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach($markets as $marketItem)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-5 align-top">
                                    <div class="font-medium text-gray-900">{{ $marketItem->name }}</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $marketItem->code }} - {{ $marketItem->currency }}
                                    </div>
                                </td>
                                <td class="px-6 py-5 align-top">
                                    @php
                                        $existingPriceSeller = $existingPrices->where('market_id', $marketItem->id)->where('shipping_type', 'seller')->first();
                                    @endphp
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Item 1:</label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">{{ $marketItem->currency_symbol ?? ($marketItem->currency === 'USD' ? '$' : ($marketItem->currency === 'EUR' ? '€' : ($marketItem->currency === 'GBP' ? '£' : $marketItem->currency . ' '))) }}</span>
                                                <input 
                                                    type="number" 
                                                    name="prices[{{ $marketItem->id }}_seller][price]" 
                                                    value="{{ old("prices.{$marketItem->id}_seller.price", $existingPriceSeller ? $existingPriceSeller->price : '') }}"
                                                    step="0.01"
                                                    min="0"
                                                    placeholder="0.00"
                                                    class="w-full px-3 py-2 pl-7 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-all text-sm"
                                                >
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Item 2+ (trừ phí label):</label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">{{ $marketItem->currency_symbol ?? ($marketItem->currency === 'USD' ? '$' : ($marketItem->currency === 'EUR' ? '€' : ($marketItem->currency === 'GBP' ? '£' : $marketItem->currency . ' '))) }}</span>
                                                <input 
                                                    type="number" 
                                                    name="prices[{{ $marketItem->id }}_seller][additional_item_price]" 
                                                    value="{{ old("prices.{$marketItem->id}_seller.additional_item_price", $existingPriceSeller ? $existingPriceSeller->additional_item_price : '') }}"
                                                    step="0.01"
                                                    min="0"
                                                    placeholder="0.00"
                                                    class="w-full px-3 py-2 pl-7 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-all text-sm"
                                                >
                                            </div>
                                        </div>
                                        <input type="hidden" name="prices[{{ $marketItem->id }}_seller][market_id]" value="{{ $marketItem->id }}">
                                        <input type="hidden" name="prices[{{ $marketItem->id }}_seller][shipping_type]" value="seller">
                                        <input type="hidden" name="prices[{{ $marketItem->id }}_seller][status]" value="active">
                                        @if($existingPriceSeller)
                                        <div class="flex items-center gap-1 text-xs text-green-600">
                                            <span class="material-symbols-outlined text-sm">check_circle</span>
                                            Saved
                                        </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-5 align-top">
                                    @php
                                        $existingPriceTiktok = $existingPrices->where('market_id', $marketItem->id)->where('shipping_type', 'tiktok')->first();
                                    @endphp
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Item 1:</label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">{{ $marketItem->currency_symbol ?? ($marketItem->currency === 'USD' ? '$' : ($marketItem->currency === 'EUR' ? '€' : ($marketItem->currency === 'GBP' ? '£' : $marketItem->currency . ' '))) }}</span>
                                                <input 
                                                    type="number" 
                                                    name="prices[{{ $marketItem->id }}_tiktok][price]" 
                                                    value="{{ old("prices.{$marketItem->id}_tiktok.price", $existingPriceTiktok ? $existingPriceTiktok->price : '') }}"
                                                    step="0.01"
                                                    min="0"
                                                    placeholder="0.00"
                                                    class="w-full px-3 py-2 pl-7 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-all text-sm"
                                                >
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Item 2+ (trừ phí label):</label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">{{ $marketItem->currency_symbol ?? ($marketItem->currency === 'USD' ? '$' : ($marketItem->currency === 'EUR' ? '€' : ($marketItem->currency === 'GBP' ? '£' : $marketItem->currency . ' '))) }}</span>
                                                <input 
                                                    type="number" 
                                                    name="prices[{{ $marketItem->id }}_tiktok][additional_item_price]" 
                                                    value="{{ old("prices.{$marketItem->id}_tiktok.additional_item_price", $existingPriceTiktok ? $existingPriceTiktok->additional_item_price : '') }}"
                                                    step="0.01"
                                                    min="0"
                                                    placeholder="0.00"
                                                    class="w-full px-3 py-2 pl-7 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-all text-sm"
                                                >
                                            </div>
                                        </div>
                                        <input type="hidden" name="prices[{{ $marketItem->id }}_tiktok][market_id]" value="{{ $marketItem->id }}">
                                        <input type="hidden" name="prices[{{ $marketItem->id }}_tiktok][shipping_type]" value="tiktok">
                                        <input type="hidden" name="prices[{{ $marketItem->id }}_tiktok][status]" value="active">
                                        @if($existingPriceTiktok)
                                        <div class="flex items-center gap-1 text-xs text-green-600">
                                            <span class="material-symbols-outlined text-sm">check_circle</span>
                                            Saved
                                        </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-5 align-top">
                                    <div class="space-y-2">
                                        <input 
                                            type="date" 
                                            name="prices[{{ $marketItem->id }}_seller][valid_from]" 
                                            value="{{ old("prices.{$marketItem->id}_seller.valid_from", $existingPriceSeller && $existingPriceSeller->valid_from ? $existingPriceSeller->valid_from->format('Y-m-d') : '') }}"
                                            class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-all text-sm"
                                        >
                                        <input 
                                            type="date" 
                                            name="prices[{{ $marketItem->id }}_tiktok][valid_from]" 
                                            value="{{ old("prices.{$marketItem->id}_tiktok.valid_from", $existingPriceTiktok && $existingPriceTiktok->valid_from ? $existingPriceTiktok->valid_from->format('Y-m-d') : '') }}"
                                            class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-all text-sm"
                                        >
                                    </div>
                                </td>
                                <td class="px-6 py-5 align-top">
                                    <div class="space-y-2">
                                        <input 
                                            type="date" 
                                            name="prices[{{ $marketItem->id }}_seller][valid_to]" 
                                            value="{{ old("prices.{$marketItem->id}_seller.valid_to", $existingPriceSeller && $existingPriceSeller->valid_to ? $existingPriceSeller->valid_to->format('Y-m-d') : '') }}"
                                            class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-all text-sm"
                                        >
                                        <input 
                                            type="date" 
                                            name="prices[{{ $marketItem->id }}_tiktok][valid_to]" 
                                            value="{{ old("prices.{$marketItem->id}_tiktok.valid_to", $existingPriceTiktok && $existingPriceTiktok->valid_to ? $existingPriceTiktok->valid_to->format('Y-m-d') : '') }}"
                                            class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-all text-sm"
                                        >
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-6 bg-gray-50 border-t border-gray-100 flex flex-col sm:flex-row gap-4 items-center justify-between">
                    <div class="flex-1"></div>
                    <div class="flex gap-3 w-full sm:w-auto">
                        <a href="{{ route('admin.products.show', $product) }}" class="flex-1 sm:flex-none px-6 py-2.5 rounded-lg font-semibold border border-gray-200 hover:bg-white transition-all text-center">
                            Cancel
                        </a>
                        <button 
                            type="submit"
                            class="flex-1 sm:flex-none px-8 py-2.5 rounded-lg font-semibold bg-orange-600 text-white hover:bg-orange-700 shadow-lg shadow-orange-600/20 transition-all transform active:scale-[0.98]"
                        >
                            Save Custom Prices
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </form>
</div>
@endsection

@php
    $activeMenu = 'products';
@endphp
