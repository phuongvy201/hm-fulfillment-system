@extends('layouts.app')

@section('title', 'Bulk Set Prices by Tier - ' . config('app.name', 'Laravel'))

@section('header-title', 'Bulk Set Prices by Tier')
@section('header-subtitle', 'Set prices for multiple variants by pricing tier')

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

        <div class="mb-6 p-4 rounded-lg bg-yellow-50 border border-yellow-200">
            <p class="text-sm text-yellow-800">
                <strong>💡 Lưu ý:</strong> Giá bạn nhập ở đây sẽ áp dụng cho các variants được filter và tier đã chọn.<br>
                Giá này đã bao gồm: base cost + phí ship + <strong>1 mặt in</strong>
            </p>
        </div>

        <form method="POST" action="{{ route('admin.products.variants.bulk-prices.store', $product) }}" id="bulkPriceForm">
            @csrf

            <div class="space-y-6">
                <!-- Tier Selection -->
                <div class="mb-6 p-4 rounded-lg border border-yellow-200 bg-yellow-50">
                    <label class="block text-sm font-semibold mb-2 text-yellow-800">💰 Chọn Pricing Tier:</label>
                    <select 
                        name="pricing_tier_id" 
                        id="pricing_tier_id"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    >
                        <option value="">-- Chọn tier --</option>
                        @if(isset($tiers) && $tiers->count() > 0)
                            @foreach($tiers as $tier)
                                <option value="{{ $tier->id }}" {{ old('pricing_tier_id') == $tier->id ? 'selected' : '' }}>
                                    {{ $tier->name }} ({{ $tier->slug }})
                                    @if($tier->min_orders !== null)
                                        - ≥ {{ number_format($tier->min_orders) }} đơn/tháng
                                    @endif
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <p class="text-xs text-gray-600 mt-2">
                        Chọn tier để set giá. Giá sẽ được áp dụng cho tier đã chọn.
                    </p>
                </div>

                <!-- Smart Filter Section -->
                <div class="mb-6 p-4 rounded-lg border border-blue-200 bg-blue-50">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-semibold text-blue-900">🔍 Bộ lọc thông minh - Chọn variants để set giá</h4>
                        <div class="flex gap-2">
                            <button 
                                type="button" 
                                onclick="selectAllAttributes()" 
                                class="px-3 py-1.5 text-xs font-medium rounded-lg border border-blue-300 bg-white text-blue-700 hover:bg-blue-50 transition-colors"
                            >
                                ✓ Chọn tất cả
                            </button>
                            <button 
                                type="button" 
                                onclick="deselectAllAttributes()" 
                                class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition-colors"
                            >
                                ✗ Bỏ chọn tất cả
                            </button>
                        </div>
                    </div>
                    
                    @if(!empty($attributesByGroup))
                        <div class="space-y-4">
                            @foreach($attributesByGroup as $attrName => $attrValues)
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-sm font-medium text-gray-900">
                                            {{ $attrName }}:
                                        </label>
                                        <div class="flex gap-2">
                                            <button 
                                                type="button" 
                                                onclick="selectAttributeGroup('{{ $attrName }}')" 
                                                class="px-2 py-1 text-xs font-medium rounded border border-blue-300 bg-white text-blue-700 hover:bg-blue-50 transition-colors"
                                            >
                                                ✓ Tất cả
                                            </button>
                                            <button 
                                                type="button" 
                                                onclick="deselectAttributeGroup('{{ $attrName }}')" 
                                                class="px-2 py-1 text-xs font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition-colors"
                                            >
                                                ✗ Bỏ chọn
                                            </button>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($attrValues as $attrValue)
                                            <label class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 bg-white cursor-pointer hover:bg-gray-50 transition-colors">
                                                <input 
                                                    type="checkbox" 
                                                    name="selected_attributes[{{ $attrName }}][]" 
                                                    value="{{ $attrValue }}"
                                                    class="attribute-filter w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                    data-attr-name="{{ $attrName }}"
                                                    onchange="updateVariantPreview()"
                                                >
                                                <span class="text-sm text-gray-700">{{ $attrValue }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-blue-200">
                            <label class="block text-sm font-medium mb-2 text-gray-900">Logic matching:</label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="matching_logic" value="and" checked class="w-4 h-4 text-blue-600 focus:ring-blue-500" onchange="updateVariantPreview()">
                                    <span class="text-sm text-gray-700">AND (Tất cả attributes phải khớp)</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="matching_logic" value="or" class="w-4 h-4 text-blue-600 focus:ring-blue-500" onchange="updateVariantPreview()">
                                    <span class="text-sm text-gray-700">OR (Bất kỳ attribute nào khớp)</span>
                                </label>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-600">Không có attributes để lọc. Tất cả variants sẽ được chọn.</p>
                    @endif
                </div>

                <!-- Preview Section -->
                <div id="variantPreview" class="mb-6 p-4 rounded-lg border border-green-200 bg-green-50 hidden">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm font-medium text-green-800">
                            <span id="previewCount">0</span> variants sẽ được áp dụng giá
                        </p>
                    </div>
                    <div id="previewVariants" class="mt-2 text-xs text-gray-600 max-h-32 overflow-y-auto"></div>
                </div>

                <!-- Price Settings Section -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold mb-3 text-gray-900">💰 Thiết lập giá (đã bao gồm base cost + phí ship + 1 mặt in)</h4>
                    <div class="mb-3 p-3 rounded-lg bg-yellow-50 border border-yellow-200">
                        <p class="text-xs text-yellow-800">
                            <strong>💡 Lưu ý quan trọng:</strong><br>
                            • Giá bạn nhập ở đây đã bao gồm: base cost + phí ship + <strong>1 mặt in</strong><br>
                            • Mỗi variant có 2 loại giá:<br>
                            &nbsp;&nbsp;- <strong>Giá ship by Seller:</strong> Giá đã bao gồm base cost + phí ship by seller + 1 mặt in<br>
                            &nbsp;&nbsp;- <strong>Giá ship by TikTok:</strong> Giá đã bao gồm base cost + phí ship by tiktok + 1 mặt in<br>
                            • Để set giá cho các mặt in thêm (từ mặt 2 trở đi), vui lòng sử dụng "🖨️ Bulk Set Printing Prices"
                        </p>
                    </div>
                    <div id="priceFields" class="space-y-4">
                        @if(isset($markets) && $markets->count() > 0)
                            @foreach($markets as $index => $market)
                                <div class="p-4 rounded-lg border border-gray-200 bg-gray-50 price-field">
                                    <div class="mb-3">
                                        <label class="text-sm font-medium text-gray-900">
                                            {{ $market->name }} ({{ $market->code }}) - {{ $market->currency }}
                                        </label>
                                    </div>
                                    <div class="space-y-3">
                                        <!-- Giá ship by Seller -->
                                        <div>
                                            <label class="block text-xs font-medium mb-1 text-gray-600">Giá ship by Seller (base + ship):</label>
                                            <input 
                                                type="number" 
                                                name="prices[{{ $index }}_seller][base_price]" 
                                                step="0.01" 
                                                min="0"
                                                placeholder="0.00"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                            >
                                            <input type="hidden" name="prices[{{ $index }}_seller][market_id]" value="{{ $market->id }}">
                                            <input type="hidden" name="prices[{{ $index }}_seller][shipping_type]" value="seller">
                                            <input type="hidden" name="prices[{{ $index }}_seller][status]" value="active">
                                        </div>
                                        
                                        <!-- Giá ship by TikTok -->
                                        <div>
                                            <label class="block text-xs font-medium mb-1 text-gray-600">Giá ship by TikTok (base + ship):</label>
                                            <input 
                                                type="number" 
                                                name="prices[{{ $index }}_tiktok][base_price]" 
                                                step="0.01" 
                                                min="0"
                                                placeholder="0.00"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                            >
                                            <input type="hidden" name="prices[{{ $index }}_tiktok][market_id]" value="{{ $market->id }}">
                                            <input type="hidden" name="prices[{{ $index }}_tiktok][shipping_type]" value="tiktok">
                                            <input type="hidden" name="prices[{{ $index }}_tiktok][status]" value="active">
                                        </div>
                                    </div>
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
                        <input 
                            type="checkbox" 
                            name="clear_existing" 
                            value="1"
                            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                        <span class="text-sm text-gray-700">Xóa giá cũ trước khi set giá mới (cho các markets được chọn)</span>
                    </label>
                </div>

                <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
                    <button 
                        type="submit"
                        class="px-6 py-3 rounded-lg font-semibold text-white transition-colors bg-green-500 hover:bg-green-600"
                    >
                        Áp dụng giá
                    </button>
                    <a href="{{ route('admin.products.show', $product) }}" class="px-6 py-3 rounded-lg font-semibold transition-colors border border-gray-300 text-gray-700 hover:bg-gray-100">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

@php
    $variantsForJs = $variants->map(function($variant) {
        return [
            'id' => $variant->id,
            'display_name' => $variant->display_name,
            'attributes' => $variant->attributes->pluck('attribute_value', 'attribute_name')->toArray()
        ];
    })->values()->all();
@endphp

<script>
    const variantsData = @json($variantsForJs ?? []);

    function updateVariantPreview() {
        const form = document.getElementById('bulkPriceForm');
        if (!form) return;
        
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

        const matchingLogic = form.querySelector('input[name="matching_logic"]:checked')?.value || 'and';

        const matchingVariants = variantsData.filter(variant => {
            if (Object.keys(selectedAttributes).length === 0) {
                return false;
            }

            const nonEmptyAttributes = Object.fromEntries(
                Object.entries(selectedAttributes).filter(([key, values]) => values && values.length > 0)
            );

            if (Object.keys(nonEmptyAttributes).length === 0) {
                return false;
            }

            if (matchingLogic === 'and') {
                for (const [attrName, attrValues] of Object.entries(nonEmptyAttributes)) {
                    const variantValue = variant.attributes[attrName];
                    if (!variantValue || !attrValues.includes(variantValue)) {
                        return false;
                    }
                }
                return true;
            } else {
                for (const [attrName, attrValues] of Object.entries(nonEmptyAttributes)) {
                    const variantValue = variant.attributes[attrName];
                    if (variantValue && attrValues.includes(variantValue)) {
                        return true;
                    }
                }
                return false;
            }
        });

        const previewDiv = document.getElementById('variantPreview');
        const previewCount = document.getElementById('previewCount');
        const previewVariants = document.getElementById('previewVariants');

        if (matchingVariants.length > 0) {
            previewDiv.classList.remove('hidden');
            previewCount.textContent = matchingVariants.length;
            
            const displayVariants = matchingVariants.slice(0, 10);
            previewVariants.innerHTML = displayVariants.map(v => v.display_name).join(', ') + 
                (matchingVariants.length > 10 ? ` ... và ${matchingVariants.length - 10} variants khác` : '');
        } else {
            previewDiv.classList.add('hidden');
        }
    }

    function selectAllAttributes() {
        document.querySelectorAll('.attribute-filter').forEach(checkbox => {
            checkbox.checked = true;
        });
        updateVariantPreview();
    }

    function deselectAllAttributes() {
        document.querySelectorAll('.attribute-filter').forEach(checkbox => {
            checkbox.checked = false;
        });
        updateVariantPreview();
    }

    function selectAttributeGroup(attrName) {
        document.querySelectorAll(`.attribute-filter[data-attr-name="${attrName}"]`).forEach(checkbox => {
            checkbox.checked = true;
        });
        updateVariantPreview();
    }

    function deselectAttributeGroup(attrName) {
        document.querySelectorAll(`.attribute-filter[data-attr-name="${attrName}"]`).forEach(checkbox => {
            checkbox.checked = false;
        });
        updateVariantPreview();
    }

    // Initialize
    updateVariantPreview();
</script>
@endsection

@php
    $activeMenu = 'products';
@endphp
