@extends('layouts.app')

@section('title', 'Bulk Add Variants - ' . config('app.name', 'Laravel'))

@section('header-title', 'Bulk Add Variants to ' . $product->name)
@section('header-subtitle', 'Create multiple variants from color and size combinations')

@section('header-actions')
<a href="{{ route('admin.products.show', $product) }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
    ← Back to Product
</a>
@endsection

@section('content')
<div class="max-w-4xl">
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
            <p class="text-sm" style="color: #1E40AF;">
                <strong>💡 Tip:</strong> Nhập tên attributes và values tùy chỉnh. Tất cả combinations sẽ được tạo tự động.<br>
                <strong>Loại trừ values:</strong> Nếu một attribute1 value chỉ có một số attribute2 values nhất định, dùng format: <code>Value:CODE|V1,V2,V3</code>
            </p>
        </div>

        <form method="POST" action="{{ route('admin.products.variants.bulk-store', $product) }}">
            @csrf

            <div class="space-y-6">
                <!-- Attribute Names -->
                <div class="grid grid-cols-2 gap-4 p-4 rounded-lg border" style="border-color: #DBEAFE; background-color: #EFF6FF;">
                    <div>
                        <label for="attribute1_name" class="block text-sm font-semibold mb-2" style="color: #111827;">Attribute 1 Name</label>
                        <input 
                            type="text" 
                            id="attribute1_name" 
                            name="attribute1_name" 
                            value="{{ old('attribute1_name', 'Color') }}"
                            placeholder="e.g., Color, Material, Style"
                            class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                            style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                            onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                            onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                        >
                    </div>
                    <div>
                        <label for="attribute2_name" class="block text-sm font-semibold mb-2" style="color: #111827;">Attribute 2 Name</label>
                        <input 
                            type="text" 
                            id="attribute2_name" 
                            name="attribute2_name" 
                            value="{{ old('attribute2_name', 'Size') }}"
                            placeholder="e.g., Size, Pattern, Brand"
                            class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                            style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                            onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                            onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                        >
                    </div>
                    <p class="col-span-2 text-xs" style="color: #6B7280;">
                        💡 Đặt tên cho 2 attributes. Ví dụ: "Color/Size" cho quần áo, "Material/Style" cho nội thất, v.v.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Attribute 1 Values -->
                    <div>
                        <label for="colors" class="block text-sm font-semibold mb-2" style="color: #111827;">
                            <span id="attribute1_label">Color</span> Values <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            id="colors" 
                            name="colors" 
                            rows="12"
                            required 
                            autofocus
                            placeholder="Red&#10;Blue&#10;Green&#10;Black&#10;White&#10;&#10;Với mã màu:&#10;Cardinal Red:CRED&#10;Navy Blue:NAVY&#10;Black:BLAC&#10;&#10;Với sizes tùy chỉnh:&#10;Cardinal Red:CRED|S,M,L,XL,2XL,3XL&#10;Black:BLAC|XS,S,M,L,XL,2XL,3XL,4XL,5XL"
                            class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all resize-none font-mono text-sm"
                            style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                            onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                            onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                        >{{ old('colors') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">
                            Enter one value per line.<br>
                            • Format cơ bản: <code>Value</code> hoặc <code>Value:CODE</code><br>
                            • Với attribute2 values tùy chỉnh: <code>Value:CODE|V1,V2,V3</code><br>
                            Ví dụ: <code>Cardinal Red:CRED|S,M,L,XL,2XL,3XL</code>
                        </p>
                    </div>

                    <!-- Attribute 2 Values -->
                    <div>
                        <label for="sizes" class="block text-sm font-semibold mb-2" style="color: #111827;">
                            <span id="attribute2_label">Size</span> Values <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            id="sizes" 
                            name="sizes" 
                            rows="12"
                            required
                            placeholder="XS&#10;S&#10;M&#10;L&#10;XL&#10;XXL&#10;...&#10;&#10;Or: XS, S, M, L, XL, XXL"
                            class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all resize-none font-mono text-sm"
                            style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                            onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                            onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                        >{{ old('sizes') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Enter one value per line or separate by commas</p>
                    </div>
                </div>
                
                <script>
                    // Update labels when attribute names change
                    document.getElementById('attribute1_name')?.addEventListener('input', function() {
                        const label = document.getElementById('attribute1_label');
                        if (label) {
                            label.textContent = this.value || 'Color';
                        }
                    });
                    document.getElementById('attribute2_name')?.addEventListener('input', function() {
                        const label = document.getElementById('attribute2_label');
                        if (label) {
                            label.textContent = this.value || 'Size';
                        }
                    });
                </script>

                <!-- Preview -->
                <div id="preview-section" class="hidden p-4 rounded-lg border" style="border-color: #DBEAFE; background-color: #EFF6FF;">
                    <h4 class="text-sm font-semibold mb-2" style="color: #1E40AF;">Preview:</h4>
                    <p class="text-sm text-gray-600">
                        <span id="preview-count">0</span> variants will be created
                    </p>
                    <div id="preview-warning" class="hidden mt-2 p-2 rounded text-xs" style="background-color: #FEF3C7; border: 1px solid #FCD34D; color: #92400E;">
                        ⚠️ Creating more than 500 variants may take a moment. Please wait...
                    </div>
                    <div id="preview-error" class="hidden mt-2 p-2 rounded text-xs" style="background-color: #FEE2E2; border: 1px solid #EF4444; color: #991B1B;">
                        ❌ Too many variants! Maximum 1000 variants at once. Please split into smaller batches.
                    </div>
                </div>

                <!-- SKU Prefix -->
                <div>
                    <label for="sku_prefix" class="block text-sm font-semibold mb-2" style="color: #111827;">
                        Variant SKU Prefix (Nội bộ) <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="sku_prefix" 
                        name="sku_prefix" 
                        value="{{ old('sku_prefix', $product->sku ?? '') }}"
                        placeholder="e.g., T001"
                        required
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                        onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                    >
                    <p class="mt-1 text-xs text-gray-500">
                        Format: <code>{prefix}-{COLORCODE}-{SIZE}-{MARKET}</code><br>
                        Ví dụ: <code>T001-WHIT-3XL-UK</code> (Variant SKU nội bộ)
                    </p>
                </div>

                <!-- Workshops for Workshop SKUs -->
                @if($workshops && $workshops->count() > 0)
                <div>
                    <div class="mb-2">
                        <label class="block text-sm font-semibold mb-2" style="color: #111827;">
                            Workshops (Để tạo Workshop SKUs)
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input 
                                type="checkbox" 
                                id="auto_create_workshop_skus" 
                                name="auto_create_workshop_skus" 
                                value="1"
                                {{ old('auto_create_workshop_skus') ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                onchange="document.getElementById('workshops-container').style.display = this.checked ? 'block' : 'none';"
                            >
                            <span class="text-sm text-gray-700">Tự động tạo Workshop SKUs cho các variants</span>
                        </label>
                        <input type="hidden" name="workshop_ids[]" value=""> <!-- Dummy để validation pass -->
                    </div>
                    <div id="workshops-container" class="p-4 rounded-lg border" style="border-color: #DBEAFE; background-color: #EFF6FF; display: {{ old('auto_create_workshop_skus') ? 'block' : 'none' }};">
                        <p class="text-xs text-gray-600 mb-3">
                            Chọn workshops và nhập Workshop SKU Code cho từng workshop. Format: <code>{WORKSHOP_SKU_CODE}-{COLORCODE}-{SIZE}</code><br>
                            <strong>Ví dụ:</strong> T-Shirt từ workshop GD05 → SKU Code: <code>GD05</code> → SKU: <code>GD05-WHIT-3XL</code><br>
                            Sweatshirt từ workshop GD05 → SKU Code: <code>GD056</code> → SKU: <code>GD056-WHIT-3XL</code>
                        </p>
                        <div class="space-y-3 max-h-64 overflow-y-auto">
                            @php
                                $productWorkshopMappings = $product->workshopProductSkuCodes ?? collect();
                            @endphp
                            @foreach($workshops as $index => $workshop)
                                @php
                                    $existingMapping = $productWorkshopMappings->firstWhere('workshop_id', $workshop->id);
                                    $defaultSkuCode = $existingMapping ? $existingMapping->sku_code : $workshop->code;
                                @endphp
                                <div class="p-3 rounded-lg border" style="border-color: #DBEAFE; background-color: #FFFFFF;">
                                    <div class="flex items-center gap-3 mb-2">
                                        <input 
                                            type="checkbox" 
                                            name="workshop_sku_mappings[{{ $index }}][workshop_id]" 
                                            value="{{ $workshop->id }}"
                                            class="workshop-checkbox w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                            data-workshop-index="{{ $index }}"
                                            onchange="toggleWorkshopSkuInput({{ $index }})"
                                        >
                                        <div class="flex-1">
                                            <span class="text-sm font-medium text-gray-900">{{ $workshop->name }}</span>
                                            <span class="text-xs text-gray-500 ml-2">(Code: {{ $workshop->code }})</span>
                                        </div>
                                    </div>
                                    <div id="workshop-sku-input-{{ $index }}" class="mt-2">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">
                                            Workshop SKU Code cho sản phẩm này:
                                        </label>
                                        <input 
                                            type="text" 
                                            name="workshop_sku_mappings[{{ $index }}][sku_code]" 
                                            value="{{ old("workshop_sku_mappings.{$index}.sku_code", $defaultSkuCode) }}"
                                            placeholder="e.g., GD05, GD056"
                                            class="w-full px-3 py-2 text-sm border rounded-lg font-mono"
                                            style="border-color: #D1D5DB;"
                                            onfocus="this.style.borderColor='#2563EB';"
                                            onblur="this.style.borderColor='#D1D5DB';"
                                        >
                                        <p class="text-xs text-gray-500 mt-1">
                                            Mặc định: {{ $workshop->code }} (nếu để trống sẽ dùng workshop code)
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <script>
                            function toggleWorkshopSkuInput(index) {
                                const checkbox = document.querySelector(`[data-workshop-index="${index}"]`);
                                const inputDiv = document.getElementById(`workshop-sku-input-${index}`);
                                if (checkbox && inputDiv) {
                                    if (checkbox.checked) {
                                        inputDiv.style.display = 'block';
                                    } else {
                                        inputDiv.style.display = 'none';
                                    }
                                }
                            }
                            // Initialize on page load
                            document.addEventListener('DOMContentLoaded', function() {
                                document.querySelectorAll('.workshop-checkbox').forEach(checkbox => {
                                    const index = checkbox.getAttribute('data-workshop-index');
                                    toggleWorkshopSkuInput(index);
                                });
                            });
                        </script>
                    </div>
                </div>
                @endif

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-semibold mb-2" style="color: #111827;">Status</label>
                    <select 
                        id="status" 
                        name="status"
                        required
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                        onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                    >
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="flex items-center gap-4 pt-4 border-t" style="border-color: #E5E7EB;">
                    <button 
                        type="submit"
                        class="px-6 py-3 rounded-lg font-semibold text-white transition-all"
                        style="background-color: #2563EB;"
                        onmouseover="this.style.backgroundColor='#1D4ED8';"
                        onmouseout="this.style.backgroundColor='#2563EB';"
                    >
                        Create All Variants
                    </button>
                    <a href="{{ route('admin.products.show', $product) }}" class="px-6 py-3 rounded-lg font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function parseList(text) {
        const lines = text.split(/\r\n|\r|\n/);
        const items = [];
        lines.forEach(line => {
            const parts = line.split(',');
            parts.forEach(part => {
                const trimmed = part.trim();
                if (trimmed) {
                    items.push(trimmed);
                }
            });
        });
        return [...new Set(items)];
    }

    function parseColorWithSizes(colorLine) {
        // Parse format: "Color Name:CODE|SIZE1,SIZE2" or "Color Name:CODE" or "Color Name"
        const parts = colorLine.split('|');
        const colorPart = parts[0].trim();
        const sizesPart = parts[1] ? parts[1].trim() : null;
        
        // Extract color name and code
        const colorCodeParts = colorPart.split(':');
        const colorName = colorCodeParts[0].trim();
        const colorCode = colorCodeParts[1] ? colorCodeParts[1].trim() : null;
        
        // Parse sizes if provided
        let sizes = [];
        if (sizesPart) {
            sizes = sizesPart.split(',').map(s => s.trim()).filter(s => s);
        }
        
        return { colorName, colorCode, sizes };
    }

    function updatePreview() {
        const colorsText = document.getElementById('colors').value;
        const sizesText = document.getElementById('sizes').value;
        
        // Get default sizes
        const defaultSizes = parseList(sizesText);
        
        if (!colorsText) {
            document.getElementById('preview-section').classList.add('hidden');
            return;
        }

        // Parse colors with their specific sizes
        const colorLines = colorsText.split(/\r\n|\r|\n/).map(line => line.trim()).filter(line => line);
        let total = 0;
        
        colorLines.forEach(colorLine => {
            const { sizes: sizesForColor } = parseColorWithSizes(colorLine);
            const finalSizes = sizesForColor.length > 0 ? sizesForColor : defaultSizes;
            total += finalSizes.length;
        });

        const previewSection = document.getElementById('preview-section');
        const previewWarning = document.getElementById('preview-warning');
        const previewError = document.getElementById('preview-error');
        
        if (total > 0) {
            document.getElementById('preview-count').textContent = total;
            previewSection.classList.remove('hidden');
            
            // Show warnings/errors
            if (total > 1000) {
                previewError.classList.remove('hidden');
                previewWarning.classList.add('hidden');
            } else if (total > 500) {
                previewWarning.classList.remove('hidden');
                previewError.classList.add('hidden');
            } else {
                previewWarning.classList.add('hidden');
                previewError.classList.add('hidden');
            }
        } else {
            previewSection.classList.add('hidden');
        }
    }

    document.getElementById('colors').addEventListener('input', updatePreview);
    document.getElementById('sizes').addEventListener('input', updatePreview);
    
    // Initial preview
    updatePreview();
</script>
@endsection

@php
    $activeMenu = 'products';
@endphp

