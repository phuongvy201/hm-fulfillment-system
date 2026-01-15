@extends('layouts.admin-dashboard')

@section('title', 'Create Manual Order - ' . config('app.name', 'Laravel'))

@section('header-title', 'Create Manual Order')
@section('header-subtitle', 'Create and submit manual order to workshop')

@section('content')
<div class="p-8 max-w-6xl mx-auto w-full space-y-6 pb-32">
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <ul class="text-sm list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.orders.store') }}" id="orderForm">
        @csrf

        <!-- Order Information -->
        <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                <span class="material-symbols-outlined" style="color: #F7961D;">info</span>
                <h3 class="font-bold text-slate-900">Order Information</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Customer <span class="text-red-500">*</span></label>
                    <select 
                        name="user_id" 
                        required
                        class="w-full px-4 py-2 rounded-md border border-slate-300 focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D]"
                    >
                        <option value="">Select Customer</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Order Number</label>
                    <input 
                        type="text" 
                        name="order_number" 
                        value="{{ old('order_number', '') }}"
                        placeholder="Auto-generated if empty"
                        class="w-full px-4 py-2 rounded-md border border-slate-300 focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D]"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Store Name</label>
                    <input 
                        type="text" 
                        name="store_name" 
                        value="{{ old('store_name', '') }}"
                        placeholder="Enter store name"
                        class="w-full px-4 py-2 rounded-md border border-slate-300 focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D]"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Sales Channel</label>
                    <select name="sales_channel" class="w-full px-4 py-2 rounded-md border border-slate-300 focus:ring-primary focus:border-primary">
                        <option value="">Select Sales Channel</option>
                        <option value="shopify" {{ old('sales_channel') == 'shopify' ? 'selected' : '' }}>Shopify</option>
                        <option value="etsy" {{ old('sales_channel') == 'etsy' ? 'selected' : '' }}>Etsy</option>
                        <option value="amazon" {{ old('sales_channel') == 'amazon' ? 'selected' : '' }}>Amazon</option>
                        <option value="tiktok" {{ old('sales_channel') == 'tiktok' ? 'selected' : '' }}>TikTok</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Shipping Method</label>
                    <select name="shipping_method" class="w-full px-4 py-2 rounded-md border border-slate-300 focus:ring-primary focus:border-primary">
                        <option value="">-- Select Method --</option>
                        <option value="standard" {{ old('shipping_method') == 'standard' ? 'selected' : '' }}>Standard Shipping</option>
                        <option value="express" {{ old('shipping_method') == 'express' ? 'selected' : '' }}>Express Shipping</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Warehouse (Workshop) <span class="text-red-500">*</span></label>
                    <select 
                        name="workshop_id" 
                        id="workshop_id"
                        required
                        class="w-full px-4 py-2 rounded-md border border-slate-300 focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D]"
                    >
                        <option value="">-- Select Warehouse --</option>
                        @foreach($workshops as $workshop)
                            <option value="{{ $workshop->id }}" {{ old('workshop_id') == $workshop->id ? 'selected' : '' }}>
                                {{ $workshop->name }} ({{ $workshop->code }}) - {{ $workshop->market->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Order Note</label>
                    <textarea 
                        name="order_note" 
                        id="order_note"
                        class="w-full px-4 py-2 rounded-md border border-slate-300 h-24 resize-none focus:ring-primary focus:border-primary {{ old('sales_channel') == 'tiktok' ? '' : 'bg-slate-50 text-slate-400 cursor-not-allowed' }}"
                        placeholder="Only allowed when TikTok Label is selected"
                        {{ old('sales_channel') == 'tiktok' ? '' : 'disabled' }}
                    >{{ old('order_note', '') }}</textarea>
                </div>
            </div>
        </section>

        <!-- Shipping Information -->
        <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                <span class="material-symbols-outlined" style="color: #F7961D;">local_shipping</span>
                <h3 class="font-bold text-slate-900">Shipping Information</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Recipient Name <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="shipping_address[name]" 
                        value="{{ old('shipping_address.name', '') }}"
                        placeholder="Full name"
                        required
                        class="w-full px-4 py-2 rounded-md border border-slate-300 focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D]"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Recipient Email</label>
                    <input 
                        type="email" 
                        name="shipping_address[email]" 
                        value="{{ old('shipping_address.email', '') }}"
                        placeholder="email@example.com"
                        class="w-full px-4 py-2 rounded-md border border-slate-300 focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D]"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Phone Number</label>
                    <input 
                        type="tel" 
                        name="shipping_address[phone]" 
                        value="{{ old('shipping_address.phone', '') }}"
                        placeholder="e.g. +1 234 567 890"
                        class="w-full px-4 py-2 rounded-md border border-slate-300 focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D]"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Country <span class="text-red-500">*</span></label>
                    <select 
                        name="shipping_address[country]" 
                        required
                        class="w-full px-4 py-2 rounded-md border border-slate-300 focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D]"
                    >
                        <option value="">-- Select Country --</option>
                        <option value="US" {{ old('shipping_address.country') == 'US' ? 'selected' : '' }}>United States</option>
                        <option value="GB" {{ old('shipping_address.country') == 'GB' ? 'selected' : '' }}>United Kingdom</option>
                        <option value="CA" {{ old('shipping_address.country') == 'CA' ? 'selected' : '' }}>Canada</option>
                        <option value="AU" {{ old('shipping_address.country') == 'AU' ? 'selected' : '' }}>Australia</option>
                        <option value="VN" {{ old('shipping_address.country') == 'VN' ? 'selected' : '' }}>Vietnam</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Address <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="shipping_address[address]" 
                        value="{{ old('shipping_address.address', '') }}"
                        placeholder="Street address"
                        required
                        class="w-full px-4 py-2 rounded-md border border-slate-300 focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D]"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Address 2 (Optional)</label>
                    <input 
                        type="text" 
                        name="shipping_address[address2]" 
                        value="{{ old('shipping_address.address2', '') }}"
                        placeholder="Apartment, suite, etc."
                        class="w-full px-4 py-2 rounded-md border border-slate-300 focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D]"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">City <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="shipping_address[city]" 
                        value="{{ old('shipping_address.city', '') }}"
                        placeholder="City"
                        required
                        class="w-full px-4 py-2 rounded-md border border-slate-300 focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D]"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">State/Province</label>
                    <input 
                        type="text" 
                        name="shipping_address[state]" 
                        value="{{ old('shipping_address.state', '') }}"
                        placeholder="State/Province"
                        class="w-full px-4 py-2 rounded-md border border-slate-300 focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D]"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Postal Code <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="shipping_address[postal_code]" 
                        value="{{ old('shipping_address.postal_code', '') }}"
                        placeholder="Zip/Postal code"
                        required
                        class="w-full px-4 py-2 rounded-md border border-slate-300 focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D]"
                    >
                </div>
            </div>
        </section>

        <!-- Products -->
        <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined" style="color: #F7961D;">inventory</span>
                    <h3 class="font-bold text-slate-900">Products</h3>
                </div>
                <button 
                    type="button" 
                    onclick="addProduct()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-semibold flex items-center gap-1.5 transition-colors"
                >
                    <span class="material-symbols-outlined text-sm">add</span>
                    Add Product
                </button>
            </div>
            <div class="p-6 space-y-8" id="products-container">
                <!-- Product items will be added here dynamically -->
            </div>
        </section>
    </form>

    <!-- Footer with Action Buttons -->
    <footer class="fixed bottom-0 right-0 left-0 md:left-[260px] bg-white border-t border-slate-200 px-4 md:px-8 py-4 flex justify-end items-center gap-4 z-10 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
        <button 
            type="button"
            onclick="window.location.href='{{ route('admin.orders.index') }}'"
            class="px-6 py-2.5 border border-slate-300 text-slate-700 font-semibold rounded-md hover:bg-slate-50 transition-all"
        >
            Cancel
        </button>
        <button 
            type="submit"
            form="orderForm"
            class="px-8 py-2.5 bg-primary hover:bg-primary/90 text-white font-bold rounded-md shadow-lg shadow-primary/20 transition-all transform active:scale-95"
            style="background-color: #F7961D;"
        >
            Create Order
        </button>
    </footer>
</div>

<!-- Product Selection Modal -->
<div id="productModal" class="fixed inset-0 hidden items-center justify-center p-4" style="display: none; left: 0; z-index: 10000;">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeProductModal()" style="left: 0; z-index: 10000;"></div>
    <div class="relative bg-white w-full max-w-5xl h-[85vh] rounded-2xl shadow-2xl flex flex-col overflow-hidden border border-slate-200" style="margin-left: 0; z-index: 10001;">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-slate-900">Select Product</h3>
                <p class="text-xs text-slate-500">Choose a product from our fulfillment catalog</p>
            </div>
            <button onclick="closeProductModal()" class="p-2 hover:bg-slate-100 rounded-full text-slate-500 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="p-6 space-y-4 border-b border-slate-100">
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                <div class="relative w-full md:w-96">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                    <input 
                        id="productSearchInput"
                        type="text" 
                        class="w-full pl-10 pr-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D] text-sm" 
                        placeholder="Search by name, SKU or brand..." 
                        oninput="filterProducts()"
                    >
                </div>
                <div class="flex items-center gap-2 overflow-x-auto pb-2 md:pb-0 w-full md:w-auto custom-scrollbar">
                    <button onclick="setProductFilter('all')" class="product-filter-btn px-4 py-1.5 bg-[#F7961D] text-white text-sm font-semibold rounded-full whitespace-nowrap" data-filter="all">All Products</button>
                    <button onclick="setProductFilter('clothing')" class="product-filter-btn px-4 py-1.5 bg-slate-100 text-slate-600 hover:bg-slate-200 text-sm font-medium rounded-full whitespace-nowrap transition-colors" data-filter="clothing">Clothing</button>
                    <button onclick="setProductFilter('accessories')" class="product-filter-btn px-4 py-1.5 bg-slate-100 text-slate-600 hover:bg-slate-200 text-sm font-medium rounded-full whitespace-nowrap transition-colors" data-filter="accessories">Accessories</button>
                    <button onclick="setProductFilter('home')" class="product-filter-btn px-4 py-1.5 bg-slate-100 text-slate-600 hover:bg-slate-200 text-sm font-medium rounded-full whitespace-nowrap transition-colors" data-filter="home">Home & Living</button>
                    <button onclick="setProductFilter('wall-art')" class="product-filter-btn px-4 py-1.5 bg-slate-100 text-slate-600 hover:bg-slate-200 text-sm font-medium rounded-full whitespace-nowrap transition-colors" data-filter="wall-art">Wall Art</button>
                </div>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar bg-slate-50/50" id="productModalGrid">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="productGrid">
                <!-- Products will be loaded here -->
            </div>
        </div>
        <div class="px-6 py-4 border-t border-slate-200 flex justify-end items-center gap-3 bg-white">
            <button onclick="closeProductModal()" class="px-5 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-md transition-colors">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Variant Selection Modal -->
<div id="variantModal" class="fixed inset-0 hidden items-center justify-center p-4" style="display: none; left: 0; z-index: 10000;">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeVariantModal()" style="left: 0; z-index: 10000;"></div>
    <div class="relative bg-white w-full max-w-5xl h-[85vh] rounded-2xl shadow-2xl flex flex-col overflow-hidden border border-slate-200" style="margin-left: 0; z-index: 10001;">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-slate-900" id="variantModalTitle">Select Variant</h3>
                <p class="text-xs text-slate-500" id="variantModalSubtitle">Choose a variant for this product</p>
            </div>
            <button onclick="closeVariantModal()" class="p-2 hover:bg-slate-100 rounded-full text-slate-500 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar bg-slate-50/50" id="variantModalContent">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="variantGrid">
                <!-- Variants will be loaded here -->
            </div>
        </div>
        <div class="px-6 py-4 border-t border-slate-200 flex justify-between items-center gap-3 bg-white">
            <button onclick="backToProductModal()" class="px-5 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-md transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                Back
            </button>
            <div class="flex gap-3">
                <button onclick="selectNoVariant()" class="px-5 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-md transition-colors">
                    No Variant
                </button>
                <button onclick="selectVariantFromModal()" class="px-8 py-2 bg-[#F7961D] hover:bg-[#F7961D]/90 text-white font-bold rounded-md shadow-lg shadow-[#F7961D]/20 transition-all">
                    Select Variant
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>

@push('scripts')
<script>
let productIndex = 0;
const products = @json($products);
let currentProductModalIndex = null;
let selectedProductId = null;
let selectedVariantId = null;
let currentProductFilter = 'all';
let searchQuery = '';
let currentProductForVariant = null;

// Initialize with first product
document.addEventListener('DOMContentLoaded', function() {
    addProduct();
    
    // Handle sales channel change to enable/disable order note
    document.querySelector('[name="sales_channel"]').addEventListener('change', function() {
        const orderNote = document.getElementById('order_note');
        if (this.value === 'tiktok') {
            orderNote.disabled = false;
            orderNote.classList.remove('bg-slate-50', 'text-slate-400', 'cursor-not-allowed');
            orderNote.classList.add('bg-white', 'text-slate-900');
        } else {
            orderNote.disabled = true;
            orderNote.classList.remove('bg-white', 'text-slate-900');
            orderNote.classList.add('bg-slate-50', 'text-slate-400', 'cursor-not-allowed');
        }
    });
});

function addProduct() {
    const container = document.getElementById('products-container');
    const productHtml = `
        <div class="p-6 rounded-lg border border-slate-200 bg-slate-50/50 relative product-item" data-index="${productIndex}">
            <button 
                type="button" 
                onclick="removeProduct(this)"
                class="absolute top-4 right-4 text-red-500 hover:bg-red-50 p-1 rounded transition-colors"
            >
                <span class="material-symbols-outlined">delete</span>
            </button>
            <h4 class="font-bold text-slate-800 mb-4">Product #${productIndex + 1}</h4>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Select Product <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input 
                            type="hidden" 
                            name="items[${productIndex}][product_id]" 
                            class="product-id-input" 
                            required
                        >
                        <button 
                            type="button"
                            onclick="openProductModal(${productIndex})"
                            class="w-full flex items-center justify-between px-4 py-2 rounded-md border border-slate-300 text-left bg-white hover:border-[#F7961D] transition-colors product-select-btn"
                        >
                            <span class="product-select-text text-slate-500">Search and select product...</span>
                            <span class="material-symbols-outlined text-slate-400">search</span>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Variant (Optional)</label>
                    <select 
                        name="items[${productIndex}][variant_id]" 
                        id="variant-select-${productIndex}"
                        class="variant-select w-full px-4 py-2 rounded-md border border-slate-300 bg-slate-50 text-slate-400"
                    >
                        <option value="">Select product first</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Quantity <span class="text-red-500">*</span></label>
                    <input 
                        type="number" 
                        name="items[${productIndex}][quantity]" 
                        value="1" 
                        min="1" 
                        required
                        class="w-full px-4 py-2 rounded-md border border-slate-300 focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D]"
                    >
                </div>
            </div>
            
            <div class="md:col-span-3 mb-6">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Product Title</label>
                <input 
                    type="text" 
                    name="items[${productIndex}][product_title]" 
                    placeholder="Enter product title"
                        class="w-full px-4 py-2 rounded-md border border-slate-300 focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D]"
                >
            </div>
            
            <div class="mb-6">
                <div class="flex justify-between items-center mb-3">
                    <label class="text-sm font-semibold text-slate-700">Design * (at least 1)</label>
                    <button 
                        type="button"
                        onclick="addDesign(${productIndex})"
                        class="text-xs font-bold text-blue-600 hover:underline flex items-center gap-1"
                    >
                        <span class="material-symbols-outlined text-[12px]">add</span> Add Design
                    </button>
                </div>
                <div class="designs-container space-y-2" data-product="${productIndex}">
                    <div class="flex gap-3">
                        <input 
                            type="text" 
                            name="items[${productIndex}][designs][0][url]" 
                            placeholder="Design file URL" 
                            required
                            class="flex-1 px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D]"
                        >
                        <input 
                            type="text" 
                            name="items[${productIndex}][designs][0][position]" 
                            placeholder="Position (e.g. Front, Back)" 
                            required
                            class="w-1/3 px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D]"
                        >
                        <button 
                            type="button"
                            onclick="removeDesign(this)"
                            class="px-3 py-2 bg-red-100 text-red-600 rounded-md hover:bg-red-200 transition-colors"
                        >
                            <span class="material-symbols-outlined text-base">close</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <div>
                <div class="flex justify-between items-center mb-3">
                    <label class="text-sm font-semibold text-slate-700">Mockup * (at least 1)</label>
                    <button 
                        type="button"
                        onclick="addMockup(${productIndex})"
                        class="text-xs font-bold text-blue-600 hover:underline flex items-center gap-1"
                    >
                        <span class="material-symbols-outlined text-[12px]">add</span> Add Mockup
                    </button>
                </div>
                <div class="mockups-container space-y-2" data-product="${productIndex}">
                    <div class="flex gap-3">
                        <input 
                            type="text" 
                            name="items[${productIndex}][mockups][0][url]" 
                            placeholder="Mockup file URL" 
                            required
                            class="flex-1 px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D]"
                        >
                        <input 
                            type="text" 
                            name="items[${productIndex}][mockups][0][position]" 
                            placeholder="Position (e.g. S-Front, Tote-Back)" 
                            required
                            class="w-1/3 px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D]"
                        >
                        <button 
                            type="button"
                            onclick="removeMockup(this)"
                            class="px-3 py-2 bg-red-100 text-red-600 rounded-md hover:bg-red-200 transition-colors"
                        >
                            <span class="material-symbols-outlined text-base">close</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', productHtml);
    productIndex++;
    updateProductNumbers();
}

function removeProduct(button) {
    const productItem = button.closest('.product-item');
    productItem.remove();
    updateProductNumbers();
}

function updateProductNumbers() {
    const products = document.querySelectorAll('.product-item');
    products.forEach((item, index) => {
        item.querySelector('h4').textContent = `Product #${index + 1}`;
    });
}

