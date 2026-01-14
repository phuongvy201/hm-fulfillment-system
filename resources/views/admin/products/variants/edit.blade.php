@extends('layouts.admin-dashboard')     

@section('title', 'Edit Variant - ' . config('app.name', 'Laravel'))

@section('header-title', 'Edit Variant' . ($variant->attributes->count() > 0 ? ': ' . $variant->display_name : ''))
@section('header-subtitle', 'Update variant information')

@section('header-actions')
<a href="{{ route('admin.products.show', $product) }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
    ← Back to Product
</a>
@endsection

@section('content')
<div class="max-w-3xl">
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

        <form method="POST" action="{{ route('admin.products.variants.update', [$product, $variant]) }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="sku" class="block text-sm font-semibold mb-2" style="color: #111827;">SKU (Optional)</label>
                        <input 
                            type="text" 
                            id="sku" 
                            name="sku" 
                            value="{{ old('sku', $variant->sku) }}"
                            class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                            style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                            onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                            onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                        >
                    </div>
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
                            <option value="active" {{ old('status', $variant->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $variant->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- Dynamic Attributes Section -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-semibold" style="color: #111827;">Attributes</label>
                        <button type="button" onclick="addAttributeField()" class="text-sm font-medium" style="color: #2563EB;">
                            + Add Attribute
                        </button>
                    </div>
                    <div id="attributes-container" class="space-y-3">
                        @if($variant->attributes && $variant->attributes->count() > 0)
                            @foreach($variant->attributes as $attr)
                            <div class="flex items-center gap-3 attribute-row">
                                <input 
                                    type="text" 
                                    name="attribute_names[]" 
                                    value="{{ old("attribute_names.{$loop->index}", $attr->attribute_name) }}"
                                    placeholder="Attribute Name (e.g., Color, Size, Material)"
                                    class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                                    style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                                    onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                                    onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                                >
                                <input 
                                    type="text" 
                                    name="attribute_values[]" 
                                    value="{{ old("attribute_values.{$loop->index}", $attr->attribute_value) }}"
                                    placeholder="Attribute Value (e.g., Red, M, Cotton)"
                                    class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                                    style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                                    onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                                    onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                                >
                                <button type="button" onclick="removeAttributeField(this)" class="px-3 py-2 rounded-lg text-sm font-medium transition-all border" style="color: #DC2626; border-color: #FEE2E2; background-color: #FEF2F2;" onmouseover="this.style.backgroundColor='#FEE2E2';" onmouseout="this.style.backgroundColor='#FEF2F2';">
                                    Remove
                                </button>
                            </div>
                            @endforeach
                        @else
                            <div class="flex items-center gap-3 attribute-row">
                                <input 
                                    type="text" 
                                    name="attribute_names[]" 
                                    placeholder="Attribute Name (e.g., Color, Size, Material)"
                                    class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                                    style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                                    onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                                    onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                                >
                                <input 
                                    type="text" 
                                    name="attribute_values[]" 
                                    placeholder="Attribute Value (e.g., Red, M, Cotton)"
                                    class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                                    style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                                    onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                                    onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                                >
                                <button type="button" onclick="removeAttributeField(this)" class="px-3 py-2 rounded-lg text-sm font-medium transition-all border" style="color: #DC2626; border-color: #FEE2E2; background-color: #FEF2F2;" onmouseover="this.style.backgroundColor='#FEE2E2';" onmouseout="this.style.backgroundColor='#FEF2F2';">
                                    Remove
                                </button>
                            </div>
                        @endif
                    </div>
                    <p class="mt-2 text-xs" style="color: #6B7280;">
                        💡 Thêm các attributes cho variant. Ví dụ: Color/Red, Size/M, Material/Cotton, v.v. Variant name sẽ được tạo tự động từ các attributes.
                    </p>
                </div>

                <div class="flex items-center gap-4 pt-4">
                    <button 
                        type="submit"
                        class="px-6 py-3 rounded-lg font-semibold text-white transition-all"
                        style="background-color: #2563EB;"
                        onmouseover="this.style.backgroundColor='#1D4ED8';"
                        onmouseout="this.style.backgroundColor='#2563EB';"
                    >
                        Update Variant
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
    function addAttributeField() {
        const container = document.getElementById('attributes-container');
        const row = document.createElement('div');
        row.className = 'flex items-center gap-3 attribute-row';
        row.innerHTML = `
            <input 
                type="text" 
                name="attribute_names[]" 
                placeholder="Attribute Name (e.g., Color, Size, Material)"
                class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
            >
            <input 
                type="text" 
                name="attribute_values[]" 
                placeholder="Attribute Value (e.g., Red, M, Cotton)"
                class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
            >
            <button type="button" onclick="removeAttributeField(this)" class="px-3 py-2 rounded-lg text-sm font-medium transition-all border" style="color: #DC2626; border-color: #FEE2E2; background-color: #FEF2F2;" onmouseover="this.style.backgroundColor='#FEE2E2';" onmouseout="this.style.backgroundColor='#FEF2F2';">
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
</script>
@endsection

@php
    $activeMenu = 'products';
@endphp





