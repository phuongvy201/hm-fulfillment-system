@extends('layouts.admin-dashboard') 

@section('title', 'Bulk Set User Custom Prices - ' . config('app.name', 'Laravel'))

@section('header-title', 'Bulk Set User Custom Prices')
@section('header-subtitle', 'Set custom prices for multiple users and variants')

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

    <form method="POST" action="{{ route('admin.products.variants.user-prices.bulk-store', $product) }}" id="bulkUserPriceForm">
        @csrf

        <div class="space-y-6">
            <!-- Info Section -->
            <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-orange-600">lightbulb</span>
                    <h2 class="text-lg font-semibold text-gray-900">Lưu ý quan trọng</h2>
                </div>
                <div class="p-6">
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <p class="text-sm text-amber-800">
                            <strong>💡 Lưu ý:</strong> Giá bạn nhập ở đây sẽ áp dụng riêng cho các user đã chọn và các variants được filter.<br>
                            Giá này sẽ có độ ưu tiên cao nhất (cao hơn giá tier, giá team, giá mặc định).
                        </p>
                    </div>
                </div>
            </section>

            <!-- User Selection Section -->
            <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-orange-600">people</span>
                        <h2 class="text-lg font-semibold text-gray-900">1. Chọn Users</h2>
                    </div>
                    <div class="flex gap-2">
                        <button 
                            type="button" 
                            onclick="selectAllUsers()" 
                            class="text-xs font-medium px-3 py-1.5 rounded bg-gray-100 hover:bg-gray-200 transition-colors"
                        >
                            ✓ Chọn tất cả
                        </button>
                        <button 
                            type="button" 
                            onclick="deselectAllUsers()" 
                            class="text-xs font-medium px-3 py-1.5 rounded bg-gray-100 hover:bg-gray-200 transition-colors"
                        >
                            ✕ Bỏ chọn tất cả
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div class="max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-3 bg-white">
                        @forelse($users as $userItem)
                            <label class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 cursor-pointer transition-all mb-2 hover:bg-gray-50">
                                <input 
                                    type="checkbox" 
                                    name="user_ids[]" 
                                    value="{{ $userItem->id }}"
                                    class="user-filter w-4 h-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                                    onchange="updateUserPreview()"
                                >
                                <span class="text-sm text-gray-700 flex-1">{{ $userItem->name }}</span>
                                <span class="text-xs text-gray-500">{{ $userItem->email }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-600">Không có users nào.</p>
                        @endforelse
                    </div>
                    <div id="userPreview" class="mt-3 text-sm text-gray-600">
                        <span id="userCount">0</span> users được chọn
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
                                            <label class="block text-xs text-gray-500 mb-1.5">Item 1 (base + ship + 1 mặt in)</label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">{{ $market->currency === 'USD' ? '$' : ($market->currency === 'EUR' ? '€' : ($market->currency === 'GBP' ? '£' : $market->currency . ' ')) }}</span>
                                                <input 
                                                    type="number" 
                                                    name="prices[{{ $index }}_seller][price]" 
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
                                            <label class="block text-xs text-gray-500 mb-1.5">Item 1 (base + ship + 1 mặt in)</label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">{{ $market->currency === 'USD' ? '$' : ($market->currency === 'EUR' ? '€' : ($market->currency === 'GBP' ? '£' : $market->currency . ' ')) }}</span>
                                                <input 
                                                    type="number" 
                                                    name="prices[{{ $index }}_tiktok][price]" 
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
                                
                                <!-- Valid From/To Dates -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1.5">Valid From:</label>
                                        <div class="space-y-2">
                                            <input 
                                                type="date" 
                                                name="prices[{{ $index }}_seller][valid_from]" 
                                                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-all"
                                            >
                                            <input 
                                                type="date" 
                                                name="prices[{{ $index }}_tiktok][valid_from]" 
                                                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-all"
                                            >
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1.5">Valid To:</label>
                                        <div class="space-y-2">
                                            <input 
                                                type="date" 
                                                name="prices[{{ $index }}_seller][valid_to]" 
                                                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-all"
                                            >
                                            <input 
                                                type="date" 
                                                name="prices[{{ $index }}_tiktok][valid_to]" 
                                                class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-all"
                                            >
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
                    
                    <div class="pt-4 border-t border-gray-100">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input 
                                type="checkbox" 
                                name="clear_existing" 
                                value="1"
                                class="w-5 h-5 rounded border-gray-300 text-orange-600 focus:ring-orange-500 focus:ring-offset-0"
                            >
                            <span class="text-sm text-gray-600 group-hover:text-gray-900 transition-colors">Xóa giá cũ trước khi set giá mới (cho các users và markets được chọn)</span>
                        </label>
                    </div>
                </div>
                <div class="p-6 bg-gray-50 border-t border-gray-100 flex flex-col sm:flex-row gap-4 items-center justify-between">
                    <div class="flex-1"></div>
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

    function selectAllUsers() {
        document.querySelectorAll('.user-filter').forEach(checkbox => {
            checkbox.checked = true;
        });
        updateUserPreview();
    }

    function deselectAllUsers() {
        document.querySelectorAll('.user-filter').forEach(checkbox => {
            checkbox.checked = false;
        });
        updateUserPreview();
    }

    function updateUserPreview() {
        const checked = document.querySelectorAll('.user-filter:checked').length;
        document.getElementById('userCount').textContent = checked;
    }

    function updateVariantPreview() {
        const form = document.getElementById('bulkUserPriceForm');
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
                return true; // No filter, select all
            }

            const nonEmptyAttributes = Object.fromEntries(
                Object.entries(selectedAttributes).filter(([key, values]) => values && values.length > 0)
            );

            if (Object.keys(nonEmptyAttributes).length === 0) {
                return true; // No valid filters, select all
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
    updateUserPreview();
    updateVariantPreview();
</script>
@endsection

@php
    $activeMenu = 'products';
@endphp