function addDesign(productIndex) {
    const container = document.querySelector(`.designs-container[data-product="${productIndex}"]`);
    const designCount = container.children.length;
    const designHtml = `
        <div class="flex gap-3">
            <input 
                type="text" 
                name="items[${productIndex}][designs][${designCount}][url]" 
                placeholder="Design file URL" 
                required
                class="flex-1 px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-primary focus:border-primary"
            >
            <input 
                type="text" 
                name="items[${productIndex}][designs][${designCount}][position]" 
                placeholder="Position (e.g. Front, Back)" 
                required
                class="w-1/3 px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-primary focus:border-primary"
            >
            <button 
                type="button"
                onclick="removeDesign(this)"
                class="px-3 py-2 bg-red-100 text-red-600 rounded-md hover:bg-red-200 transition-colors"
            >
                <span class="material-symbols-outlined text-base">close</span>
            </button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', designHtml);
}

function removeDesign(button) {
    const container = button.closest('.designs-container');
    if (container.children.length > 1) {
        button.closest('.flex').remove();
    } else {
        alert('At least one design is required');
    }
}

function addMockup(productIndex) {
    const container = document.querySelector(`.mockups-container[data-product="${productIndex}"]`);
    const mockupCount = container.children.length;
    const mockupHtml = `
        <div class="flex gap-3">
            <input 
                type="text" 
                name="items[${productIndex}][mockups][${mockupCount}][url]" 
                placeholder="Mockup file URL" 
                required
                class="flex-1 px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-primary focus:border-primary"
            >
            <input 
                type="text" 
                name="items[${productIndex}][mockups][${mockupCount}][position]" 
                placeholder="Position (e.g. S-Front, Tote-Back)" 
                required
                class="w-1/3 px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-primary focus:border-primary"
            >
            <button 
                type="button"
                onclick="removeMockup(this)"
                class="px-3 py-2 bg-red-100 text-red-600 rounded-md hover:bg-red-200 transition-colors"
            >
                <span class="material-symbols-outlined text-base">close</span>
            </button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', mockupHtml);
}

