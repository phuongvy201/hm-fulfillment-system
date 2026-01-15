@extends('layouts.admin-dashboard')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Products Management - ' . config('app.name', 'Laravel'))

@section('header-title', 'Products Management')
@section('header-subtitle', 'Manage all products in the system')

@section('header-actions')
<a href="{{ route('admin.products.create') }}" class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2.5 rounded-lg font-semibold flex items-center gap-2 transition-colors shadow-sm">
    <span class="material-symbols-outlined">add</span>
    Add Product
</a>
<a href="{{ route('admin.products.trashed') }}" class="border border-gray-300 bg-white text-gray-700 px-5 py-2.5 rounded-lg font-semibold flex items-center gap-2 hover:bg-gray-50 transition-colors shadow-sm">
    <span class="material-symbols-outlined">delete</span>
    Deleted Products
</a>
@endsection

@section('content')
<div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Product</th>
                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Variants</th>
                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Created Date</th>
                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Type / Workshop</th>
                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
    @forelse($products as $product)
            @php
                $primaryImage = $product->images->where('is_primary', true)->first() ?? $product->images->first();
            @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-5">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-gray-100 rounded-md overflow-hidden shrink-0 border border-gray-200">
            @if($primaryImage)
                                    <img alt="{{ $product->name }}" class="w-full h-full object-cover" src="{{ Storage::url($primaryImage->image_path) }}">
            @else
                                    <div class="w-full h-full flex items-center justify-center bg-gray-100">
                                        <span class="material-symbols-outlined text-gray-400">inventory_2</span>
                                    </div>
            @endif
        </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-gray-900">{{ $product->name }}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide
                    @if($product->status === 'active') bg-emerald-100 text-emerald-700
                    @elseif($product->status === 'inactive') bg-red-100 text-red-700
                                        @else bg-amber-100 text-amber-700
                    @endif">
                                        <span class="w-1.5 h-1.5 rounded-full mr-1.5
                        @if($product->status === 'active') bg-emerald-500
                        @elseif($product->status === 'inactive') bg-red-500
                                            @else bg-amber-500
                        @endif"></span> 
                    {{ ucfirst($product->status) }}
                </span>
            </div>
                                @if($product->sku)
                                    <p class="text-xs text-gray-500 mt-0.5">ID: {{ $product->sku }}</p>
                @endif
            </div>
        </div>
                    </td>
                    <td class="px-6 py-5">
                        <div class="flex items-center text-sm text-gray-600">
                            <span class="material-symbols-outlined text-gray-400">assignment</span>
                            <span class="ml-1">{{ $product->variants_count ?? 0 }} {{ ($product->variants_count ?? 0) === 1 ? 'variant' : 'variants' }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-5">
                        <div class="flex items-center text-sm text-gray-600">
                            <span class="material-symbols-outlined text-gray-400">calendar_today</span>
                            <span class="ml-1">{{ $product->created_at->format('M d, Y') }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-5">
                        @if($product->workshop)
                            <div class="flex items-center text-sm text-gray-600">
                                <span class="material-symbols-outlined text-gray-400">factory</span>
                                <span class="ml-1">{{ $product->workshop->name }}</span>
                            </div>
                        @else
                            <span class="text-sm text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-5 text-right">
                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('admin.products.show', $product) }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">View</a>
                            <a href="{{ route('admin.products.edit', $product) }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">Edit</a>
            <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this product?\n\nThis will soft delete (can be restored).\nTo permanently delete, use Force Delete button.');">
                @csrf
                @method('DELETE')
                                <button type="submit" class="text-sm font-medium text-red-500 hover:text-red-700 transition-colors">Delete</button>
            </form>
        </div>
                    </td>
                </tr>
    @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <span class="material-symbols-outlined text-6xl text-gray-200 mb-4 block">inventory_2</span>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No products found</h3>
        <p class="text-sm text-gray-500 mb-6">Get started by creating a new product.</p>
        <a href="{{ route('admin.products.create') }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold text-white bg-orange-500 hover:bg-orange-600 transition-all shadow-sm">
            <span class="material-symbols-outlined text-base mr-2">add</span>
            Add First Product
        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
        <span class="text-sm text-gray-500 font-medium">Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} products</span>
        <div class="flex gap-2">
            @if($products->onFirstPage())
                <button class="p-2 text-gray-400 transition-colors disabled:opacity-30" disabled>
                    <span class="material-symbols-outlined m-0">chevron_left</span>
                </button>
            @else
                <a href="{{ $products->previousPageUrl() }}" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                    <span class="material-symbols-outlined m-0">chevron_left</span>
                </a>
            @endif
            
            @php
                $currentPage = $products->currentPage();
                $lastPage = $products->lastPage();
                $startPage = max(1, $currentPage - 2);
                $endPage = min($lastPage, $currentPage + 2);
            @endphp
            
            @if($startPage > 1)
                <a href="{{ $products->url(1) }}" class="w-8 h-8 flex items-center justify-center rounded-md text-gray-600 hover:bg-gray-100 text-sm font-semibold transition-colors">1</a>
                @if($startPage > 2)
                    <span class="w-8 h-8 flex items-center justify-center text-gray-400">...</span>
                @endif
            @endif
            
            @for($page = $startPage; $page <= $endPage; $page++)
                @if($page == $currentPage)
                    <button class="w-8 h-8 flex items-center justify-center rounded-md bg-orange-500 text-white text-sm font-semibold">{{ $page }}</button>
                @else
                    <a href="{{ $products->url($page) }}" class="w-8 h-8 flex items-center justify-center rounded-md text-gray-600 hover:bg-gray-100 text-sm font-semibold transition-colors">{{ $page }}</a>
                @endif
            @endfor
            
            @if($endPage < $lastPage)
                @if($endPage < $lastPage - 1)
                    <span class="w-8 h-8 flex items-center justify-center text-gray-400">...</span>
                @endif
                <a href="{{ $products->url($lastPage) }}" class="w-8 h-8 flex items-center justify-center rounded-md text-gray-600 hover:bg-gray-100 text-sm font-semibold transition-colors">{{ $lastPage }}</a>
            @endif
            
            @if($products->hasMorePages())
                <a href="{{ $products->nextPageUrl() }}" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                    <span class="material-symbols-outlined m-0">chevron_right</span>
                </a>
            @else
                <button class="p-2 text-gray-400 transition-colors disabled:opacity-30" disabled>
                    <span class="material-symbols-outlined m-0">chevron_right</span>
                </button>
            @endif
</div>
    </div>
@endif
</div>
@endsection

@php
    $activeMenu = 'products';
@endphp
