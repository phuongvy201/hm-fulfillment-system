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

    <form method="POST" action="{{ route($routePrefix . '.orders.store') }}" id="orderForm" enctype="multipart/form-data">
            @csrf

        <!-- Order Information -->
        <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                <span class="material-symbols-outlined" style="color: #F7961D;">info</span>
                <h3 class="font-bold text-slate-900">Order Information</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                @if(isset($isCustomer) && $isCustomer)
                    <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                @else
                <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Customer</label>
                            <select 
                                name="user_id"
                                id="user_id_select"
                                class="w-full px-4 py-2 rounded-md border border-slate-300 focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D]"
                            >
                                <option value="">Select Customer (Optional)</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-slate-500">If no customer selected, wallet will not be charged</p>
                        </div>
                @endif
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
                    <select name="shipping_method" id="shipping_method" class="w-full px-4 py-2 rounded-md border border-slate-300 focus:ring-primary focus:border-primary">
                        <option value="">-- Select Method --</option>
                        <option value="tiktok_label" {{ old('shipping_method') == 'tiktok_label' ? 'selected' : '' }}>TikTok Label</option>
                        <option value="standard" {{ old('shipping_method') == 'standard' ? 'selected' : '' }}>Standard Shipping</option>
                        <option value="express" {{ old('shipping_method') == 'express' ? 'selected' : '' }}>Express Shipping</option>
                            </select>
                                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">TikTok Label URL <span class="text-red-500" id="tiktok_label_required" style="display: none;">*</span></label>
                    <input 
                        type="url" 
                        name="tiktok_label_url" 
                        id="tiktok_label_url"
                        value="{{ old('tiktok_label_url', '') }}"
                        placeholder="https://drive.google.com/..."
                        class="w-full px-4 py-2 rounded-md border border-slate-300 bg-slate-50 text-slate-400 cursor-not-allowed focus:ring-primary focus:border-primary"
                        disabled
                    >
                    <p class="mt-1 text-xs text-slate-500">Chỉ chấp nhận link Google Drive. Trường này chỉ bắt buộc khi Shipping Method = TikTok Label.</p>
                                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Order Note</label>
                    <textarea 
                        name="order_note" 
                        id="order_note"
                        class="w-full px-4 py-2 rounded-md border border-slate-300 h-24 resize-none focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D]"
                        placeholder="Enter any additional notes or instructions for this order"
                    >{{ old('order_note', '') }}</textarea>
                            </div>
                @if(!isset($isCustomer) || !$isCustomer)
                <div class="md:col-span-2" id="charge_wallet_container" style="display: none;">
                    <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-lg border border-slate-200">
                        <input 
                            type="checkbox" 
                            name="charge_wallet" 
                            id="charge_wallet"
                            value="1"
                            {{ old('charge_wallet', '1') ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-slate-300 text-[#F7961D] focus:ring-[#F7961D] focus:ring-2"
                        >
                        <label for="charge_wallet" class="text-sm font-medium text-slate-700 cursor-pointer">
                            Charge customer wallet
                        </label>
                        <p class="text-xs text-slate-500 ml-auto">If unchecked, payment will remain pending and can be processed later</p>
                        </div>
                    </div>
                @endif
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

        @if(isset($isCustomer) && $isCustomer)
        <!-- Products (Customer View) -->
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
        @else
        <!-- Products (Staff/Admin View) -->
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

        <!-- Pricing Information (Staff/Admin View) -->
        <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                <span class="material-symbols-outlined" style="color: #F7961D;">attach_money</span>
                <h3 class="font-bold text-slate-900">Pricing Information</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">SKU / Product Code (Optional)</label>
                        <input 
                            type="text" 
                            name="sku" 
                            value="{{ old('sku', '') }}"
                            placeholder="Enter SKU or product code (e.g. GD05-BLAC-S)"
                            class="w-full px-4 py-2 rounded-md border border-slate-300 focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D]"
                        >
                        <p class="mt-1 text-xs text-slate-500">Enter SKU directly. No validation required.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Total Amount <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-semibold">USD</span>
                            <input 
                                type="number" 
                                name="total_amount" 
                                step="0.01"
                                min="0"
                                value="{{ old('total_amount', '') }}"
                                placeholder="0.00"
                                required
                                class="w-full pl-16 pr-4 py-2 rounded-md border border-slate-300 focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D]"
                            >
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Enter total order amount. Currency is fixed as USD.</p>
                    </div>
                </div>
            </div>
        </section>
        @endif
    </form>

    <!-- Footer with Action Buttons -->
    <footer class="fixed bottom-0 right-0 left-0 md:left-[260px] bg-white border-t border-slate-200 px-4 md:px-8 py-4 flex justify-end items-center gap-4 z-10 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
        <button 
            type="button"
            onclick="window.location.href='{{ route($routePrefix . '.orders.index') }}'"
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
const isCustomer = @json(isset($isCustomer) && $isCustomer);

// Store old input data for restoration
const oldInputData = @json(old('items', []));
const allProducts = @json($products ?? []);

// Function to restore product item from old input
function restoreProduct(itemIndex, itemData) {
    // Find product item by data-index attribute
    const productItem = document.querySelector(`.product-item[data-index="${itemIndex}"]`);
    
    if (!productItem || !itemData) return;
    
    // Check if SKU is provided (for staff/admin)
    const skuInput = productItem.querySelector(`input[name="items[${itemIndex}][sku]"]`);
    if (itemData.sku && skuInput) {
        // Restore SKU mode
        setProductSelectionMode(itemIndex, 'sku');
        setTimeout(() => {
            skuInput.value = itemData.sku;
        }, 50);
    }
    
    // Restore product selection
    const productIdInput = productItem.querySelector(`input[name="items[${itemIndex}][product_id]"]`);
    const productSelectText = productItem.querySelector('.product-select-text');
    const variantSelect = productItem.querySelector(`select[name="items[${itemIndex}][variant_id]"]`);
    
    if (itemData.product_id && productIdInput && !itemData.sku) {
        // Only restore product selection if not in SKU mode
        productIdInput.value = itemData.product_id;
        
        // Find product name from products data
        const product = products.find(p => p.id == itemData.product_id);
        if (product && productSelectText) {
            productSelectText.textContent = product.name;
            productSelectText.classList.remove('text-slate-500');
            productSelectText.classList.add('text-slate-900');
        }
        
        // Update variants dropdown
        if (typeof updateVariants === 'function') {
            updateVariants(itemIndex, itemData.product_id);
        }
        
        // Restore variant after variants are loaded
        setTimeout(() => {
            if (variantSelect && itemData.variant_id) {
                variantSelect.value = itemData.variant_id;
                variantSelect.classList.remove('bg-slate-50', 'text-slate-400');
                variantSelect.classList.add('bg-white', 'text-slate-900');
            }
        }, 600);
    }
    
    // Restore quantity
    const quantityInput = productItem.querySelector(`input[name="items[${itemIndex}][quantity]"]`);
    if (quantityInput && itemData.quantity) {
        quantityInput.value = itemData.quantity;
    }
    
    // Restore product title
    const productTitleInput = productItem.querySelector(`input[name="items[${itemIndex}][product_title]"]`);
    if (productTitleInput && itemData.product_title) {
        productTitleInput.value = itemData.product_title;
    }
    
    // Restore designs
    if (itemData.designs && itemData.designs.length > 0) {
        const designsContainer = productItem.querySelector(`.designs-container[data-product="${itemIndex}"]`);
        if (designsContainer) {
            // Clear all existing designs first
            designsContainer.innerHTML = '';
            
            // Restore all designs
            itemData.designs.forEach((design, designIndex) => {
                if (designIndex === 0) {
                    // First design - create it
                    const designUrl = (design.url || '').replace(/"/g, '&quot;');
                    const designPosition = (design.position || '').replace(/"/g, '&quot;');
                    const firstDesignHtml = `
                        <div class="design-item border border-slate-200 rounded-lg p-3 bg-white">
                            <div class="flex items-center gap-2 mb-2">
                                <button type="button" onclick="toggleDesignInput(this, ${itemIndex}, ${designIndex})" class="text-xs px-2 py-1 rounded bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                                    <span class="input-mode-text">Switch to File Upload</span>
                                </button>
                            </div>
                            <div class="flex gap-3">
                                <div class="flex-1 design-input-wrapper" data-mode="url">
                                    <input 
                                        type="text" 
                                        name="items[${itemIndex}][designs][${designIndex}][url]" 
                                        value="${designUrl}"
                                        placeholder="Design PNG URL (Google Drive supported)" 
                                        required
                                        class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D] design-url-input"
                                        data-design-index="${designIndex}"
                                        onchange="validateDesignUrl(this)"
                                    >
                                </div>
                                <input 
                                    type="text" 
                                    name="items[${itemIndex}][designs][${designIndex}][position]" 
                                    value="${designPosition}"
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
                    `;
                    designsContainer.insertAdjacentHTML('beforeend', firstDesignHtml);
                } else {
                    // Additional designs - use addDesign function
                    addDesign(itemIndex);
                    // Update the newly added design with values
                    setTimeout(() => {
                        const newDesigns = designsContainer.querySelectorAll('.design-item');
                        const newDesign = newDesigns[newDesigns.length - 1];
                        if (newDesign) {
                            const urlInput = newDesign.querySelector('input[name*="[url]"]');
                            const positionInput = newDesign.querySelector('input[name*="[position]"]');
                            if (urlInput && design.url) {
                                urlInput.value = design.url;
                            }
                            if (positionInput && design.position) {
                                positionInput.value = design.position;
                            }
                        }
                    }, 50);
                }
            });
        }
    }
    
    // Restore mockups
    if (itemData.mockups && itemData.mockups.length > 0) {
        const mockupsContainer = productItem.querySelector(`.mockups-container[data-product="${itemIndex}"]`);
        if (mockupsContainer) {
            // Clear all existing mockups first
            mockupsContainer.innerHTML = '';
            
            // Restore all mockups
            itemData.mockups.forEach((mockup, mockupIndex) => {
                if (mockupIndex === 0) {
                    // First mockup - create it
                    const mockupUrl = (mockup.url || '').replace(/"/g, '&quot;');
                    const mockupPosition = (mockup.position || '').replace(/"/g, '&quot;');
                    const firstMockupHtml = `
                        <div class="mockup-item border border-slate-200 rounded-lg p-3 bg-white">
                            <div class="flex items-center gap-2 mb-2">
                                <button type="button" onclick="toggleMockupInput(this, ${itemIndex}, ${mockupIndex})" class="text-xs px-2 py-1 rounded bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                                    <span class="input-mode-text">Switch to File Upload</span>
                                </button>
                            </div>
                            <div class="flex gap-3">
                                <div class="flex-1 mockup-input-wrapper" data-mode="url">
                                    <input 
                                        type="text" 
                                        name="items[${itemIndex}][mockups][${mockupIndex}][url]" 
                                        value="${mockupUrl}"
                                        placeholder="Mockup URL or Google Drive link (All image formats)" 
                                        required
                                        class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D] mockup-url-input"
                                        data-mockup-index="${mockupIndex}"
                                        onchange="validateMockupUrl(this)"
                                    >
                                </div>
                                <input 
                                    type="text" 
                                    name="items[${itemIndex}][mockups][${mockupIndex}][position]" 
                                    value="${mockupPosition}"
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
                    `;
                    mockupsContainer.insertAdjacentHTML('beforeend', firstMockupHtml);
                } else {
                    // Additional mockups - use addMockup function
                    addMockup(itemIndex);
                    // Update the newly added mockup with values
                    setTimeout(() => {
                        const newMockups = mockupsContainer.querySelectorAll('.mockup-item');
                        const newMockup = newMockups[newMockups.length - 1];
                        if (newMockup) {
                            const urlInput = newMockup.querySelector('input[name*="[url]"]');
                            const positionInput = newMockup.querySelector('input[name*="[position]"]');
                            if (urlInput && mockup.url) {
                                urlInput.value = mockup.url;
                            }
                            if (positionInput && mockup.position) {
                                positionInput.value = mockup.position;
                            }
                        }
                    }, 50);
                }
            });
        }
    }
}

// Initialize with products from old input or add first product
document.addEventListener('DOMContentLoaded', function() {
    // Handle customer selection to show/hide charge wallet checkbox (for staff/admin)
    const userSelect = document.getElementById('user_id_select');
    const chargeWalletContainer = document.getElementById('charge_wallet_container');
    const chargeWalletCheckbox = document.getElementById('charge_wallet');
    
    function updateChargeWalletVisibility() {
        if (userSelect && chargeWalletContainer) {
            if (userSelect.value) {
                // Customer selected - show checkbox
                chargeWalletContainer.style.display = 'block';
            } else {
                // No customer - hide checkbox and uncheck it
                chargeWalletContainer.style.display = 'none';
                if (chargeWalletCheckbox) {
                    chargeWalletCheckbox.checked = false;
                }
            }
        }
    }
    
    if (userSelect) {
        userSelect.addEventListener('change', updateChargeWalletVisibility);
        // Initialize on page load
        updateChargeWalletVisibility();
    }
    
    // Restore products from old input if available (for both customer and staff/admin views)
    const productsContainer = document.getElementById('products-container');
    if (productsContainer) {
        // Initialize products container
        if (oldInputData && oldInputData.length > 0) {
            // Add first product container
            addProduct();
            
            // Add additional product containers if needed
            for (let i = 1; i < oldInputData.length; i++) {
                addProduct();
            }
            
            // Now restore data for each product (with delay to ensure DOM is ready)
            setTimeout(() => {
                oldInputData.forEach((item, index) => {
                    restoreProduct(index, item);
                });
            }, 200);
        } else {
            // No old input - add first product
            addProduct();
        }
    }
    
    // Handle shipping method change to enable/disable TikTok Label URL
    const shippingMethodSelect = document.getElementById('shipping_method');
    const tiktokLabelUrl = document.getElementById('tiktok_label_url');
    const tiktokLabelRequired = document.getElementById('tiktok_label_required');
    
    function updateTiktokLabelField() {
        if (!shippingMethodSelect || !tiktokLabelUrl || !tiktokLabelRequired) {
            return; // Elements not found, skip
        }
        
        if (shippingMethodSelect.value === 'tiktok_label') {
            tiktokLabelUrl.disabled = false;
            tiktokLabelUrl.required = true;
            tiktokLabelUrl.classList.remove('bg-slate-50', 'text-slate-400', 'cursor-not-allowed');
            tiktokLabelUrl.classList.add('bg-white', 'text-slate-900');
            tiktokLabelRequired.style.display = 'inline';
        } else {
            tiktokLabelUrl.disabled = true;
            tiktokLabelUrl.required = false;
            tiktokLabelUrl.value = '';
            tiktokLabelUrl.classList.remove('bg-white', 'text-slate-900');
            tiktokLabelUrl.classList.add('bg-slate-50', 'text-slate-400', 'cursor-not-allowed');
            tiktokLabelRequired.style.display = 'none';
        }
    }
    
    if (shippingMethodSelect && tiktokLabelUrl && tiktokLabelRequired) {
        shippingMethodSelect.addEventListener('change', updateTiktokLabelField);
        // Initialize on page load
        updateTiktokLabelField();
    }
    
    // Validate TikTok Label URL (must be Google Drive link)
    if (tiktokLabelUrl) {
        tiktokLabelUrl.addEventListener('blur', function() {
            if (this.value) {
                // Validate URL format
                if (!this.value.match(/^https?:\/\/.+/)) {
                    this.setCustomValidity('TikTok Label URL must be a valid URL.');
                    this.reportValidity();
                    return;
                }
                if (!this.value.includes('drive.google.com')) {
                    this.setCustomValidity('TikTok Label URL must be a Google Drive link.');
                    this.reportValidity();
                } else {
                    this.setCustomValidity('');
                }
            } else {
                this.setCustomValidity('');
            }
        });
    }
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
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">${isCustomer ? 'Select Product' : 'Product Selection'} <span class="text-red-500">*</span></label>
                    ${!isCustomer ? `
                    <div class="mb-2 flex gap-2">
                        <button 
                            type="button"
                            onclick="setProductSelectionMode(${productIndex}, 'select')"
                            class="product-selection-mode-btn px-3 py-1.5 text-xs font-semibold rounded-md transition-colors bg-[#F7961D] text-white"
                            data-mode="select"
                            data-product-index="${productIndex}"
                        >
                            Select Product
                        </button>
                        <button 
                            type="button"
                            onclick="setProductSelectionMode(${productIndex}, 'sku')"
                            class="product-selection-mode-btn px-3 py-1.5 text-xs font-semibold rounded-md transition-colors bg-slate-100 text-slate-600 hover:bg-slate-200"
                            data-mode="sku"
                            data-product-index="${productIndex}"
                        >
                            Enter SKU
                        </button>
                    </div>
                    ` : ''}
                    <div class="product-selection-container" data-product-index="${productIndex}">
                        <input 
                            type="hidden" 
                            name="items[${productIndex}][product_id]" 
                            class="product-id-input" 
                            ${isCustomer ? 'required' : ''}
                        >
                        <div class="product-select-wrapper" data-mode="select">
                            <button 
                                type="button"
                                onclick="openProductModal(${productIndex})"
                                class="w-full flex items-center justify-between px-4 py-2 rounded-md border border-slate-300 text-left bg-white hover:border-[#F7961D] transition-colors product-select-btn"
                            >
                                <span class="product-select-text text-slate-500">Search and select product...</span>
                                <span class="material-symbols-outlined text-slate-400">search</span>
                            </button>
                        </div>
                        ${!isCustomer ? `
                        <div class="product-sku-wrapper hidden" data-mode="sku">
                            <input 
                                type="text" 
                                name="items[${productIndex}][sku]" 
                                placeholder="Enter SKU (e.g. GD05-BLAC-S)" 
                                class="w-full px-4 py-2 rounded-md border border-slate-300 focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D] product-sku-input"
                                data-product-index="${productIndex}"
                            >
                            <p class="mt-1 text-xs text-slate-500">Enter SKU directly without selecting product</p>
                        </div>
                        ` : ''}
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
                    <label class="text-sm font-semibold text-slate-700">Design * (at least 1 - PNG only)</label>
                    <button 
                        type="button"
                        onclick="addDesign(${productIndex})"
                        class="text-xs font-bold text-blue-600 hover:underline flex items-center gap-1"
                    >
                        <span class="material-symbols-outlined text-[12px]">add</span> Add Design
                    </button>
                </div>
                <div class="designs-container space-y-2" data-product="${productIndex}">
                    <div class="design-item border border-slate-200 rounded-lg p-3 bg-white">
                        <div class="flex items-center gap-2 mb-2">
                            <button type="button" onclick="toggleDesignInput(this, ${productIndex}, 0)" class="text-xs px-2 py-1 rounded bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                                <span class="input-mode-text">Switch to File Upload</span>
                            </button>
                        </div>
                        <div class="flex gap-3">
                            <div class="flex-1 design-input-wrapper" data-mode="url">
                                <input 
                                    type="text" 
                                    name="items[${productIndex}][designs][0][url]" 
                                    placeholder="Design PNG URL (Google Drive supported)" 
                                    required
                                    accept=".png"
                                    class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D] design-url-input"
                                    data-design-index="0"
                                    onchange="validateDesignUrl(this)"
                                >
                            </div>
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
                </div>

            <div>
                <div class="flex justify-between items-center mb-3">
                    <label class="text-sm font-semibold text-slate-700">Mockup * (at least 1 - All image formats)</label>
                    <button 
                        type="button"
                        onclick="addMockup(${productIndex})"
                        class="text-xs font-bold text-blue-600 hover:underline flex items-center gap-1"
                    >
                        <span class="material-symbols-outlined text-[12px]">add</span> Add Mockup
                    </button>
            </div>
                <div class="mockups-container space-y-2" data-product="${productIndex}">
                    <div class="mockup-item border border-slate-200 rounded-lg p-3 bg-white">
                        <div class="flex items-center gap-2 mb-2">
                            <button type="button" onclick="toggleMockupInput(this, ${productIndex}, 0)" class="text-xs px-2 py-1 rounded bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                                <span class="input-mode-text">Switch to File Upload</span>
                </button>
            </div>
                        <div class="flex gap-3">
                            <div class="flex-1 mockup-input-wrapper" data-mode="url">
                                <input 
                                    type="text" 
                                    name="items[${productIndex}][mockups][0][url]" 
                                    placeholder="Mockup URL or Google Drive link (All image formats)" 
                                    required
                                    class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D] mockup-url-input"
                                    data-mockup-index="0"
                                    onchange="validateMockupUrl(this)"
                                >
    </div>
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

// Toggle between Select Product and Enter SKU modes (for staff/admin)
function setProductSelectionMode(productIndex, mode) {
    const productItem = document.querySelector(`.product-item[data-index="${productIndex}"]`);
    if (!productItem) return;
    
    const container = productItem.querySelector('.product-selection-container');
    const selectWrapper = productItem.querySelector('.product-select-wrapper');
    const skuWrapper = productItem.querySelector('.product-sku-wrapper');
    const productIdInput = productItem.querySelector('.product-id-input');
    const skuInput = productItem.querySelector('.product-sku-input');
    const modeButtons = productItem.querySelectorAll('.product-selection-mode-btn');
    
    // Update button styles
    modeButtons.forEach(btn => {
        if (btn.dataset.mode === mode) {
            btn.classList.remove('bg-slate-100', 'text-slate-600', 'hover:bg-slate-200');
            btn.classList.add('bg-[#F7961D]', 'text-white');
        } else {
            btn.classList.remove('bg-[#F7961D]', 'text-white');
            btn.classList.add('bg-slate-100', 'text-slate-600', 'hover:bg-slate-200');
        }
    });
    
    if (mode === 'select') {
        // Show select product button, hide SKU input
        if (selectWrapper) {
            selectWrapper.classList.remove('hidden');
        }
        if (skuWrapper) {
            skuWrapper.classList.add('hidden');
            if (skuInput) {
                skuInput.value = '';
                skuInput.removeAttribute('required');
            }
        }
        // Make product_id required
        if (productIdInput) {
            productIdInput.setAttribute('required', 'required');
        }
    } else {
        // Show SKU input, hide select product button
        if (selectWrapper) {
            selectWrapper.classList.add('hidden');
        }
        if (skuWrapper) {
            skuWrapper.classList.remove('hidden');
            if (skuInput) {
                skuInput.setAttribute('required', 'required');
            }
        }
        // Remove product_id requirement
        if (productIdInput) {
            productIdInput.removeAttribute('required');
            productIdInput.value = '';
        }
    }
}

function addDesign(productIndex) {
    const container = document.querySelector(`.designs-container[data-product="${productIndex}"]`);
    const designCount = container.children.length;
    const designHtml = `
        <div class="design-item border border-slate-200 rounded-lg p-3 bg-white">
            <div class="flex items-center gap-2 mb-2">
                <button type="button" onclick="toggleDesignInput(this, ${productIndex}, ${designCount})" class="text-xs px-2 py-1 rounded bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                    <span class="input-mode-text">Switch to File Upload</span>
                </button>
            </div>
            <div class="flex gap-3">
                <div class="flex-1 design-input-wrapper" data-mode="url">
                    <input 
                        type="text" 
                        name="items[${productIndex}][designs][${designCount}][url]" 
                        placeholder="Design PNG URL (Google Drive supported)" 
                        required
                        accept=".png"
                        class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D] design-url-input"
                        data-design-index="${designCount}"
                        onchange="validateDesignUrl(this)"
                    >
            </div>
                <input 
                    type="text" 
                    name="items[${productIndex}][designs][${designCount}][position]" 
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
        <div class="mockup-item border border-slate-200 rounded-lg p-3 bg-white">
            <div class="flex items-center gap-2 mb-2">
                <button type="button" onclick="toggleMockupInput(this, ${productIndex}, ${mockupCount})" class="text-xs px-2 py-1 rounded bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                    <span class="input-mode-text">Switch to File Upload</span>
                </button>
            </div>
            <div class="flex gap-3">
                <div class="flex-1 mockup-input-wrapper" data-mode="url">
                    <input 
                        type="text" 
                        name="items[${productIndex}][mockups][${mockupCount}][url]" 
                        placeholder="Mockup URL or Google Drive link (All image formats)" 
                        required
                        class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D] mockup-url-input"
                        data-mockup-index="${mockupCount}"
                        onchange="validateMockupUrl(this)"
                    >
            </div>
                <input 
                    type="text" 
                    name="items[${productIndex}][mockups][${mockupCount}][position]" 
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

// Toggle between file upload and URL input for Design
function toggleDesignInput(button, productIndex, designIndex) {
    const wrapper = button.closest('.design-item').querySelector('.design-input-wrapper');
    const mode = wrapper.dataset.mode;
    
    if (mode === 'url') {
        // Switch to file upload
        const urlInput = wrapper.querySelector('.design-url-input');
        const urlValue = urlInput.value;
        wrapper.dataset.mode = 'file';
        wrapper.innerHTML = `
            <input 
                type="file" 
                name="items[${productIndex}][designs][${designIndex}][file]" 
                accept=".png,image/png"
                required
                class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D] design-file-input"
                onchange="validateDesignFile(this)"
            >
            <input type="hidden" name="items[${productIndex}][designs][${designIndex}][url]" value="${urlValue || ''}">
        `;
        button.querySelector('.input-mode-text').textContent = 'Switch to URL';
    } else {
        // Switch to URL
        const fileInput = wrapper.querySelector('.design-file-input');
        wrapper.dataset.mode = 'url';
        wrapper.innerHTML = `
            <input 
                type="text" 
                name="items[${productIndex}][designs][${designIndex}][url]" 
                placeholder="Design PNG URL (Google Drive supported)" 
                required
                class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D] design-url-input"
                data-design-index="${designIndex}"
                onchange="validateDesignUrl(this)"
            >
        `;
        button.querySelector('.input-mode-text').textContent = 'Switch to File Upload';
    }
}

// Toggle between file upload and URL input for Mockup
function toggleMockupInput(button, productIndex, mockupIndex) {
    const wrapper = button.closest('.mockup-item').querySelector('.mockup-input-wrapper');
    const mode = wrapper.dataset.mode;
    
    if (mode === 'url') {
        // Switch to file upload
        const urlInput = wrapper.querySelector('.mockup-url-input');
        const urlValue = urlInput.value;
        wrapper.dataset.mode = 'file';
        wrapper.innerHTML = `
            <input 
                type="file" 
                name="items[${productIndex}][mockups][${mockupIndex}][file]" 
                accept="image/*,.pdf"
                required
                class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D] mockup-file-input"
                onchange="validateMockupFile(this)"
            >
            <input type="hidden" name="items[${productIndex}][mockups][${mockupIndex}][url]" value="${urlValue || ''}">
        `;
        button.querySelector('.input-mode-text').textContent = 'Switch to URL';
    } else {
        // Switch to URL
        const fileInput = wrapper.querySelector('.mockup-file-input');
        wrapper.dataset.mode = 'url';
        wrapper.innerHTML = `
            <input 
                type="text" 
                name="items[${productIndex}][mockups][${mockupIndex}][url]" 
                placeholder="Mockup URL or Google Drive link (All image formats)" 
                required
                class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D] mockup-url-input"
                data-mockup-index="${mockupIndex}"
                onchange="validateMockupUrl(this)"
            >
        `;
        button.querySelector('.input-mode-text').textContent = 'Switch to File Upload';
    }
}

// Convert Google Drive share link to direct download link
function convertGoogleDriveLink(url) {
    if (!url || !url.includes('drive.google.com')) return url;
    
    // Pattern: https://drive.google.com/file/d/FILE_ID/view?usp=sharing
    const fileIdMatch = url.match(/\/d\/([a-zA-Z0-9_-]+)/);
    if (fileIdMatch && fileIdMatch[1]) {
        return `https://drive.google.com/uc?export=download&id=${fileIdMatch[1]}`;
    }
    
    // Pattern: https://drive.google.com/open?id=FILE_ID
    const openIdMatch = url.match(/[?&]id=([a-zA-Z0-9_-]+)/);
    if (openIdMatch && openIdMatch[1]) {
        return `https://drive.google.com/uc?export=download&id=${openIdMatch[1]}`;
    }
    
    return url;
}

// Validate Design URL (must be PNG)
function validateDesignUrl(input) {
    let url = input.value.trim();
    if (!url) {
        input.setCustomValidity('');
        return;
    }
    
    // Validate URL format first
    if (!url.match(/^https?:\/\/.+/)) {
        input.setCustomValidity('Design URL must be a valid URL.');
        input.reportValidity();
        return;
    }
    
    // Convert Google Drive link if needed
    if (url.includes('drive.google.com')) {
        url = convertGoogleDriveLink(url);
        input.value = url; // Update the input value
        input.setCustomValidity('');
        return;
    }
    
    // Check if URL ends with .png or contains image/png
    const isPng = url.toLowerCase().endsWith('.png') || 
                  url.toLowerCase().includes('image/png') ||
                  url.toLowerCase().includes('.png?');
    
    if (!isPng) {
        input.setCustomValidity('Design URL must be a PNG file or Google Drive link.');
        input.reportValidity();
    } else {
        input.setCustomValidity('');
    }
}

// Validate Design File (must be PNG)
function validateDesignFile(input) {
    const file = input.files[0];
    if (!file) {
        input.setCustomValidity('');
        return;
    }
    
    if (file.type !== 'image/png' && !file.name.toLowerCase().endsWith('.png')) {
        input.setCustomValidity('Design file must be in PNG format.');
        input.reportValidity();
    } else {
        input.setCustomValidity('');
    }
}

// Validate Mockup URL (all image formats accepted)
function validateMockupUrl(input) {
    let url = input.value.trim();
    if (!url) {
        input.setCustomValidity('');
        return;
    }
    
    // Validate URL format
    if (!url.match(/^https?:\/\/.+/)) {
        input.setCustomValidity('Mockup URL must be a valid URL.');
        input.reportValidity();
        return;
    }
    
    // Convert Google Drive link if needed
    if (url.includes('drive.google.com')) {
        url = convertGoogleDriveLink(url);
        input.value = url; // Update the input value
    }
    
    // Accept any valid URL (validation will be done on backend)
    input.setCustomValidity('');
}

// Validate Mockup File (all image formats and PDF)
function validateMockupFile(input) {
    const file = input.files[0];
    if (!file) {
        input.setCustomValidity('');
        return;
    }
    
    const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'application/pdf'];
    const validExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.bmp', '.pdf'];
    const fileName = file.name.toLowerCase();
    
    const isValidType = validTypes.includes(file.type) || validExtensions.some(ext => fileName.endsWith(ext));
    
    if (!isValidType) {
        input.setCustomValidity('Mockup file must be an image (JPG, PNG, GIF, WEBP, BMP) or PDF.');
        input.reportValidity();
    } else {
        input.setCustomValidity('');
    }
}

// Handle ESC key to close modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const variantModal = document.getElementById('variantModal');
        if (variantModal && variantModal.style.display === 'flex') {
            backToProductModal();
        } else {
            const productModal = document.getElementById('productModal');
            if (productModal) {
                closeProductModal();
            }
        }
    }
});