function removeMockup(button) {
    const container = button.closest('.mockups-container');
    if (container.children.length > 1) {
        button.closest('.flex').remove();
    } else {
        alert('At least one mockup is required');
    }
}

function updateVariants(productIndex, productId) {
    const variantSelect = document.getElementById(`variant-select-${productIndex}`);
    if (!variantSelect) return;
    
    // Clear existing options
    variantSelect.innerHTML = '<option value="">Default / No Variant</option>';
    
    if (!productId) return;
    
    // Find product and its variants
    const product = products.find(p => p.id == productId);
    if (!product || !product.variants || product.variants.length === 0) {
        variantSelect.classList.add('bg-slate-50', 'text-slate-400');
        variantSelect.classList.remove('bg-white', 'text-slate-900');
        return;
    }
    
    // Enable select and update styling
    variantSelect.classList.remove('bg-slate-50', 'text-slate-400');
    variantSelect.classList.add('bg-white', 'text-slate-900');
    
    // Add variant options
    product.variants.forEach(variant => {
        // Build variant display name from attributes
        let displayName = variant.display_name || variant.sku || 'Variant';
        
        // Check both variant_attributes and attributes for compatibility
        const attributes = variant.variant_attributes || variant.attributes || [];
        if (attributes.length > 0) {
            const attrs = attributes.map(attr => {
                // Handle both object and array formats
                const attrName = attr.attribute_name || attr.name || 'Unknown';
                const attrValue = attr.attribute_value || attr.value || '';
                return `${attrName}: ${attrValue}`;
            }).join(', ');
            displayName = `${displayName} (${attrs})`;
        }
        
        const option = document.createElement('option');
        option.value = variant.id;
        option.textContent = displayName;
        variantSelect.appendChild(option);
    });
}

