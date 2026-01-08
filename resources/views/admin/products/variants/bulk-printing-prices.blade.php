@extends('layouts.app')

@section('title', 'Bulk Set Printing Prices - ' . config('app.name', 'Laravel'))

@section('header-title', 'Bulk Set Printing Prices')
@section('header-subtitle', 'Set printing prices for product variants')

@section('header-actions')
<a href="{{ route('admin.products.show', $product) }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
    ← Back to Product
</a>
@endsection

@section('content')
<div class="max-w-6xl">
    <div class="bg-white rounded-lg shadow-sm p-8">
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
                <ul class="text-sm text-red-800">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-6 p-4 rounded-lg bg-blue-50 border border-blue-200">
            <p class="text-sm text-blue-900">
                <strong>💡 Lưu ý quan trọng:</strong><br>
                • Giá in là giá chung cho tất cả variants của sản phẩm (không khác nhau giữa các variant).<br>
                • <strong>Giá variant đã bao gồm giá in 1 mặt</strong> (khi bạn set giá variant, giá đó đã bao gồm 1 mặt in).<br>
                • Ở đây bạn chỉ cần nhập giá cho <strong>mỗi mặt thêm</strong> (từ mặt 2 trở đi).<br>
                • Ví dụ: Nếu nhập 3 GBP cho mỗi mặt thêm → Mặt 2 = +3 GBP, Mặt 3 = +6 GBP, Mặt 4 = +9 GBP...
            </p>
        </div>

        <form method="POST" action="{{ route('admin.products.variants.bulk-printing-prices.store', $product) }}" id="bulkPrintingPriceForm">
            @csrf

            <div class="space-y-6">
                <!-- Pricing Mode Selection -->
                <div class="mb-6 p-4 rounded-lg border border-blue-200 bg-blue-50">
                    <label class="block text-sm font-semibold mb-3 text-blue-900">Chế độ nhập giá:</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="pricing_mode" value="incremental" checked class="w-4 h-4 text-blue-600 focus:ring-blue-500" onchange="togglePricingMode()">
                            <span class="text-sm text-gray-700">Incremental (Giá cố định cho mỗi mặt thêm - Khuyến nghị)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="pricing_mode" value="per_side" class="w-4 h-4 text-blue-600 focus:ring-blue-500" onchange="togglePricingMode()">
                            <span class="text-sm text-gray-700">Per Side (Giá riêng cho từng số mặt)</span>
                        </label>
                    </div>
                    <p class="text-xs text-gray-600 mt-2">
                        <strong>Incremental:</strong> Nhập giá cho mỗi mặt thêm (ví dụ: 3 GBP) → Hệ thống tự tính: Mặt 2 = +3 GBP, Mặt 3 = +6 GBP, Mặt 4 = +9 GBP... (Mặt 1 đã bao gồm trong giá variant)<br>
                        <strong>Per Side:</strong> Nhập giá riêng cho từng số mặt thêm (2 mặt, 3 mặt, 4 mặt, ...) - Dùng khi giá mỗi mặt không đồng đều
                    </p>
                </div>

                <!-- Printing Price Settings -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold mb-3 text-gray-900">💰 Thiết lập giá in</h4>
                    <div id="printingPriceFields" class="space-y-4">
                        @if(isset($markets) && $markets->count() > 0)
                            @foreach($markets as $index => $market)
                                <div class="p-4 rounded-lg border border-gray-200 bg-gray-50">
                                    <div class="mb-3">
                                        <label class="text-sm font-medium text-gray-900">
                                            {{ $market->name }} ({{ $market->code }}) - {{ $market->currency }}
                                        </label>
                                        @if(isset($existingPrices[$market->id]))
                                            <div class="mt-2 text-xs text-gray-600">
                                                <strong>Giá hiện tại:</strong>
                                                @foreach($existingPrices[$market->id] as $existingPrice)
                                                    {{ $existingPrice->sides }} mặt: {{ number_format($existingPrice->price, 2) }}{{ !$loop->last ? ', ' : '' }}
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Incremental Mode -->
                                    <div id="incremental-mode-{{ $index }}" class="printing-price-mode">
                                        <div>
                                            <label class="block text-xs font-medium mb-1 text-gray-600">Giá mỗi mặt thêm (từ mặt 2 trở đi):</label>
                                            <input 
                                                type="number" 
                                                name="markets[{{ $index }}][additional_side_price]" 
                                                step="0.01" 
                                                min="0" 
                                                placeholder="3.00" 
                                                value="{{ isset($existingPrices[$market->id]) && $existingPrices[$market->id]->where('sides', 2)->first() ? $existingPrices[$market->id]->where('sides', 2)->first()->price : '' }}"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                            >
                                            <p class="text-xs text-gray-500 mt-1">
                                                <strong>Ví dụ:</strong> Nhập 3.00 → Mặt 2 = +3.00, Mặt 3 = +6.00, Mặt 4 = +9.00...<br>
                                                <strong>Lưu ý:</strong> Giá này áp dụng chung cho tất cả variants của sản phẩm. Giá variant đã bao gồm 1 mặt in.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Per Side Mode -->
                                    <div id="per-side-mode-{{ $index }}" class="printing-price-mode hidden">
                                        <div class="space-y-2">
                                            <label class="block text-xs font-medium mb-2 text-gray-600">Giá cho từng số mặt thêm (2-10):</label>
                                            <p class="text-xs text-gray-500 mb-2">
                                                <strong>Lưu ý:</strong> Mặt 1 đã bao gồm trong giá variant (khi bạn set giá variant, giá đó đã bao gồm 1 mặt in).<br>
                                                Ở đây chỉ nhập giá cho các mặt thêm (từ mặt 2 trở đi). Giá này áp dụng chung cho tất cả variants.
                                            </p>
                                            <div class="grid grid-cols-5 gap-2">
                                                @for($side = 2; $side <= 10; $side++)
                                                    <div>
                                                        <label class="block text-xs text-gray-600 mb-1">{{ $side }} mặt:</label>
                                                        <input type="number" name="markets[{{ $index }}][prices][{{ $side-2 }}][sides]" value="{{ $side }}" hidden>
                                                        <input 
                                                            type="number" 
                                                            name="markets[{{ $index }}][prices][{{ $side-2 }}][price]" 
                                                            step="0.01" 
                                                            min="0" 
                                                            placeholder="0.00"
                                                            value="{{ isset($existingPrices[$market->id]) && $existingPrices[$market->id]->where('sides', $side)->first() ? $existingPrices[$market->id]->where('sides', $side)->first()->price : '' }}"
                                                            class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                                        >
                                                    </div>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <input type="hidden" name="markets[{{ $index }}][market_id]" value="{{ $market->id }}">
                                </div>
                            @endforeach
                        @else
                            <p class="text-sm text-gray-600">Không có markets nào.</p>
                        @endif
                    </div>
                </div>

                <!-- Options -->
                <div class="mb-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="clear_existing" value="1" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Xóa giá in cũ trước khi set giá mới (cho các markets được chọn)</span>
                    </label>
                </div>

                <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
                    <button 
                        type="submit"
                        class="px-6 py-3 rounded-lg font-semibold text-white transition-colors bg-orange-500 hover:bg-orange-600"
                    >
                        Áp dụng giá in
                    </button>
                    <a href="{{ route('admin.products.show', $product) }}" class="px-6 py-3 rounded-lg font-semibold transition-colors border border-gray-300 text-gray-700 hover:bg-gray-100">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
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
        togglePricingMode();
    });
</script>
@endsection

@php
    $activeMenu = 'products';
@endphp

