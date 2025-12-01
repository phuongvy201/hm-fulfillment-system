@extends('layouts.app')

@section('title', 'Edit Product - ' . config('app.name', 'Laravel'))

@section('header-title', 'Edit Product')
@section('header-subtitle', 'Update product information')

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

        <form method="POST" action="{{ route('admin.products.update', $product) }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-semibold mb-2" style="color: #111827;">Product Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name', $product->name) }}"
                        required 
                        autofocus
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
                        value="{{ old('sku', $product->sku) }}"
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
                            <option value="{{ $workshop->id }}" {{ old('workshop_id', $product->workshop_id) == $workshop->id ? 'selected' : '' }}>
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
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all resize-none"
                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                        onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                    >{{ old('description', $product->description) }}</textarea>
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
                        <option value="draft" {{ old('status', $product->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="flex items-center gap-4 pt-4">
                    <button 
                        type="submit"
                        class="px-6 py-3 rounded-lg font-semibold text-white transition-all"
                        style="background-color: #2563EB;"
                        onmouseover="this.style.backgroundColor='#1D4ED8';"
                        onmouseout="this.style.backgroundColor='#2563EB';"
                    >
                        Update Product
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

@php
    $activeMenu = 'products';
@endphp





