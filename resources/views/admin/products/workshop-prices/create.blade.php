@extends('layouts.app')

@section('title', 'Set Workshop Price - ' . $variant->display_name . ' - ' . config('app.name', 'Laravel'))

@section('header-title', 'Set Workshop Price')
@section('header-subtitle', $variant->display_name)

@section('header-actions')
<a href="{{ route('admin.products.show', $product) }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
    ← Back to Product
</a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 pt-6 pb-4">
            <div class="mb-6">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">🏭 Set Workshop Price</h3>
                <div class="p-4 rounded-lg" style="background-color: #EFF6FF; border: 1px solid #DBEAFE;">
                    <p class="text-sm" style="color: #1E40AF;">
                        <strong>Variant:</strong> {{ $variant->display_name }}<br>
                        <strong>Workshop:</strong> {{ $workshop->name }} ({{ $workshop->code }})<br>
                        @if($market)
                        <strong>Market:</strong> {{ $market->name }} ({{ $market->code }}) - Currency: <strong>{{ $market->currency }}</strong>
                        @endif
                    </p>
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-lg" style="background-color: #FEE2E2; border: 1px solid #EF4444;">
                    <ul class="text-sm" style="color: #991B1B;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.products.workshop-prices.store', [$product, $variant]) }}">
                @csrf

                <div class="space-y-6">
                    <!-- Price Settings -->
                    <div>
                        <h4 class="text-sm font-semibold mb-3" style="color: #111827;">💰 Thiết lập giá workshop (base price)</h4>
                        <div class="mb-3 p-3 rounded-lg" style="background-color: #FEF3C7; border: 1px solid #FCD34D;">
                            <p class="text-xs" style="color: #92400E;">
                                <strong>💡 Lưu ý:</strong><br>
                                • Giá workshop là giá cơ bản mà workshop tính cho variant này<br>
                                • Giá này không bao gồm phí ship hay giá in<br>
                                • Mỗi variant có 2 loại giá:<br>
                                &nbsp;&nbsp;- <strong>Giá ship by Seller:</strong> Giá workshop khi ship by seller<br>
                                &nbsp;&nbsp;- <strong>Giá ship by TikTok:</strong> Giá workshop khi ship by tiktok<br>
                                • Currency sẽ tự động lấy từ market của workshop ({{ $market->currency ?? 'USD' }})
                            </p>
                        </div>
                        
                        <div class="space-y-4">
                            <!-- Giá ship by Seller -->
                            <div>
                                <label class="block text-xs font-medium mb-1" style="color: #6B7280;">Giá workshop ship by Seller ({{ $market->currency ?? 'USD' }}):</label>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-gray-600">{{ $market->currency_symbol ?? $market->currency ?? 'USD' }}</span>
                                    <input 
                                        type="number" 
                                        name="prices[seller][base_price]" 
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        value="{{ old('prices.seller.base_price', $existingPrices['seller']->base_price ?? '') }}"
                                        class="flex-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-1 transition-all text-sm"
                                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                                        onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 2px rgba(37, 99, 235, 0.1)';"
                                        onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                                    >
                                </div>
                                @if(isset($existingPrices['seller']))
                                <div class="text-xs text-green-600 mt-1">✓ Saved</div>
                                @endif
                            </div>
                            
                            <!-- Giá ship by TikTok -->
                            <div>
                                <label class="block text-xs font-medium mb-1" style="color: #6B7280;">Giá workshop ship by TikTok ({{ $market->currency ?? 'USD' }}):</label>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-gray-600">{{ $market->currency_symbol ?? $market->currency ?? 'USD' }}</span>
                                    <input 
                                        type="number" 
                                        name="prices[tiktok][base_price]" 
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        value="{{ old('prices.tiktok.base_price', $existingPrices['tiktok']->base_price ?? '') }}"
                                        class="flex-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-1 transition-all text-sm"
                                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                                        onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 2px rgba(37, 99, 235, 0.1)';"
                                        onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                                    >
                                </div>
                                @if(isset($existingPrices['tiktok']))
                                <div class="text-xs text-green-600 mt-1">✓ Saved</div>
                                @endif
                            </div>
                            
                            <div>
                                <label class="block text-xs font-medium mb-1" style="color: #6B7280;">Status:</label>
                                <select 
                                    name="status" 
                                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-1 transition-all text-sm"
                                    style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                                    required
                                >
                                    @php
                                        $firstPrice = $existingPrices['seller'] ?? $existingPrices['tiktok'] ?? null;
                                        $defaultStatus = $firstPrice ? $firstPrice->status : 'active';
                                    @endphp
                                    <option value="active" {{ old('status', $defaultStatus) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $defaultStatus) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium mb-1" style="color: #6B7280;">Valid From:</label>
                                    <input 
                                        type="date" 
                                        name="valid_from" 
                                        value="{{ old('valid_from', $firstPrice && $firstPrice->valid_from ? $firstPrice->valid_from->format('Y-m-d') : '') }}"
                                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-1 transition-all text-sm"
                                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                                    >
                                </div>
                                <div>
                                    <label class="block text-xs font-medium mb-1" style="color: #6B7280;">Valid To:</label>
                                    <input 
                                        type="date" 
                                        name="valid_to" 
                                        value="{{ old('valid_to', $firstPrice && $firstPrice->valid_to ? $firstPrice->valid_to->format('Y-m-d') : '') }}"
                                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-1 transition-all text-sm"
                                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-6 border-t" style="border-color: #E5E7EB;">
                    <button 
                        type="submit"
                        class="px-6 py-3 rounded-lg font-semibold text-white transition-all"
                        style="background-color: #10B981;"
                        onmouseover="this.style.backgroundColor='#059669';"
                        onmouseout="this.style.backgroundColor='#10B981';"
                    >
                        Lưu giá workshop
                    </button>
                    <a href="{{ route('admin.products.show', $product) }}" class="px-6 py-3 rounded-lg font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@php
    $activeMenu = 'products';
@endphp