// Modal functions
function openProductModal(productItemIndex) {
    currentProductModalIndex = productItemIndex;
    selectedProductId = null;
    const modal = document.getElementById('productModal');
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
    renderProductModal();
}

function closeProductModal() {
    const modal = document.getElementById('productModal');
    modal.classList.add('hidden');
    modal.style.display = 'none';
    currentProductModalIndex = null;
    selectedProductId = null;
}

function renderProductModal() {
    const grid = document.getElementById('productGrid');
    grid.innerHTML = '';
    
    const filteredProducts = getFilteredProducts();
    
    filteredProducts.forEach(product => {
        const productCard = document.createElement('div');
        
        // Get image URL - check both url and image_path
        let imageUrl = 'https://via.placeholder.com/300x300?text=No+Image';
        if (product.images && product.images.length > 0) {
            const firstImage = product.images[0];
            if (firstImage.url) {
                imageUrl = firstImage.url;
            } else if (firstImage.image_path) {
                // If no URL accessor, construct it manually
                imageUrl = '/storage/' + firstImage.image_path;
            }
        }
        
        const warehouseName = product.workshop && product.workshop.market 
            ? product.workshop.market.name + ' Warehouse'
            : 'N/A';
        
        productCard.className = `group bg-white border-2 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all cursor-pointer relative ${selectedProductId == product.id ? 'border-[#F7961D]' : 'border-slate-200'}`;
        productCard.onclick = () => selectProductInModal(product.id);
        
        productCard.innerHTML = `
            ${selectedProductId == product.id ? `
                <div class="absolute top-2 right-2 z-10">
                    <span class="bg-[#F7961D] text-white p-1 rounded-full flex items-center justify-center">
                        <span class="material-symbols-outlined text-sm">check</span>
                    </span>
                </div>
            ` : ''}
            <div class="aspect-square bg-slate-100 overflow-hidden relative">
                <img alt="${product.name}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" src="${imageUrl}" onerror="this.src='https://via.placeholder.com/300x300?text=No+Image'">
            </div>
            <div class="p-4">
                <h4 class="font-bold text-slate-900 text-sm line-clamp-1 mb-2">${product.name}</h4>
                <div class="flex items-center gap-1 text-[10px] text-slate-500 bg-slate-100 px-2 py-0.5 rounded">
                    <span class="material-symbols-outlined text-xs">location_on</span>
                    ${warehouseName}
                </div>
            </div>
        `;
        
        grid.appendChild(productCard);
    });
}

