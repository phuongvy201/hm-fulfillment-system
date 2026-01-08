@extends('layouts.app')

@section('title', 'Bulk Set User Custom Prices - ' . config('app.name', 'Laravel'))

@section('header-title', 'Bulk Set User Custom Prices')
@section('header-subtitle', 'Set custom prices for multiple users and variants')

@section('header-actions')
<a href="{{ route('admin.products.show', $product) }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
    ← Back to Product
</a>
@endsection

@section('content')
<div class="max-w-6xl">
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

        <div class="mb-6 p-4 rounded-lg" style="background-color: #FEF3C7; border: 1px solid #FCD34D;">
            <p class="text-sm" style="color: #92400E;">
                <strong>💡 Lưu ý:</strong> Giá bạn nhập ở đây sẽ áp dụng riêng cho các user đã chọn và các variants được filter.<br>
                Giá này sẽ có độ ưu tiên cao nhất (cao hơn giá tier, giá team, giá mặc định).
            </p>
        </div>

        <form method="POST" action="{{ route('admin.products.variants.user-prices.bulk-store', $product) }}" id="bulkUserPriceForm">
            @csrf

            <div class="space-y-6">
                <!-- User Selection -->
                <div class="mb-6 p-4 rounded-lg border" style="border-color: #DBEAFE; background-color: #EFF6FF;">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-semibold" style="color: #1E40AF;">👥 Chọn Users</h4>
                        <div class="flex gap-2">
                            <button 
                                type="button" 
                                onclick="selectAllUsers()" 
                                class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-all"
                                style="color: #2563EB; border-color: #DBEAFE; background-color: #EFF6FF;"
                                onmouseover="this.style.backgroundColor='#DBEAFE';"
                                onmouseout="this.style.backgroundColor='#EFF6FF';"
                            >
                                ✓ Chọn tất cả
                            </button>
                            <button 
                                type="button" 
                                onclick="deselectAllUsers()" 
                                class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-all"
                                style="color: #6B7280; border-color: #D1D5DB; background-color: #FFFFFF;"
                                onmouseover="this.style.backgroundColor='#F3F4F6';"
                                onmouseout="this.style.backgroundColor='#FFFFFF';"
                            >
                                ✗ Bỏ chọn tất cả
                            </button>
                        </div>
                    </div>
                    <div class="max-h-48 overflow-y-auto border rounded-lg p-3" style="border-color: #D1D5DB; background-color: #FFFFFF;">
                        @forelse($users as $userItem)
                            <label class="flex items-center gap-2 px-3 py-2 rounded-lg border cursor-pointer transition-all mb-2" style="border-color: #D1D5DB; background-color: #FFFFFF;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='#FFFFFF';">
                                <input 
                                    type="checkbox" 
                                    name="user_ids[]" 
                                    value="{{ $userItem->id }}"
                                    class="user-filter w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
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

                <!-- Smart Filter Section -->
                <div class="mb-6 p-4 rounded-lg border" style="border-color: #DBEAFE; background-color: #EFF6FF;">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-semibold" style="color: #1E40AF;">🔍 Bộ lọc thông minh - Chọn variants để set giá</h4>
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
                            <span id="previewCount">0</span> variants sẽ được áp dụng giá
                        </p>
                    </div>
                    <div id="previewVariants" class="mt-2 text-xs text-gray-600 max-h-32 overflow-y-auto"></div>
                </div>

                <!-- Price Settings Section -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold mb-3" style="color: #111827;">💰 Thiết lập giá riêng cho users (đã bao gồm base cost + phí ship + 1 mặt in)</h4>
                    <div class="mb-3 p-3 rounded-lg" style="background-color: #FEF3C7; border: 1px solid #FCD34D;">
                        <p class="text-xs" style="color: #92400E;">
                            <strong>💡 Lưu ý quan trọng:</strong><br>
                            • Giá bạn nhập ở đây đã bao gồm: base cost + phí ship + <strong>1 mặt in</strong><br>
                            • Mỗi variant có 2 loại giá:<br>
                            &nbsp;&nbsp;- <strong>Giá ship by Seller:</strong> Giá đã bao gồm base cost + phí ship by seller + 1 mặt in<br>
                            &nbsp;&nbsp;- <strong>Giá ship by TikTok:</strong> Giá đã bao gồm base cost + phí ship by tiktok + 1 mặt in
                        </p>
                    </div>
                    <div id="priceFields" class="space-y-4">
                        @if(isset($markets) && $markets->count() > 0)
                            @foreach($markets as $index => $market)
                                <div class="p-4 rounded-lg border price-field" style="border-color: #DBEAFE; background-color: #F9FAFB;">
                                    <div class="mb-3">
                                        <label class="text-sm font-medium" style="color: #111827;">
                                            {{ $market->name }} ({{ $market->code }}) - {{ $market->currency }}
                                        </label>
                                    </div>
                                    <div class="space-y-3">
                                        <!-- Giá ship by Seller -->
                                        <div>
                                            <label class="block text-xs font-medium mb-1" style="color: #6B7280;">Giá ship by Seller (base + ship + 1 mặt in):</label>
                                            <input 
                                                type="number" 
                                                name="prices[{{ $index }}_seller][price]" 
                                                step="0.01" 
                                                min="0"
                                                placeholder="0.00"
                                                class="w-full px-3 py-2 border rounded-lg text-sm"
                                                style="border-color: #D1D5DB;"
                                            >
                                            <input type="hidden" name="prices[{{ $index }}_seller][market_id]" value="{{ $market->id }}">
                                            <input type="hidden" name="prices[{{ $index }}_seller][shipping_type]" value="seller">
                                            <input type="hidden" name="prices[{{ $index }}_seller][status]" value="active">
                                        </div>
                                        
                                        <!-- Giá ship by TikTok -->
                                        <div>
                                            <label class="block text-xs font-medium mb-1" style="color: #6B7280;">Giá ship by TikTok (base + ship + 1 mặt in):</label>
                                            <input 
                                                type="number" 
                                                name="prices[{{ $index }}_tiktok][price]" 
                                                step="0.01" 
                                                min="0"
                                                placeholder="0.00"
                                                class="w-full px-3 py-2 border rounded-lg text-sm"
                                                style="border-color: #D1D5DB;"
                                            >
                                            <input type="hidden" name="prices[{{ $index }}_tiktok][market_id]" value="{{ $market->id }}">
                                            <input type="hidden" name="prices[{{ $index }}_tiktok][shipping_type]" value="tiktok">
                                            <input type="hidden" name="prices[{{ $index }}_tiktok][status]" value="active">
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-xs font-medium mb-1" style="color: #6B7280;">Valid From:</label>
                                                <input 
                                                    type="date" 
                                                    name="prices[{{ $index }}_seller][valid_from]" 
                                                    class="w-full px-3 py-2 border rounded-lg text-sm mb-2"
                                                    style="border-color: #D1D5DB;"
                                                >
                                                <input 
                                                    type="date" 
                                                    name="prices[{{ $index }}_tiktok][valid_from]" 
                                                    class="w-full px-3 py-2 border rounded-lg text-sm"
                                                    style="border-color: #D1D5DB;"
                                                >
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium mb-1" style="color: #6B7280;">Valid To:</label>
                                                <input 
                                                    type="date" 
                                                    name="prices[{{ $index }}_seller][valid_to]" 
                                                    class="w-full px-3 py-2 border rounded-lg text-sm mb-2"
                                                    style="border-color: #D1D5DB;"
                                                >
                                                <input 
                                                    type="date" 
                                                    name="prices[{{ $index }}_tiktok][valid_to]" 
                                                    class="w-full px-3 py-2 border rounded-lg text-sm"
                                                    style="border-color: #D1D5DB;"
                                                >
                                            </div>
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
                        <span class="text-sm text-gray-700">Xóa giá cũ trước khi set giá mới (cho các users và markets được chọn)</span>
                    </label>
                </div>

                <div class="flex items-center gap-4 pt-4 border-t" style="border-color: #E5E7EB;">
                    <button 
                        type="submit"
                        class="px-6 py-3 rounded-lg font-semibold text-white transition-all"
                        style="background-color: #10B981;"
                        onmouseover="this.style.backgroundColor='#059669';"
                        onmouseout="this.style.backgroundColor='#10B981';"
                    >
                        Áp dụng giá riêng
                    </button>
                    <a href="{{ route('admin.products.show', $product) }}" class="px-6 py-3 rounded-lg font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
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
        const formData = new FormData(form);
        
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
    updateUserPreview();
    updateVariantPreview();
</script>
@endsection

@php
    $activeMenu = 'products';
@endphp

