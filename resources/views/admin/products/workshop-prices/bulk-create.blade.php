@extends('layouts.app')

@section('title', 'Set Workshop Prices - ' . $product->name . ' - ' . config('app.name', 'Laravel'))

@section('header-title', 'Set Workshop Prices')
@section('header-subtitle', $product->name . ' - ' . $workshop->name)

@section('header-actions')
<a href="{{ route('admin.products.show', $product) }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
    ← Back to Product
</a>
@endsection

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <form method="POST" action="{{ route('admin.products.workshop-prices.bulk-store', $product) }}">
            @csrf

            <div class="px-6 pt-6 pb-4">
                <div class="mb-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">🏭 Bulk Set Workshop Prices</h3>
                    <div class="p-4 rounded-lg" style="background-color: #EFF6FF; border: 1px solid #DBEAFE;">
                        <p class="text-sm" style="color: #1E40AF;">
                            <strong>Workshop:</strong> {{ $workshop->name }} ({{ $workshop->code }})<br>
                            @if($market)
                            <strong>Market:</strong> {{ $market->name }} ({{ $market->code }}) - Currency: <strong>{{ $currency }}</strong>
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

                <!-- Smart Filter Section -->
                <div class="mb-6 p-4 rounded-lg border" style="border-color: #DBEAFE; background-color: #EFF6FF;">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-semibold" style="color: #1E40AF;">🔍 Bộ lọc thông minh - Chọn variants để set giá workshop</h4>
                        <div class="flex gap-2">
                            <button 
                                type="button" 
                                onclick="selectAllAttributes()" 
                                class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-all"
                                style="color: #2563EB; border-color: #DBEAFE; background-color: #EFF6FF;"
                                onmouseover="this.style.backgroundColor='#DBEAFE';"
                                onmouseout="this.style.backgroundColor='#EFF6FF';"
                            >
                                ✓ Chọn tất cả
                            </button>
                            <button 
                                type="button" 
                                onclick="deselectAllAttributes()" 
                                class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-all"
                                style="color: #6B7280; border-color: #D1D5DB; background-color: #FFFFFF;"
                                onmouseover="this.style.backgroundColor='#F3F4F6';"
                                onmouseout="this.style.backgroundColor='#FFFFFF';"
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
                                        <label class="block text-sm font-medium" style="color: #111827;">
                                            {{ $attrName }}:
                                        </label>
                                        <div class="flex gap-2">
                                            <button 
                                                type="button" 
                                                onclick="selectAttributeGroup('{{ $attrName }}')" 
                                                class="px-2 py-1 text-xs font-medium rounded border transition-all"
                                                style="color: #2563EB; border-color: #DBEAFE; background-color: #EFF6FF;"
                                                onmouseover="this.style.backgroundColor='#DBEAFE';"
                                                onmouseout="this.style.backgroundColor='#EFF6FF';"
                                            >
                                                ✓ Tất cả
                                            </button>
                                            <button 
                                                type="button" 
                                                onclick="deselectAttributeGroup('{{ $attrName }}')" 
                                                class="px-2 py-1 text-xs font-medium rounded border transition-all"
                                                style="color: #6B7280; border-color: #D1D5DB; background-color: #FFFFFF;"
                                                onmouseover="this.style.backgroundColor='#F3F4F6';"
                                                onmouseout="this.style.backgroundColor='#FFFFFF';"
                                            >
                                                ✗ Bỏ chọn
                                            </button>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($attrValues as $attrValue)
                                            <label class="flex items-center gap-2 px-3 py-2 rounded-lg border cursor-pointer transition-all" style="border-color: #D1D5DB; background-color: #FFFFFF;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='#FFFFFF';">
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
                        
                        <div class="mt-4 pt-4 border-t" style="border-color: #DBEAFE;">
                            <label class="block text-sm font-medium mb-2" style="color: #111827;">Logic matching:</label>
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
                <div id="variantPreview" class="mb-6 p-4 rounded-lg border hidden" style="border-color: #D1FAE5; background-color: #ECFDF5;">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" style="color: #059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm font-medium" style="color: #059669;">
                            <span id="previewCount">0</span> variants sẽ được áp dụng giá workshop
                        </p>
                    </div>
                    <div id="previewVariants" class="mt-2 text-xs text-gray-600 max-h-32 overflow-y-auto"></div>
                </div>

                <!-- Price Settings Section -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold mb-3" style="color: #111827;">💰 Thiết lập giá workshop (base price)</h4>
                    <div class="mb-3 p-3 rounded-lg" style="background-color: #FEF3C7; border: 1px solid #FCD34D;">
                        <p class="text-xs" style="color: #92400E;">
                            <strong>💡 Lưu ý:</strong><br>
                            • Giá workshop là giá cơ bản mà workshop tính cho sản phẩm/variant này<br>
                            • Giá này không bao gồm phí ship hay giá in<br>
                            • Mỗi variant có 2 loại giá:<br>
                            &nbsp;&nbsp;- <strong>Giá ship by Seller:</strong> Giá workshop khi ship by seller<br>
                            &nbsp;&nbsp;- <strong>Giá ship by TikTok:</strong> Giá workshop khi ship by tiktok<br>
                            • Currency sẽ tự động lấy từ market của workshop ({{ $currency }})
                        </p>
                    </div>
                    <div class="space-y-4">
                        <!-- Giá ship by Seller -->
                        <div>
                            <label class="block text-xs font-medium mb-1" style="color: #6B7280;">Giá workshop ship by Seller ({{ $currency }}):</label>
                            <input 
                                type="number" 
                                name="prices[seller][base_price]" 
                                step="0.01" 
                                min="0"
                                placeholder="0.00"
                                value="{{ old('prices.seller.base_price') }}"
                                class="w-full px-3 py-2 border rounded-lg text-sm"
                                style="border-color: #D1D5DB;"
                            >
                        </div>
                        
                        <!-- Giá ship by TikTok -->
                        <div>
                            <label class="block text-xs font-medium mb-1" style="color: #6B7280;">Giá workshop ship by TikTok ({{ $currency }}):</label>
                            <input 
                                type="number" 
                                name="prices[tiktok][base_price]" 
                                step="0.01" 
                                min="0"
                                placeholder="0.00"
                                value="{{ old('prices.tiktok.base_price') }}"
                                class="w-full px-3 py-2 border rounded-lg text-sm"
                                style="border-color: #D1D5DB;"
                            >
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium mb-1" style="color: #6B7280;">Status:</label>
                            <select 
                                name="status" 
                                class="w-full px-3 py-2 border rounded-lg text-sm"
                                style="border-color: #D1D5DB;"
                                required
                            >
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium mb-1" style="color: #6B7280;">Valid From:</label>
                                <input 
                                    type="date" 
                                    name="valid_from" 
                                    value="{{ old('valid_from') }}"
                                    class="w-full px-3 py-2 border rounded-lg text-sm"
                                    style="border-color: #D1D5DB;"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1" style="color: #6B7280;">Valid To:</label>
                                <input 
                                    type="date" 
                                    name="valid_to" 
                                    value="{{ old('valid_to') }}"
                                    class="w-full px-3 py-2 border rounded-lg text-sm"
                                    style="border-color: #D1D5DB;"
                                >
                            </div>
                        </div>
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
                        <span class="text-sm text-gray-700">Xóa giá workshop cũ trước khi set giá mới (cho các variants được chọn)</span>
                    </label>
                </div>
            </div>

            <div class="px-6 py-4 border-t bg-gray-50 flex items-center justify-end gap-3" style="border-color: #E5E7EB;">
                <a href="{{ route('admin.products.show', $product) }}" class="px-4 py-2 rounded-lg text-sm font-medium transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
                    Hủy
                </a>
                <button 
                    type="submit" 
                    class="px-6 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm"
                    style="background-color: #10B981;"
                    onmouseover="this.style.backgroundColor='#059669';"
                    onmouseout="this.style.backgroundColor='#10B981';"
                >
                    Áp dụng giá workshop
                </button>
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
        const checkboxes = document.querySelectorAll('.attribute-filter');
        checkboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        updateVariantPreview();
    }

    function deselectAllAttributes() {
        const checkboxes = document.querySelectorAll('.attribute-filter');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        updateVariantPreview();
    }

    function selectAttributeGroup(attrName) {
        const checkboxes = document.querySelectorAll(`.attribute-filter[data-attr-name="${attrName}"]`);
        checkboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        updateVariantPreview();
    }

    function deselectAttributeGroup(attrName) {
        const checkboxes = document.querySelectorAll(`.attribute-filter[data-attr-name="${attrName}"]`);
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
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