function getFilteredProducts() {
    let filtered = products;
    
    // Apply search filter
    if (searchQuery) {
        const query = searchQuery.toLowerCase();
        filtered = filtered.filter(p => 
            p.name.toLowerCase().includes(query) ||
            (p.sku && p.sku.toLowerCase().includes(query))
        );
    }
    
    // Apply category filter (if implemented)
    // For now, just return all products
    
    return filtered;
}

function filterProducts() {
    searchQuery = document.getElementById('productSearchInput').value;
    renderProductModal();
}

function setProductFilter(filter) {
    currentProductFilter = filter;
    
    // Update button styles
    document.querySelectorAll('.product-filter-btn').forEach(btn => {
        if (btn.dataset.filter === filter) {
            btn.classList.remove('bg-slate-100', 'text-slate-600');
            btn.classList.add('bg-[#F7961D]', 'text-white');
        } else {
            btn.classList.remove('bg-[#F7961D]', 'text-white');
            btn.classList.add('bg-slate-100', 'text-slate-600');
        }
    });
    
    // Filter products (you can implement category filtering here)
    renderProductModal();
}

function selectProductInModal(productId) {
    selectedProductId = productId;
    const product = products.find(p => p.id == productId);
    if (!product) return;
    
    // Check if product has variants
    if (product.variants && product.variants.length > 0) {
        // Open variant selection modal
        currentProductForVariant = product;
        selectedVariantId = null;
        openVariantModal();
    } else {
        // No variants, directly select product
        confirmProductSelection(productId, null);
    }
}

