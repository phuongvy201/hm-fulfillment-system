@extends('layouts.app')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Products Management - ' . config('app.name', 'Laravel'))

@section('header-title', 'Products Management')
@section('header-subtitle', 'Manage all products in the system')

@section('header-actions')
<a href="{{ route('admin.products.create') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm" style="background-color: #2563EB;" onmouseover="this.style.backgroundColor='#1D4ED8';" onmouseout="this.style.backgroundColor='#2563EB';">
    + Add Product
</a>
<a href="{{ route('admin.products.trashed') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #F59E0B; border-color: #FEF3C7; background-color: #FFFBEB;" onmouseover="this.style.backgroundColor='#FEF3C7';" onmouseout="this.style.backgroundColor='#FFFBEB';">
    <svg class="w-4 h-4 inline mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
    </svg>
    Deleted Products
</a>
@endsection

@section('content')
<div class="space-y-6">
    @forelse($products as $product)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-200 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <!-- Left: Product Info -->
                <div class="flex items-center gap-4 flex-1">
                    <!-- Product Image or Icon -->
                    @php
                        $primaryImage = $product->images->where('is_primary', true)->first() ?? $product->images->first();
                    @endphp
                    @if($primaryImage)
                        <img src="{{ Storage::url($primaryImage->image_path) }}" alt="{{ $product->name }}" class="w-14 h-14 rounded-xl object-cover border shadow-md" style="border-color: #E5E7EB;">
                    @else
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center font-bold text-white text-lg shadow-md" style="background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                    @endif

                    <!-- Product Details -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $product->name }}</h3>
                            @if($product->status === 'active')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full" style="background-color: #D1FAE5; color: #065F46;">
                                    Active
                                </span>
                            @elseif($product->status === 'inactive')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full" style="background-color: #FEE2E2; color: #991B1B;">
                                    Inactive
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full" style="background-color: #F3F4F6; color: #6B7280;">
                                    Draft
                                </span>
                            @endif
                        </div>
                        @if($product->sku)
                            <div class="flex items-center gap-1.5 text-sm text-gray-600 mb-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                </svg>
                                <span>SKU: {{ $product->sku }}</span>
                            </div>
                        @endif
                        <div class="flex items-center gap-4 text-sm text-gray-500">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                <span>{{ $product->variants_count }} {{ $product->variants_count === 1 ? 'variant' : 'variants' }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span>Created {{ $product->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Actions -->
                <div class="flex items-center gap-2 ml-4">
                    <a href="{{ route('admin.products.show', $product) }}" class="px-4 py-2 rounded-lg text-sm font-medium transition-all border" style="color: #2563EB; border-color: #DBEAFE; background-color: #EFF6FF;" onmouseover="this.style.backgroundColor='#DBEAFE'; this.style.borderColor='#2563EB';" onmouseout="this.style.backgroundColor='#EFF6FF'; this.style.borderColor='#DBEAFE';">
                        <svg class="w-4 h-4 inline mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        View
                    </a>
                    <a href="{{ route('admin.products.edit', $product) }}" class="px-4 py-2 rounded-lg text-sm font-medium transition-all border" style="color: #2563EB; border-color: #DBEAFE; background-color: #EFF6FF;" onmouseover="this.style.backgroundColor='#DBEAFE'; this.style.borderColor='#2563EB';" onmouseout="this.style.backgroundColor='#EFF6FF'; this.style.borderColor='#DBEAFE';">
                        <svg class="w-4 h-4 inline mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                    </a>
                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this product?\n\nThis will soft delete (can be restored).\nTo permanently delete, use Force Delete button.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium transition-all border" style="color: #DC2626; border-color: #FEE2E2; background-color: #FEF2F2;" onmouseover="this.style.backgroundColor='#FEE2E2'; this.style.borderColor='#DC2626';" onmouseout="this.style.backgroundColor='#FEF2F2'; this.style.borderColor='#FEE2E2';">
                            <svg class="w-4 h-4 inline mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete
                        </button>
                    </form>
                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="inline" onsubmit="return confirm('⚠️ WARNING: This will PERMANENTLY delete the product from database!\n\nThis action CANNOT be undone!\n\nAre you absolutely sure?');">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="force" value="1">
                        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium transition-all border" style="color: #991B1B; border-color: #FEE2E2; background-color: #FEF2F2;" onmouseover="this.style.backgroundColor='#FEE2E2'; this.style.borderColor='#DC2626';" onmouseout="this.style.backgroundColor='#FEF2F2'; this.style.borderColor='#FEE2E2';" title="Permanently delete from database">
                            <svg class="w-4 h-4 inline mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Force Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No products found</h3>
        <p class="text-sm text-gray-600 mb-6">Get started by creating a new product.</p>
        <a href="{{ route('admin.products.create') }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm" style="background-color: #2563EB;" onmouseover="this.style.backgroundColor='#1D4ED8';" onmouseout="this.style.backgroundColor='#2563EB';">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add First Product
        </a>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($products->hasPages())
<div class="mt-6 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-4 py-3">
        {{ $products->links() }}
    </div>
</div>
@endif
@endsection

@php
    $activeMenu = 'products';
@endphp





