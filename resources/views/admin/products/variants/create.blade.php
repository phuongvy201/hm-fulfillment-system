@extends('layouts.admin-dashboard') 

@section('title', 'Add Variant - ' . config('app.name', 'Laravel'))
@section('breadcrumb-current', 'Add Variant')
@section('page-title', 'Add Variant to ' . $product->name)
@section('page-actions')
    <a href="{{ route('admin.products.show', $product) }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold border border-neutral-200 text-neutral-700 hover:bg-neutral-100 transition-colors">
    ← Back to Product
</a>
@endsection

@section('content')
<div class="max-w-3xl space-y-6">
        @if ($errors->any())
        <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-sm text-red-800">
            <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

    <div class="bg-white dark:bg-[#1a140b] rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-8">
        <form method="POST" action="{{ route('admin.products.variants.store', $product) }}">
            @csrf

            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="sku" class="block text-sm font-semibold text-neutral-800 dark:text-gray-100 mb-2">SKU (Optional)</label>
                        <input 
                            type="text" 
                            id="sku" 
                            name="sku" 
                            value="{{ old('sku') }}"
                            placeholder="e.g., PROD-001-RED-L"
                            class="w-full px-4 py-3 rounded-lg border border-neutral-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-neutral-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition"
                        >
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-semibold text-neutral-800 dark:text-gray-100 mb-2">Status</label>
                        <select 
                            id="status" 
                            name="status"
                            required
                            class="w-full px-4 py-3 rounded-lg border border-neutral-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-neutral-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition"
                        >
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

        <!-- Attributes -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-semibold text-neutral-800 dark:text-gray-100">Attributes</label>
                        <button type="button" onclick="addAttributeField()" class="text-sm font-medium text-primary hover:text-primary/80">
                            + Add Attribute
                        </button>
                    </div>
                    <div id="attributes-container" class="space-y-3">
                        <div class="flex flex-col md:flex-row items-stretch md:items-center gap-3 attribute-row">
                            <input 
                                type="text" 
                                name="attribute_names[]" 
                                placeholder="Attribute Name (e.g., Color, Size, Material)"
                                class="flex-1 px-4 py-2 rounded-lg border border-neutral-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-neutral-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition attribute-name-input"
                                oninput="toggleColorHexInput(this)"
                            >
                            <input 
                                type="text" 
                                name="attribute_values[]" 
                                placeholder="Attribute Value (e.g., Red, M, Cotton)"
                                class="flex-1 px-4 py-2 rounded-lg border border-neutral-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-neutral-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition"
                            >
                            <input 
                                type="color" 
                                name="color_hexes[]" 
                                value="#000000"
                                class="color-hex-input hidden w-20 h-10 border rounded-lg cursor-pointer border-neutral-200 dark:border-gray-700"
                                title="Mã màu hex cho frontend"
                            >
                            <button type="button" onclick="removeAttributeField(this)" class="px-3 py-2 rounded-lg text-sm font-medium border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 transition">
                                Remove
                            </button>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-neutral-500 dark:text-gray-400">
                        💡 Thêm các attributes cho variant. Ví dụ: Color/Red, Size/M, Material/Cotton. Tên variant sẽ tự tạo từ các thuộc tính.
                    </p>
                </div>

                <div class="flex items-center gap-4 pt-4">
                    <button 
                        type="submit"
                        class="px-6 py-3 rounded-lg font-semibold text-white bg-primary hover:bg-orange-500 transition shadow-sm shadow-primary/20"
                    >
                        Create Variant
                    </button>
                    <a href="{{ route('admin.products.show', $product) }}" class="px-6 py-3 rounded-lg font-semibold border border-neutral-200 text-neutral-700 hover:bg-neutral-100 transition">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function addAttributeField() {
        const container = document.getElementById('attributes-container');
        const row = document.createElement('div');
        row.className = 'flex flex-col md:flex-row items-stretch md:items-center gap-3 attribute-row';
        row.innerHTML = `
            <input 
                type="text" 
                name="attribute_names[]" 
                placeholder="Attribute Name (e.g., Color, Size, Material)"
                class="flex-1 px-4 py-2 rounded-lg border border-neutral-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-neutral-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition attribute-name-input"
                oninput="toggleColorHexInput(this)"
            >
            <input 
                type="text" 
                name="attribute_values[]" 
                placeholder="Attribute Value (e.g., Red, M, Cotton)"
                class="flex-1 px-4 py-2 rounded-lg border border-neutral-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-neutral-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/50 transition"
            >
            <input 
                type="color" 
                name="color_hexes[]" 
                value="#000000"
                class="color-hex-input hidden w-20 h-10 border rounded-lg cursor-pointer border-neutral-200 dark:border-gray-700"
                title="Mã màu hex cho frontend"
            >
            <button type="button" onclick="removeAttributeField(this)" class="px-3 py-2 rounded-lg text-sm font-medium border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 transition">
                Remove
            </button>
        `;
        container.appendChild(row);
    }

    function removeAttributeField(button) {
        const container = document.getElementById('attributes-container');
        if (container.children.length > 1) {
            button.closest('.attribute-row').remove();
        }
    }

    function toggleColorHexInput(input) {
        const row = input.closest('.attribute-row');
        const colorHexInput = row.querySelector('.color-hex-input');
        const attrName = input.value.toLowerCase().trim();
        
        // Kiểm tra nếu là color attribute
        const isColor = attrName === 'color' || 
                       attrName === 'màu' || 
                       attrName.includes('color') || 
                       attrName.includes('màu');
        
        if (isColor && colorHexInput) {
            colorHexInput.classList.remove('hidden');
        } else if (colorHexInput) {
            colorHexInput.classList.add('hidden');
        }
    }
</script>
@endsection

@php
    $activeMenu = 'products';
@endphp