function confirmProductSelection(productId, variantId) {
    const product = products.find(p => p.id == productId);
    if (!product) return;
    
    // Update the hidden input and button text
    const productItem = document.querySelector(`.product-item[data-index="${currentProductModalIndex}"]`);
    if (productItem) {
        const productIdInput = productItem.querySelector('.product-id-input');
        const variantSelect = document.getElementById(`variant-select-${currentProductModalIndex}`);
        const productSelectBtn = productItem.querySelector('.product-select-btn');
        const productSelectText = productItem.querySelector('.product-select-text');
        
        if (productIdInput) productIdInput.value = productId;
        if (variantSelect && variantId) {
            variantSelect.value = variantId;
            variantSelect.classList.remove('bg-slate-50', 'text-slate-400');
            variantSelect.classList.add('bg-white', 'text-slate-900');
        }
        if (productSelectText) {
            const variantText = variantId ? ` (Variant selected)` : '';
            productSelectText.textContent = product.name + variantText;
            productSelectText.classList.remove('text-slate-500');
            productSelectText.classList.add('text-slate-900');
        }
        
        // Update variants dropdown
        updateVariants(currentProductModalIndex, productId);
        if (variantId && variantSelect) {
            variantSelect.value = variantId;
        }
    }
    
    closeProductModal();
    closeVariantModal();
}

