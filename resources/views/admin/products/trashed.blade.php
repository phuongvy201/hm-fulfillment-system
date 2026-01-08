@extends('layouts.app')

@section('title', 'Deleted Products - ' . config('app.name', 'Laravel'))

@section('header-title', 'Deleted Products')
@section('header-subtitle', 'Restore or permanently delete products')

@section('header-actions')
<a href="{{ route('admin.products.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
    ← Back to Products
</a>
@endsection

@section('content')
@if(session('success'))
    <div class="mb-6 p-4 rounded-lg" style="background-color: #D1FAE5; border: 1px solid #10B981;">
        <p class="text-sm font-medium" style="color: #065F46;">{{ session('success') }}</p>
    </div>
@endif

<div class="space-y-6">
    <!-- Info Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: #FEF3C7;">
                <svg class="w-6 h-6" style="color: #F59E0B;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Soft Deleted Products</h3>
                <p class="text-sm text-gray-600">These products have been soft deleted and can be restored. Total: {{ $products->total() }}</p>
            </div>
        </div>
    </div>

    <!-- Products List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @forelse($products as $product)
        <div class="p-6 border-b border-gray-100 hover:bg-gray-50 transition-colors {{ $loop->last ? '' : 'border-b' }}">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <h4 class="text-lg font-semibold text-gray-900">{{ $product->name }}</h4>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full" style="background-color: #FEE2E2; color: #991B1B;">
                            Deleted
                        </span>
                    </div>
                    <div class="flex items-center gap-6 text-sm text-gray-600">
                        @if($product->sku)
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                </svg>
                                <span><strong>SKU:</strong> {{ $product->sku }}</span>
                            </div>
                        @endif
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            <span>{{ $product->variants_count }} {{ $product->variants_count === 1 ? 'variant' : 'variants' }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Deleted {{ $product->deleted_at->format('M d, Y H:i') }}</span>
                        </div>
                    </div>
                    @if($product->description)
                        <p class="text-sm text-gray-600 mt-2">{{ \Illuminate\Support\Str::limit($product->description, 150) }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-2 ml-4">
                    <form action="{{ route('admin.products.restore', $product->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to restore this product?');">
                        @csrf
                        @method('POST')
                        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium transition-all border" style="color: #10B981; border-color: #D1FAE5; background-color: #ECFDF5;" onmouseover="this.style.backgroundColor='#D1FAE5'; this.style.borderColor='#10B981';" onmouseout="this.style.backgroundColor='#ECFDF5'; this.style.borderColor='#D1FAE5';">
                            <svg class="w-4 h-4 inline mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Restore
                        </button>
                    </form>
                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="inline" onsubmit="return confirm('⚠️ WARNING: This will PERMANENTLY delete the product from database!\n\nThis action CANNOT be undone!\n\nAre you absolutely sure?');">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="force" value="1">
                        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium transition-all border" style="color: #991B1B; border-color: #FEE2E2; background-color: #FEF2F2;" onmouseover="this.style.backgroundColor='#FEE2E2'; this.style.borderColor='#DC2626';" onmouseout="this.style.backgroundColor='#FEF2F2'; this.style.borderColor='#FEE2E2';">
                            <svg class="w-4 h-4 inline mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Force Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="p-12 text-center">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="text-sm text-gray-600 mb-4">No deleted products found.</p>
            <a href="{{ route('admin.products.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm" style="background-color: #2563EB;" onmouseover="this.style.backgroundColor='#1D4ED8';" onmouseout="this.style.backgroundColor='#2563EB';">
                ← Back to Products
            </a>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($products->hasPages())
    <div class="flex justify-center">
        {{ $products->links() }}
    </div>
    @endif
</div>
@endsection

@php
    $activeMenu = 'products';
@endphp

