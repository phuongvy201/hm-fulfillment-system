@extends('layouts.admin-dashboard') 

@section('title', 'Bulk Set Prices by Tier - ' . config('app.name', 'Laravel'))

@section('header-title', 'Bulk Set Prices by Tier')
@section('header-subtitle', 'Set prices for multiple variants by pricing tier')

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

    <form method="POST" action="{{ route('admin.products.variants.bulk-prices.store', $product) }}" id="bulkPriceForm">
        @csrf

        <div class="space-y-6">
            <!-- Tier Selection Section -->
            <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-orange-600">settings</span>
                    <h2 class="text-lg font-semibold text-gray-900">1. Cấu hình chung</h2>
                </div>
                <div class="p-6 space-y-6">
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 flex gap-3">
                        <span class="material-symbols-outlined text-amber-600 shrink-0">lightbulb</span>
                        <div class="text-sm text-amber-800">
                            <p class="font-semibold">Lưu ý quan trọng:</p>
                            <ul class="list-disc ml-4 mt-1 space-y-1">
                                <li>Giá bạn nhập sẽ áp dụng cho các variants được filter và tier đã chọn.</li>
                                <li>Giá này đã bao gồm: <strong>base cost + phí ship + 1 mặt in</strong>.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="max-w-md">
                        <label class="block text-sm font-medium mb-2 text-gray-700">Chọn Pricing Tier:</label>
                        <div class="relative">
                            <select 
                                name="pricing_tier_id" 
                                id="pricing_tier_id"
                                required
                                class="w-full bg-white border border-gray-300 rounded-lg py-2.5 pl-4 pr-10 appearance-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-all"
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
                            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 italic">Chọn tier để set giá. Giá sẽ được áp dụng cho tier đã chọn.</p>
                    </div>
                </div>
            </section>

            <!-- Smart Filter Section -->
            <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-orange-600">filter_alt</span>
                        <h2 class="text-lg font-semibold text-gray-900">2. Bộ lọc thông minh</h2>
                    </div>
                    <div class="flex gap-2">
                        <button 
                            type="button" 
                            onclick="selectAllAttributes()" 
                            class="text-xs font-medium px-3 py-1.5 rounded bg-gray-100 hover:bg-gray-200 transition-colors"
                        >
                            ✓ Chọn tất cả
                        </button>
                        <button 
                            type="button" 
                            onclick="deselectAllAttributes()" 
                            class="text-xs font-medium px-3 py-1.5 rounded bg-gray-100 hover:bg-gray-200 transition-colors"
                        >
                            ✕ Bỏ chọn tất cả
                        </button>
                    </div>
                </div>
                <div class="p-6 space-y-8">
                    @if(!empty($attributesByGroup))
                        @foreach($attributesByGroup as $attrName => $attrValues)
                            @php
                                $attrKey = strtolower($attrName);
                                $isColor = str_contains($attrKey, 'color');
                                $isSize = str_contains($attrKey, 'size');
                            @endphp
                            <div>
                                <div class="flex items-center justify-between mb-4">
                                    <label class="text-sm font-semibold uppercase tracking-wider text-gray-500">{{ $attrName }}</label>
                                    <div class="space-x-4">
                                        <button 
                                            type="button" 
                                            onclick="selectAttributeGroup('{{ $attrName }}')" 
                                            class="text-xs text-orange-600 hover:underline"
                                        >
                                            Tất cả
                                        </button>
                                        <button 
                                            type="button" 
                                            onclick="deselectAttributeGroup('{{ $attrName }}')" 
                                            class="text-xs text-gray-400 hover:underline"
                                        >
                                            Bỏ chọn
                                        </button>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($attrValues as $attrValue)
                                        @php
                                            $uniqueId = 'attr_' . md5($attrName . '_' . $attrValue);
                                        @endphp
                                        <input 
                                            type="checkbox" 
                                            id="{{ $uniqueId }}"
                                            name="selected_attributes[{{ $attrName }}][]" 
                                            value="{{ $attrValue }}"
                                            class="hidden chip-checkbox attribute-filter"
                                            data-attr-name="{{ $attrName }}"
                                            onchange="updateVariantPreview()"
                                        >
                                        <label 
                                            for="{{ $uniqueId }}"
                                            class="px-4 py-2 rounded-{{ $isColor ? 'full' : 'lg' }} border border-gray-200 text-sm cursor-pointer hover:border-orange-500 transition-all flex items-center gap-2 {{ $isSize ? 'min-w-[50px] text-center' : '' }}"
                                        >
                                            @if($isColor)
                                                <span class="w-3 h-3 rounded-full bg-gray-300"></span>
                                            @endif
                                            {{ $attrValue }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                        
                        <div class="pt-4 border-t border-gray-100">
                            <label class="block text-sm font-medium mb-2 text-gray-900">Logic matching:</label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="matching_logic" value="and" checked class="w-4 h-4 text-orange-600 focus:ring-orange-500" onchange="updateVariantPreview()">
                                    <span class="text-sm text-gray-700">AND (Tất cả attributes phải khớp)</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="matching_logic" value="or" class="w-4 h-4 text-orange-600 focus:ring-orange-500" onchange="updateVariantPreview()">
                                    <span class="text-sm text-gray-700">OR (Bất kỳ attribute nào khớp)</span>
                                </label>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-600">Không có attributes để lọc. Tất cả variants sẽ được chọn.</p>
                    @endif
                </div>
            </section>

            <!-- Preview Section -->
            <div id="variantPreview" class="bg-green-50 border border-green-200 rounded-xl p-4 hidden">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-green-600">check_circle</span>
                    <p class="text-sm font-medium text-green-800">
                        <span id="previewCount">0</span> variants sẽ được áp dụng giá
                    </p>
                </div>
                <div id="previewVariants" class="mt-2 text-xs text-gray-600 max-h-32 overflow-y-auto"></div>
            </div>

            <!-- Price Settings Section -->
            <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-orange-600">edit_note</span>
                    <h2 class="text-lg font-semibold text-gray-900">3. Thiết lập giá</h2>
                </div>
                <div class="p-6 space-y-8">
                    @if(isset($markets) && $markets->count() > 0)
                        @foreach($markets as $index => $market)
                            <div class="space-y-4">
                                <div class="mb-2">
                                    <label class="text-sm font-medium text-gray-900">
                                        {{ $market->name }} ({{ $market->code }}) - {{ $market->currency }}
                                    </label>
                                </div>
                                <!-- Ship by Seller -->
                                <div class="space-y-4">
                                    <div class="flex items-center gap-2 text-gray-700 font-medium">
                                        <span class="material-symbols-outlined text-blue-500">local_shipping</span>
                                        Ship by Seller
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1.5">Item 1 (base + ship)</label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">{{ $market->currency === 'USD' ? '$' : ($market->currency === 'EUR' ? '€' : ($market->currency === 'GBP' ? '£' : $market->currency . ' ')) }}</span>
                                                <input 
                                                    type="number" 
                                                    name="prices[{{ $index }}_seller][base_price]" 
                                                    step="0.01" 
                                                    min="0"
                                                    placeholder="0.00"
                                                    class="w-full bg-white border border-gray-200 rounded-lg py-2.5 pl-7 pr-4 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-all"
                                                >
                                            </div>
                                            <input type="hidden" name="prices[{{ $index }}_seller][market_id]" value="{{ $market->id }}">
                                            <input type="hidden" name="prices[{{ $index }}_seller][shipping_type]" value="seller">
                                            <input type="hidden" name="prices[{{ $index }}_seller][status]" value="active">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1.5">Item 2+ (trừ phí label)</label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">{{ $market->currency === 'USD' ? '$' : ($market->currency === 'EUR' ? '€' : ($market->currency === 'GBP' ? '£' : $market->currency . ' ')) }}</span>
                                                <input 
                                                    type="number" 
                                                    name="prices[{{ $index }}_seller][additional_item_price]" 
                                                    step="0.01" 
                                                    min="0"
                                                    placeholder="0.00"
                                                    class="w-full bg-white border border-gray-200 rounded-lg py-2.5 pl-7 pr-4 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-all"
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="h-px bg-gray-100"></div>
                                
                                <!-- Ship by TikTok -->
                                <div class="space-y-4">
                                    <div class="flex items-center gap-2 text-gray-700 font-medium">
                                        <span class="material-symbols-outlined text-gray-900">bolt</span>
                                        Ship by TikTok
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1.5">Item 1 (base + ship)</label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">{{ $market->currency === 'USD' ? '$' : ($market->currency === 'EUR' ? '€' : ($market->currency === 'GBP' ? '£' : $market->currency . ' ')) }}</span>
                                                <input 
                                                    type="number" 
                                                    name="prices[{{ $index }}_tiktok][base_price]" 
                                                    step="0.01" 
                                                    min="0"
                                                    placeholder="0.00"
                                                    class="w-full bg-white border border-gray-200 rounded-lg py-2.5 pl-7 pr-4 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-all"
                                                >
                                            </div>
                                            <input type="hidden" name="prices[{{ $index }}_tiktok][market_id]" value="{{ $market->id }}">
                                            <input type="hidden" name="prices[{{ $index }}_tiktok][shipping_type]" value="tiktok">
                                            <input type="hidden" name="prices[{{ $index }}_tiktok][status]" value="active">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1.5">Item 2+ (trừ phí label)</label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">{{ $market->currency === 'USD' ? '$' : ($market->currency === 'EUR' ? '€' : ($market->currency === 'GBP' ? '£' : $market->currency . ' ')) }}</span>
                                                <input 
                                                    type="number" 
                                                    name="prices[{{ $index }}_tiktok][additional_item_price]" 
                                                    step="0.01" 
                                                    min="0"
                                                    placeholder="0.00"
                                                    class="w-full bg-white border border-gray-200 rounded-lg py-2.5 pl-7 pr-4 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-all"
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                @if(!$loop->last)
                                    <div class="h-px bg-gray-100"></div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <p class="text-sm text-gray-600">Không có markets nào.</p>
                    @endif
                    
                    <div class="pt-4">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input 
                                type="checkbox" 
                                name="clear_existing" 
                                value="1"
                                class="w-5 h-5 rounded border-gray-300 text-orange-600 focus:ring-orange-500 focus:ring-offset-0"
                            >
                            <span class="text-sm text-gray-600 group-hover:text-gray-900 transition-colors">Xóa giá cũ trước khi set giá mới (cho các markets được chọn)</span>
                        </label>
                    </div>
                </div>
                <div class="p-6 bg-gray-50 border-t border-gray-100 flex flex-col sm:flex-row gap-4 items-center justify-between">
                    <p class="text-sm text-gray-500 flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">print</span>
                        Để set giá cho mặt in thêm, vui lòng dùng "Bulk Set Printing Prices"
                    </p>
                    <div class="flex gap-3 w-full sm:w-auto">
                        <a href="{{ route('admin.products.show', $product) }}" class="flex-1 sm:flex-none px-6 py-2.5 rounded-lg font-semibold border border-gray-200 hover:bg-white transition-all text-center">
                            Hủy
                        </a>
                        <button 
                            type="submit"
                            class="flex-1 sm:flex-none px-8 py-2.5 rounded-lg font-semibold bg-orange-600 text-white hover:bg-orange-700 shadow-lg shadow-orange-600/20 transition-all transform active:scale-[0.98]"
                        >
                            Apply Price
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </form>
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

    // Update chip styling on checkbox change
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.chip-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const label = document.querySelector(`label[for="${this.id}"]`);
                if (this.checked) {
                    label.classList.add('border-orange-500', 'bg-orange-50');
                } else {
                    label.classList.remove('border-orange-500', 'bg-orange-50');
                }
            });
        });
    });

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
            const label = document.querySelector(`label[for="${checkbox.id}"]`);
            if (label) {
                label.classList.add('border-orange-500', 'bg-orange-50');
            }
        });
        updateVariantPreview();
    }

    function deselectAllAttributes() {
        document.querySelectorAll('.attribute-filter').forEach(checkbox => {
            checkbox.checked = false;
            const label = document.querySelector(`label[for="${checkbox.id}"]`);
            if (label) {
                label.classList.remove('border-orange-500', 'bg-orange-50');
            }
        });
        updateVariantPreview();
    }

    function selectAttributeGroup(attrName) {
        document.querySelectorAll(`.attribute-filter[data-attr-name="${attrName}"]`).forEach(checkbox => {
            checkbox.checked = true;
            const label = document.querySelector(`label[for="${checkbox.id}"]`);
            if (label) {
                label.classList.add('border-orange-500', 'bg-orange-50');
            }
        });
        updateVariantPreview();
    }

    function deselectAttributeGroup(attrName) {
        document.querySelectorAll(`.attribute-filter[data-attr-name="${attrName}"]`).forEach(checkbox => {
            checkbox.checked = false;
            const label = document.querySelector(`label[for="${checkbox.id}"]`);
            if (label) {
                label.classList.remove('border-orange-500', 'bg-orange-50');
            }
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
