@extends('layouts.admin-dashboard') 

@section('title', 'Set Workshop Prices - ' . $product->name . ' - ' . config('app.name', 'Laravel'))

@section('header-title', 'Set Workshop Prices')
@section('header-subtitle', $product->name . ' - ' . $workshop->name)

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

    <form method="POST" action="{{ route('admin.products.workshop-prices.bulk-store', $product) }}">
        @csrf
        <input type="hidden" name="workshop_id" value="{{ $workshop->id }}">
        @if($market)
        <input type="hidden" name="market_id" value="{{ $market->id }}">
        @endif

        <div class="space-y-6">
            <!-- Info Section -->
            <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-orange-600">info</span>
                    <h2 class="text-lg font-semibold text-gray-900">Thông tin</h2>
                </div>
                <div class="p-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-sm text-blue-900">
                            <strong>Workshop:</strong> {{ $workshop->name }} ({{ $workshop->code }})<br>
                            @if($market)
                            <strong>Market:</strong> {{ $market->name }} ({{ $market->code }}) - Currency: <strong>{{ $currency }}</strong>
                            @endif
                        </p>
                    </div>
                </div>
            </section>

            <!-- Smart Filter Section -->
            <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-orange-600">filter_alt</span>
                        <h2 class="text-lg font-semibold text-gray-900">1. Bộ lọc thông minh</h2>
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
                        <span id="previewCount">0</span> variants sẽ được áp dụng giá workshop
                    </p>
                </div>
                <div id="previewVariants" class="mt-2 text-xs text-gray-600 max-h-32 overflow-y-auto"></div>
            </div>

            <!-- Price Settings Section -->
            <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-orange-600">edit_note</span>
                    <h2 class="text-lg font-semibold text-gray-900">2. Thiết lập giá workshop ({{ $currency }})</h2>
                </div>
                <div class="p-6 space-y-6">
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <p class="text-sm text-amber-800">
                            <strong>💡 Lưu ý:</strong><br>
                            • Giá workshop là giá cơ bản mà workshop tính cho sản phẩm/variant này<br>
                            • Giá này không bao gồm phí ship hay giá in<br>
                            • Mỗi variant có 2 loại giá:<br>
                            &nbsp;&nbsp;- <strong>Giá ship by Seller:</strong> Giá workshop khi ship by seller<br>
                            &nbsp;&nbsp;- <strong>Giá ship by TikTok:</strong> Giá workshop khi ship by tiktok<br>
                            • Currency sẽ tự động lấy từ market của workshop ({{ $currency }})
                        </p>
                    </div>

                    <div class="space-y-6">
                        <!-- Giá ship by Seller -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 text-gray-700 font-medium">
                                <span class="material-symbols-outlined text-blue-500">local_shipping</span>
                                Ship by Seller
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1.5">Base Price</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">{{ $currency === 'USD' ? '$' : ($currency === 'EUR' ? '€' : ($currency === 'VND' ? '₫' : $currency . ' ')) }}</span>
                                    <input 
                                        type="number" 
                                        name="prices[seller][base_price]" 
                                        step="0.01" 
                                        min="0"
                                        placeholder="0.00"
                                        value="{{ old('prices.seller.base_price') }}"
                                        class="w-full bg-white border border-gray-200 rounded-lg py-2.5 pl-7 pr-4 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-all"
                                    >
                                    <input type="hidden" name="prices[seller][shipping_type]" value="seller">
                                </div>
                            </div>
                        </div>
                        
                        <div class="h-px bg-gray-100"></div>
                        
                        <!-- Giá ship by TikTok -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 text-gray-700 font-medium">
                                <span class="material-symbols-outlined text-gray-900">bolt</span>
                                Ship by TikTok
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1.5">Base Price</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">{{ $currency === 'USD' ? '$' : ($currency === 'EUR' ? '€' : ($currency === 'VND' ? '₫' : $currency . ' ')) }}</span>
                                    <input 
                                        type="number" 
                                        name="prices[tiktok][base_price]" 
                                        step="0.01" 
                                        min="0"
                                        placeholder="0.00"
                                        value="{{ old('prices.tiktok.base_price') }}"
                                        class="w-full bg-white border border-gray-200 rounded-lg py-2.5 pl-7 pr-4 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-all"
                                    >
                                    <input type="hidden" name="prices[tiktok][shipping_type]" value="tiktok">
                                </div>
                            </div>
                        </div>
                        
                        <div class="h-px bg-gray-100"></div>
                        
                        <!-- Status và Valid Dates -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1.5">Status</label>
                                <div class="relative">
                                    <select 
                                        name="status" 
                                        class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-all appearance-none"
                                        required
                                    >
                                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1.5">Valid From:</label>
                                    <input 
                                        type="date" 
                                        name="valid_from" 
                                        value="{{ old('valid_from') }}"
                                        class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-all"
                                    >
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1.5">Valid To:</label>
                                    <input 
                                        type="date" 
                                        name="valid_to" 
                                        value="{{ old('valid_to') }}"
                                        class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-all"
                                    >
                                </div>
                            </div>
                        </div>
                        
                        <div class="pt-4 border-t border-gray-100">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input 
                                    type="checkbox" 
                                    name="clear_existing" 
                                    value="1"
                                    class="w-5 h-5 rounded border-gray-300 text-orange-600 focus:ring-orange-500 focus:ring-offset-0"
                                >
                                <span class="text-sm text-gray-600 group-hover:text-gray-900 transition-colors">Xóa giá workshop cũ trước khi set giá mới (cho các variants được chọn)</span>
                            </label>
                        </div>
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
                            Apply Workshop Price
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
        const form = document.querySelector('form');
        
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

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateVariantPreview();
    });
</script>
@endsection

@php
    $activeMenu = 'products';
@endphp
