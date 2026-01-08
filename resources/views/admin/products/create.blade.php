@extends('layouts.app')

@section('title', 'Create Product - ' . config('app.name', 'Laravel'))

@section('header-title', 'Create New Product')
@section('header-subtitle', 'Add a new product to the system')

@section('header-actions')
<a href="{{ route('admin.products.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
    ← Back to Products
</a>
@endsection

@section('content')
<div class="max-w-2xl">
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

        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-semibold mb-2" style="color: #111827;">Product Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name') }}"
                        required 
                        autofocus
                        placeholder="e.g., T-Shirt, Hoodie, Mug"
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                        onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                    >
                </div>

                <div>
                    <label for="sku" class="block text-sm font-semibold mb-2" style="color: #111827;">SKU (Optional)</label>
                    <input 
                        type="text" 
                        id="sku" 
                        name="sku" 
                        value="{{ old('sku') }}"
                        placeholder="e.g., PROD-001"
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                        onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                    >
                </div>

                <div>
                    <label for="workshop_id" class="block text-sm font-semibold mb-2" style="color: #111827;">Workshop <span class="text-red-500">*</span></label>
                    <select 
                        id="workshop_id" 
                        name="workshop_id"
                        required
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                        onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                    >
                        <option value="">Select Workshop</option>
                        @foreach($workshops as $workshop)
                            <option value="{{ $workshop->id }}" {{ old('workshop_id') == $workshop->id ? 'selected' : '' }}>
                                {{ $workshop->name }} ({{ $workshop->code }}) - {{ $workshop->market->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold mb-2" style="color: #111827;">Description</label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="4"
                        placeholder="Product description..."
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all resize-none"
                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                        onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                    >{{ old('description') }}</textarea>
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
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div>
                    <label for="images" class="block text-sm font-semibold mb-2" style="color: #111827;">Product Images</label>
                    <input 
                        type="file" 
                        id="images" 
                        name="images[]" 
                        multiple
                        accept="image/*"
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                        onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                    >
                    <p class="text-xs mt-1" style="color: #6B7280;">You can upload multiple images. Accepted formats: JPG, PNG, GIF, WEBP (max 5MB per image)</p>
                    <div id="image-preview" class="mt-4 grid grid-cols-3 gap-4"></div>
                </div>

                <div class="flex items-center gap-4 pt-4">
                    <button 
                        type="submit"
                        class="px-6 py-3 rounded-lg font-semibold text-white transition-all"
                        style="background-color: #2563EB;"
                        onmouseover="this.style.backgroundColor='#1D4ED8';"
                        onmouseout="this.style.backgroundColor='#2563EB';"
                    >
                        Create Product
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="px-6 py-3 rounded-lg font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Image preview
    document.getElementById('images')?.addEventListener('change', function(e) {
        const preview = document.getElementById('image-preview');
        preview.innerHTML = '';
        
        Array.from(e.target.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'relative';
                div.innerHTML = `
                    <img src="${e.target.result}" alt="Preview" class="w-full h-32 object-cover rounded-lg border" style="border-color: #E5E7EB;">
                `;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    });
</script>
@endpush
@php
    $activeMenu = 'products';
@endphp