// Form validation before submit (for staff/admin - ensure either product_id or SKU is provided)
document.getElementById('orderForm')?.addEventListener('submit', function(e) {
    if (!isCustomer) {
        const productItems = document.querySelectorAll('.product-item');
        let isValid = true;
        
        productItems.forEach((item, index) => {
            const productIdInput = item.querySelector('.product-id-input');
            const skuInput = item.querySelector('.product-sku-input');
            const skuWrapper = item.querySelector('.product-sku-wrapper');
            
            // Check if SKU mode is active
            const isSkuMode = skuWrapper && !skuWrapper.classList.contains('hidden');
            
            if (isSkuMode) {
                // SKU mode: SKU is required, product_id is not
                if (skuInput && !skuInput.value.trim()) {
                    skuInput.setCustomValidity('Please enter SKU or select a product');
                    skuInput.reportValidity();
                    isValid = false;
                } else if (skuInput) {
                    skuInput.setCustomValidity('');
                }
                if (productIdInput) {
                    productIdInput.removeAttribute('required');
                }
            } else {
                // Select Product mode: product_id is required, SKU is not
                if (productIdInput && !productIdInput.value) {
                    productIdInput.setCustomValidity('Please select a product or enter SKU');
                    productIdInput.reportValidity();
                    isValid = false;
                } else if (productIdInput) {
                    productIdInput.setCustomValidity('');
                }
                if (skuInput) {
                    skuInput.removeAttribute('required');
                }
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            return false;
        }
    }
});

</script>
@endpush
@endsection

@php
    $activeMenu = 'orders';
@endphp

