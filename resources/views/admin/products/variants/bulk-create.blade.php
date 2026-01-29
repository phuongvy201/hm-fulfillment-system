@extends('layouts.admin-dashboard') 

@section('title', 'Bulk Add Variants - ' . config('app.name', 'Laravel'))

@section('header-title', 'Bulk Add Variants to ' . $product->name)
@section('header-subtitle', 'Create multiple variants from color and size combinations')

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

    <form method="POST" action="{{ route('admin.products.variants.bulk-store', $product) }}">
        @csrf

        <div class="space-y-6">
            <!-- Info Section -->
            <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-orange-600">lightbulb</span>
                    <h2 class="text-lg font-semibold text-gray-900">Hướng dẫn</h2>
                </div>
                <div class="p-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-sm text-blue-900">
                            <strong>💡 Tip:</strong> Nhập tên attributes và values tùy chỉnh. Tất cả combinations sẽ được tạo tự động.<br>
                            <strong>Loại trừ values:</strong> Nếu một attribute1 value chỉ có một số attribute2 values nhất định, dùng format: <code class="bg-white px-1 py-0.5 rounded text-xs">Value:CODE|V1,V2,V3</code>
                        </p>
                    </div>
                </div>
            </section>

            <!-- Attribute Names Section -->
            <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-orange-600">settings</span>
                    <h2 class="text-lg font-semibold text-gray-900">1. Đặt tên Attributes</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="attribute1_name" class="block text-sm font-medium mb-2 text-gray-700">Attribute 1 Name</label>
                            <input 
                                type="text" 
                                id="attribute1_name" 
                                name="attribute1_name" 
                                value="{{ old('attribute1_name', 'Color') }}"
                                placeholder="e.g., Color, Material, Style"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-all"
                            >
                        </div>
                        <div>
                            <label for="attribute2_name" class="block text-sm font-medium mb-2 text-gray-700">Attribute 2 Name</label>
                            <input 
                                type="text" 
                                id="attribute2_name" 
                                name="attribute2_name" 
                                value="{{ old('attribute2_name', 'Size') }}"
                                placeholder="e.g., Size, Pattern, Brand"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-all"
                            >
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-500">
                        💡 Đặt tên cho 2 attributes. Ví dụ: "Color/Size" cho quần áo, "Material/Style" cho nội thất, v.v.
                    </p>
                </div>
            </section>

            <!-- Attribute Values Section -->
            <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-orange-600">edit_note</span>
                    <h2 class="text-lg font-semibold text-gray-900">2. Nhập Attribute Values</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Attribute 1 Values -->
                        <div>
                            <label for="colors" class="block text-sm font-medium mb-2 text-gray-700">
                                <span id="attribute1_label">Color</span> Values <span class="text-red-500">*</span>
                            </label>
                            <textarea 
                                id="colors" 
                                name="colors" 
                                rows="12"
                                required 
                                autofocus
                                placeholder="Red&#10;Blue&#10;Green&#10;Black:#000000&#10;White:#FFFFFF&#10;&#10;Với mã màu hex:&#10;Red:#FF0000&#10;Blue:#0000FF&#10;Cardinal Red:#DC143C:CRED&#10;&#10;Với mã màu hex và sizes tùy chỉnh:&#10;Red:#FF0000|S,M,L,XL,2XL&#10;Black:#000000:BLAC|XS,S,M,L,XL"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none resize-none font-mono text-sm transition-all"
                            >{{ old('colors') }}</textarea>
                            <p class="mt-2 text-xs text-gray-500">
                                Enter one value per line.<br>
                                • Format cơ bản: <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">Value</code><br>
                                • Với mã màu hex: <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">Color:#FF0000</code><br>
                                • Với mã màu hex + SKU code: <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">Color:#FF0000:CODE</code><br>
                                • Với sizes tùy chỉnh: <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">Color:#FF0000:CODE|S,M,L,XL</code><br>
                                Ví dụ: <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">Red:#FF0000|S,M,L,XL</code> hoặc <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">Black:#000000:BLAC</code>
                            </p>
                        </div>

                        <!-- Attribute 2 Values -->
                        <div>
                            <label for="sizes" class="block text-sm font-medium mb-2 text-gray-700">
                                <span id="attribute2_label">Size</span> Values <span class="text-red-500">*</span>
                            </label>
                            <textarea 
                                id="sizes" 
                                name="sizes" 
                                rows="12"
                                required
                                placeholder="XS&#10;S&#10;M&#10;L&#10;XL&#10;XXL&#10;...&#10;&#10;Or: XS, S, M, L, XL, XXL"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none resize-none font-mono text-sm transition-all"
                            >{{ old('sizes') }}</textarea>
                            <p class="mt-2 text-xs text-gray-500">Enter one value per line or separate by commas</p>
                        </div>
                    </div>
                </div>
            </section>
            
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

            <!-- Preview Section -->
            <div id="preview-section" class="bg-green-50 border border-green-200 rounded-xl p-4 hidden">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-green-600">check_circle</span>
                    <h4 class="text-sm font-semibold text-green-900">Preview:</h4>
                </div>
                <p class="text-sm text-gray-700">
                    <span id="preview-count">0</span> variants will be created
                </p>
                <div id="preview-warning" class="hidden mt-3 p-3 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-800">
                    ⚠️ Creating more than 500 variants may take a moment. Please wait...
                </div>
                <div id="preview-error" class="hidden mt-3 p-3 rounded-lg bg-red-50 border border-red-200 text-xs text-red-800">
                    ❌ Too many variants! Maximum 1000 variants at once. Please split into smaller batches.
                </div>
            </div>

            <!-- Template SKU Info -->
            @if($product->sku_template || $product->workshop_sku_template)
            <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-orange-600">info</span>
                    <h2 class="text-lg font-semibold text-gray-900">Template SKU đã được cấu hình</h2>
                </div>
                <div class="p-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-2">
                        @if($product->sku_template)
                        <p class="text-xs text-gray-700">
                            <strong>Variant SKU Template:</strong> <code class="bg-white px-2 py-1 rounded text-xs">{{ $product->sku_template }}</code>
                        </p>
                        @endif
                        @if($product->workshop_sku_template)
                        <p class="text-xs text-gray-700">
                            <strong>Workshop SKU Template:</strong> <code class="bg-white px-2 py-1 rounded text-xs">{{ $product->workshop_sku_template }}</code>
                        </p>
                        @endif
                        <p class="text-xs text-gray-600 mt-2">
                            Hệ thống sẽ tự động thay thế <code class="bg-white px-1 py-0.5 rounded text-xs">{COLOR_CODE}</code>, <code class="bg-white px-1 py-0.5 rounded text-xs">{COLOR}</code>, <code class="bg-white px-1 py-0.5 rounded text-xs">{SIZE}</code>, <code class="bg-white px-1 py-0.5 rounded text-xs">{MARKET_CODE}</code>, <code class="bg-white px-1 py-0.5 rounded text-xs">{WORKSHOP_CODE}</code> trong template để tạo SKU cho từng variant.
                        </p>
                    </div>
                </div>
            </section>
            @else
            <section class="bg-white rounded-xl shadow-sm border border-amber-200 overflow-hidden">
                <div class="p-6 border-b border-amber-200 flex items-center gap-2">
                    <span class="material-symbols-outlined text-amber-600">warning</span>
                    <h2 class="text-lg font-semibold text-gray-900">Chưa có template SKU</h2>
                </div>
                <div class="p-6">
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <p class="text-xs text-amber-800">
                            ⚠️ <strong>Chưa có template SKU:</strong> Sản phẩm này chưa có template SKU. Vui lòng cập nhật template SKU trong trang chỉnh sửa sản phẩm trước khi bulk tạo variants.
                        </p>
                    </div>
                </div>
            </section>
            @endif

            <!-- Status Section -->
            <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-orange-600">tune</span>
                    <h2 class="text-lg font-semibold text-gray-900">3. Status</h2>
                </div>
                <div class="p-6">
                    <label for="status" class="block text-sm font-medium mb-2 text-gray-700">Status</label>
                    <div class="relative">
                        <select 
                            id="status" 
                            name="status"
                            required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-all appearance-none bg-white"
                        >
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                    </div>
                </div>
            </section>

            <!-- Footer Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col sm:flex-row gap-4 items-center justify-between">
                <div class="flex-1"></div>
                <div class="flex gap-3 w-full sm:w-auto">
                    <a href="{{ route('admin.products.show', $product) }}" class="flex-1 sm:flex-none px-6 py-2.5 rounded-lg font-semibold border border-gray-200 hover:bg-gray-50 transition-all text-center">
                        Cancel
                    </a>
                    <button 
                        type="submit"
                        class="flex-1 sm:flex-none px-8 py-2.5 rounded-lg font-semibold bg-orange-600 text-white hover:bg-orange-700 shadow-lg shadow-orange-600/20 transition-all transform active:scale-[0.98]"
                    >
                        Create All Variants
                    </button>
                </div>
            </div>
        </div>
    </form>
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