// Variant Modal functions
function openVariantModal() {
    if (!currentProductForVariant) return;
    
    const modal = document.getElementById('variantModal');
    const title = document.getElementById('variantModalTitle');
    const subtitle = document.getElementById('variantModalSubtitle');
    
    // Hide product modal
    document.getElementById('productModal').style.display = 'none';
    
    // Show variant modal
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
    
    // Update modal title
    if (title) title.textContent = `Select Variant - ${currentProductForVariant.name}`;
    if (subtitle) subtitle.textContent = 'Choose a variant for this product';
    
    renderVariantModal();
}

function closeVariantModal() {
    const modal = document.getElementById('variantModal');
    modal.classList.add('hidden');
    modal.style.display = 'none';
    selectedVariantId = null;
    currentProductForVariant = null;
}

function backToProductModal() {
    closeVariantModal();
    const productModal = document.getElementById('productModal');
    productModal.style.display = 'flex';
}

function renderVariantModal() {
    if (!currentProductForVariant) return;
    
    const grid = document.getElementById('variantGrid');
    grid.innerHTML = '';
    
    const product = currentProductForVariant;
    
    if (!product.variants || product.variants.length === 0) {
        grid.innerHTML = `
            <div class="col-span-full text-center py-12">
                <p class="text-slate-500">This product has no variants available.</p>
            </div>
        `;
        return;
    }
    
    product.variants.forEach(variant => {
        const variantCard = document.createElement('div');
        
        // Build variant display info
        let variantDisplay = variant.display_name || variant.sku || 'Variant';
        let attributesText = '';
        
        // Check both variant_attributes and attributes for compatibility
        const attributes = variant.variant_attributes || variant.attributes || [];
        if (attributes.length > 0) {
            const attrs = attributes.map(attr => {
                // Handle both object and array formats
                const attrName = attr.attribute_name || attr.name || 'Unknown';
                const attrValue = attr.attribute_value || attr.value || '';
                return `${attrName}: ${attrValue}`;
            }).join(', ');
            attributesText = attrs;
            variantDisplay = `${variantDisplay} (${attrs})`;
        }
        
        // Get image from product (variants don't have their own images)
        let imageUrl = 'https://via.placeholder.com/300x300?text=No+Image';
        if (product.images && product.images.length > 0) {
            const firstImage = product.images[0];
            if (firstImage.url) {
                imageUrl = firstImage.url;
            } else if (firstImage.image_path) {
                imageUrl = '/storage/' + firstImage.image_path;
            }
        }
        
        const sku = variant.sku || 'N/A';
        
        variantCard.className = `group bg-white border-2 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all cursor-pointer relative ${selectedVariantId == variant.id ? 'border-[#F7961D]' : 'border-slate-200'}`;
        variantCard.onclick = () => selectVariantInModal(variant.id);
        
        variantCard.innerHTML = `
            ${selectedVariantId == variant.id ? `
                <div class="absolute top-2 right-2 z-10">
                    <span class="bg-[#F7961D] text-white p-1 rounded-full flex items-center justify-center">
                        <span class="material-symbols-outlined text-sm">check</span>
                    </span>
                </div>
            ` : ''}
            <div class="aspect-square bg-slate-100 overflow-hidden relative">
                <img alt="${variantDisplay}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" src="${imageUrl}" onerror="this.src='https://via.placeholder.com/300x300?text=No+Image'">
            </div>
            <div class="p-4">
                <h4 class="font-bold text-slate-900 text-sm line-clamp-2 mb-2">${variantDisplay}</h4>
                ${attributesText ? `<p class="text-xs text-slate-500 mb-2">${attributesText}</p>` : ''}
                <div class="mt-2 flex items-center justify-end">
                    <div class="flex items-center gap-1 text-[10px] text-slate-500 bg-slate-100 px-2 py-0.5 rounded">
                        <span class="material-symbols-outlined text-xs">tag</span>
                        ${sku}
                    </div>
                </div>
            </div>
        `;
        
        grid.appendChild(variantCard);
    });
}

function selectVariantInModal(variantId) {
    selectedVariantId = variantId;
    renderVariantModal();
}

function selectNoVariant() {
    if (!selectedProductId) return;
    confirmProductSelection(selectedProductId, null);
}

function selectVariantFromModal() {
    if (!selectedProductId || !selectedVariantId) {
        alert('Please select a variant or choose "No Variant"');
        return;
    }
    
    confirmProductSelection(selectedProductId, selectedVariantId);
}

// Handle ESC key to close modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const variantModal = document.getElementById('variantModal');
        if (variantModal.style.display === 'flex') {
            backToProductModal();
        } else {
            closeProductModal();
        }
    }
});
</script>
@endpush
@endsection

@php
    $activeMenu = 'orders';
@endphp

