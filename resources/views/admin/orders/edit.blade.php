@extends('layouts.admin-dashboard')

@section('title', 'Edit Order #' . $order->order_number . ' - ' . config('app.name', 'Laravel'))

@section('header-title', 'Edit Order #' . $order->order_number)
@section('header-subtitle', 'Update order details')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-32">
    @if ($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
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

    <form method="POST" action="{{ route($routePrefix . '.orders.update', $order) }}" id="orderForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @php
            $items = is_array($order->items) ? $order->items : json_decode($order->items, true) ?? [];
            $apiRequest = is_array($order->api_request) ? $order->api_request : json_decode($order->api_request, true) ?? [];
            $shippingAddress = is_array($order->shipping_address) ? $order->shipping_address : json_decode($order->shipping_address, true) ?? [];
            $itemCount = count($items);
            
            // Calculate subtotal
            $subtotal = 0;
            foreach ($items as $item) {
                if (isset($item['unit_prices']) && is_array($item['unit_prices'])) {
                    $subtotal += array_sum($item['unit_prices']);
                } else {
                    $subtotal += ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
                }
            }
            
            // Convert Google Drive link to direct download URL
            $toDirectUrl = function (?string $url): ?string {
                if (!$url) return null;
                $lower = strtolower($url);
                if (!str_contains($lower, 'drive.google.com')) return $url;
                if (preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $url, $m)) {
                    return 'https://drive.google.com/uc?export=download&id=' . $m[1];
                }
                if (preg_match('/id=([a-zA-Z0-9_-]+)/', $url, $m)) {
                    return 'https://drive.google.com/uc?export=download&id=' . $m[1];
                }
                return $url;
            };
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Order Items & Shipping -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Order Items -->
                <section class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/50">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">shopping_bag</span>
                            <h2 class="font-semibold text-lg">Order Items</h2>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="bg-slate-200 text-slate-700 px-2 py-0.5 rounded text-xs font-medium" id="item-count-badge">{{ $itemCount }} {{ $itemCount == 1 ? 'Item' : 'Items' }}</span>
                            <button 
                                type="button"
                                onclick="addNewItem()"
                                class="px-4 py-2 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2"
                            >
                                <span class="material-symbols-outlined text-base">add</span>
                                Add Item
                            </button>
                        </div>
                    </div>
                    <div class="p-6 space-y-8" id="order-items-container">
                        <!-- Order items will be loaded here by JavaScript -->
                    </div>
                </section>

                <!-- Shipping Details -->
                <section class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 flex items-center gap-2 bg-slate-50/50">
                        <span class="material-symbols-outlined text-primary">local_shipping</span>
                        <h2 class="font-semibold text-lg">Shipping Details</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-400 uppercase">Recipient Name</label>
                                <input 
                                    type="text" 
                                    name="shipping_address[name]" 
                                    value="{{ old('shipping_address.name', $shippingAddress['name'] ?? '') }}"
                                    required
                                    class="w-full px-3 py-2 rounded border border-slate-300 focus:ring-primary focus:border-primary"
                                >
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-400 uppercase">Phone Number</label>
                                <input 
                                    type="tel" 
                                    name="shipping_address[phone]" 
                                    value="{{ old('shipping_address.phone', $shippingAddress['phone'] ?? '') }}"
                                    class="w-full px-3 py-2 rounded border border-slate-300 focus:ring-primary focus:border-primary"
                                >
                            </div>
                            <div class="space-y-1 md:col-span-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase">Address Line 1</label>
                                <input 
                                    type="text" 
                                    name="shipping_address[address]" 
                                    value="{{ old('shipping_address.address', $shippingAddress['address'] ?? '') }}"
                                    required
                                    class="w-full px-3 py-2 rounded border border-slate-300 focus:ring-primary focus:border-primary"
                                >
                            </div>
                            <div class="space-y-1 md:col-span-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase">Address Line 2 (Optional)</label>
                                <input 
                                    type="text" 
                                    name="shipping_address[address2]" 
                                    value="{{ old('shipping_address.address2', $shippingAddress['address2'] ?? '') }}"
                                    placeholder="Apartment, suite, etc." 
                                    class="w-full px-3 py-2 rounded border border-slate-300 focus:ring-primary focus:border-primary"
                                >
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-400 uppercase">City</label>
                                <input 
                                    type="text" 
                                    name="shipping_address[city]" 
                                    value="{{ old('shipping_address.city', $shippingAddress['city'] ?? '') }}"
                                    required
                                    class="w-full px-3 py-2 rounded border border-slate-300 focus:ring-primary focus:border-primary"
                                >
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-400 uppercase">State / Province</label>
                                <input 
                                    type="text" 
                                    name="shipping_address[state]" 
                                    value="{{ old('shipping_address.state', $shippingAddress['state'] ?? '') }}"
                                    class="w-full px-3 py-2 rounded border border-slate-300 focus:ring-primary focus:border-primary"
                                >
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-400 uppercase">ZIP / Postal Code</label>
                                <input 
                                    type="text" 
                                    name="shipping_address[postal_code]" 
                                    value="{{ old('shipping_address.postal_code', $shippingAddress['postal_code'] ?? '') }}"
                                    required
                                    class="w-full px-3 py-2 rounded border border-slate-300 focus:ring-primary focus:border-primary"
                                >
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-slate-400 uppercase">Country</label>
                                <select 
                                    name="shipping_address[country]" 
                                    required
                                    class="w-full px-3 py-2 rounded border border-slate-300 focus:ring-primary focus:border-primary"
                                >
                                    <option value="">-- Select Country --</option>
                                    <option value="US" {{ old('shipping_address.country', $shippingAddress['country'] ?? '') == 'US' ? 'selected' : '' }}>United States</option>
                                    <option value="GB" {{ old('shipping_address.country', $shippingAddress['country'] ?? 'GB') == 'GB' ? 'selected' : '' }}>United Kingdom</option>
                                    <option value="CA" {{ old('shipping_address.country', $shippingAddress['country'] ?? '') == 'CA' ? 'selected' : '' }}>Canada</option>
                                    <option value="AU" {{ old('shipping_address.country', $shippingAddress['country'] ?? '') == 'AU' ? 'selected' : '' }}>Australia</option>
                                    <option value="VN" {{ old('shipping_address.country', $shippingAddress['country'] ?? '') == 'VN' ? 'selected' : '' }}>Vietnam</option>
                                </select>
                            </div>
                        </div>
                        @if(!empty($apiRequest['shipping_method']) && $apiRequest['shipping_method'] == 'tiktok_label')
                        <div class="bg-emerald-50 p-4 rounded-lg border border-emerald-100">
                            <label class="block text-[11px] font-bold text-emerald-700 uppercase mb-2">TikTok Label URL</label>
                            <div class="flex gap-2">
                                <div class="relative flex-grow">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-emerald-500">
                                        <span class="material-symbols-outlined text-sm">link</span>
                                    </span>
                                    <input 
                                        type="url" 
                                        name="tiktok_label_url" 
                                        id="tiktok_label_url"
                                        value="{{ old('tiktok_label_url', $order->tiktok_label_url ?? '') }}"
                                        class="w-full pl-9 rounded border border-emerald-200 bg-white focus:ring-emerald-500 focus:border-emerald-500 text-sm"
                                    >
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </section>
            </div>

            <!-- Right Sidebar: Order Summary -->
            <div class="space-y-6">
                <aside class="sticky top-28 space-y-6">
                    <section class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                        <div class="px-6 py-4 border-b border-slate-200 flex items-center gap-2 bg-slate-50/50">
                            <span class="material-symbols-outlined text-primary">receipt_long</span>
                            <h2 class="font-semibold text-lg">Order Summary</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Subtotal</span>
                                <span class="font-medium">{{ number_format($subtotal, 2) }} {{ $order->currency }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Shipping</span>
                                <span class="font-medium">0.00 {{ $order->currency }}</span>
                            </div>
                            <div class="pt-4 border-t border-slate-100 flex justify-between items-baseline">
                                <span class="text-lg font-bold">Total Amount</span>
                                <span class="text-2xl font-black text-primary">{{ number_format($order->total_amount, 2) }} {{ $order->currency }}</span>
                            </div>
                        </div>
                    </section>
                    <section class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                        <div class="px-6 py-4 border-b border-slate-200 flex items-center gap-2 bg-slate-50/50">
                            <span class="material-symbols-outlined text-primary">notes</span>
                            <h2 class="font-semibold text-lg">Internal Notes</h2>
                        </div>
                        <div class="p-6">
                            <textarea 
                                name="order_note" 
                                id="order_note"
                                class="w-full rounded border border-slate-300 focus:ring-primary focus:border-primary text-sm" 
                                placeholder="Add private notes for staff only..." 
                                rows="4"
                            >{{ old('order_note', $order->notes ?? '') }}</textarea>
                        </div>
                    </section>
                </aside>
            </div>
        </div>

        <!-- Hidden fields -->
        @if(isset($isCustomer) && $isCustomer)
            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
        @else
            <input type="hidden" name="user_id" value="{{ old('user_id', $order->user_id) }}">
        @endif
        <input type="hidden" name="order_number" value="{{ old('order_number', $order->order_number) }}">
        <input type="hidden" name="store_name" value="{{ old('store_name', $apiRequest['store_name'] ?? '') }}">
        <input type="hidden" name="sales_channel" value="{{ old('sales_channel', $apiRequest['sales_channel'] ?? '') }}">
        <input type="hidden" name="shipping_method" id="shipping_method_hidden" value="{{ old('shipping_method', $apiRequest['shipping_method'] ?? '') }}">
    </form>
</div>

<!-- Footer with Action Buttons -->
<footer class="fixed bottom-0 left-0 right-0 bg-white/80 backdrop-blur-md border-t border-slate-200 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between">
            <div class="hidden md:flex items-center gap-2 text-sm text-slate-500">
                <span class="material-symbols-outlined text-amber-500">info</span>
                <span>Unsaved changes will be lost if you leave this page.</span>
            </div>
            <div class="flex items-center gap-4 w-full md:w-auto">
                <button 
                    type="button"
                    onclick="window.location.href='{{ route($routePrefix . '.orders.index') }}'"
                    class="flex-1 md:flex-none px-6 py-2.5 rounded-lg border border-slate-200 font-semibold hover:bg-slate-50 transition-colors"
                >
                    Discard Changes
                </button>
                <button 
                    type="submit"
                    form="orderForm"
                    class="flex-1 md:flex-none px-8 py-2.5 rounded-lg bg-primary text-white font-bold hover:brightness-110 shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2"
                >
                    <span class="material-symbols-outlined">save</span>
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</footer>

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

/* Input border styling - ensure borders are visible */
input[type="text"],
input[type="tel"],
input[type="url"],
input[type="number"],
select,
textarea {
    border: 1px solid #cbd5e1 !important;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

/* Only add default padding if not already specified */
input[type="text"]:not([class*="px-"]):not([class*="p-"]),
input[type="tel"]:not([class*="px-"]):not([class*="p-"]),
input[type="url"]:not([class*="px-"]):not([class*="p-"]),
input[type="number"]:not([class*="px-"]):not([class*="p-"]),
select:not([class*="px-"]):not([class*="p-"]),
textarea:not([class*="px-"]):not([class*="p-"]) {
    padding: 0.5rem 0.75rem !important;
}

input[type="text"]:focus,
input[type="tel"]:focus,
input[type="url"]:focus,
input[type="number"]:focus,
select:focus,
textarea:focus {
    outline: none;
    border-color: #F7961D !important;
    box-shadow: 0 0 0 3px rgba(247, 149, 29, 0.1);
}

/* Ensure borders are visible with proper width */
.border-slate-300 {
    border-color: #cbd5e1 !important;
    border-width: 1px !important;
    border-style: solid !important;
}

.border-emerald-200 {
    border-color: #a7f3d0 !important;
    border-width: 1px !important;
    border-style: solid !important;
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

// Store old input data for restoration
const oldInputData = @json(old('items', []));
const allProducts = @json($products ?? []);

// Function to restore product item from old input
function restoreProduct(itemIndex, itemData) {
    // Find product item by data-index attribute
    const productItem = document.querySelector(`.product-item[data-index="${itemIndex}"]`);
    
    if (!productItem || !itemData) return;
    
    // Restore product selection
    const productIdInput = productItem.querySelector(`input[name="items[${itemIndex}][product_id]"]`);
    const productSelectText = productItem.querySelector('.product-select-text');
    const variantSelect = productItem.querySelector(`select[name="items[${itemIndex}][variant_id]"]`);
    
    if (itemData.product_id && productIdInput) {
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

// Load order items from database
const orderItems = @json($items ?? []);
const allProductsData = @json($products ?? []);
const toDirectUrl = function(url) {
    if (!url || !url.includes('drive.google.com')) return url || '';
    const fileIdMatch = url.match(/\/d\/([a-zA-Z0-9_-]+)/);
    if (fileIdMatch && fileIdMatch[1]) {
        return 'https://drive.google.com/uc?export=download&id=' + fileIdMatch[1];
    }
    const openIdMatch = url.match(/[?&]id=([a-zA-Z0-9_-]+)/);
    if (openIdMatch && openIdMatch[1]) {
        return 'https://drive.google.com/uc?export=download&id=' + openIdMatch[1];
    }
    return url;
};

// Render order items in new format
function renderOrderItems() {
    const container = document.getElementById('order-items-container');
    if (!container) return;
    
    if (!orderItems || orderItems.length === 0) {
        container.innerHTML = '<div class="text-center py-8 text-slate-500">No items found</div>';
        currentItemIndex = 0;
        return;
    }
    
    container.innerHTML = '';
    
    orderItems.forEach((item, itemIndex) => {
        const designs = item.designs || [];
        const mockups = item.mockups || [];
        
        // Calculate item total
        let itemTotal = 0;
        if (item.unit_prices && Array.isArray(item.unit_prices)) {
            itemTotal = item.unit_prices.reduce((sum, price) => sum + (parseFloat(price) || 0), 0);
        } else {
            itemTotal = (parseFloat(item.price) || 0) * (parseInt(item.quantity) || 1);
        }
        
        const itemHtml = `
            <div class="space-y-6 order-item-edit" data-item-index="${itemIndex}">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-bold">${item.product_name || 'N/A'}</h3>
                        <p class="text-sm text-slate-500">${item.variant_name || 'Default'}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex flex-col">
                            <label class="text-[10px] font-bold uppercase text-slate-400 mb-1">Quantity</label>
                            <input 
                                type="number" 
                                name="items[${itemIndex}][quantity]" 
                                value="${item.quantity || 1}" 
                                min="1"
                                required
                                class="w-20 rounded border border-slate-300 focus:ring-primary focus:border-primary"
                                onchange="updateItemTotal(${itemIndex})"
                            >
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold uppercase text-slate-400 mb-1">Price</p>
                            <span class="text-lg font-bold">${itemTotal.toFixed(2)} {{ $order->currency }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Hidden fields -->
                <input type="hidden" name="items[${itemIndex}][product_id]" value="${item.product_id || ''}">
                <input type="hidden" name="items[${itemIndex}][variant_id]" value="${item.variant_id || ''}">
                <input type="hidden" name="items[${itemIndex}][product_title]" value="${(item.product_title || '').replace(/"/g, '&quot;')}">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Designs -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Designs (${designs.length})</h4>
                        <div class="grid grid-cols-2 gap-3">
                            ${designs.map((design, designIndex) => {
                                const designUrl = toDirectUrl(design.url || '');
                                return `
                                    <div class="bg-slate-50 p-2 border border-slate-200 rounded-lg">
                                        <div class="aspect-square bg-white rounded mb-2 overflow-hidden flex items-center justify-center">
                                            <img 
                                                alt="Design ${designIndex + 1} ${design.position || ''}" 
                                                class="object-contain w-full h-full opacity-80" 
                                                src="${designUrl}"
                                                onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'100\' height=\'100\'%3E%3Crect fill=\'%23f3f4f6\' width=\'100\' height=\'100\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' dominant-baseline=\'middle\' text-anchor=\'middle\' fill=\'%239ca3af\' font-size=\'12\'%3ENo Image%3C/text%3E%3C/svg%3E'"
                                            >
                                        </div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">${design.position || 'Design'} Design URL</label>
                                        <input 
                                            type="text" 
                                            name="items[${itemIndex}][designs][${designIndex}][url]" 
                                            value="${(design.url || '').replace(/"/g, '&quot;')}"
                                            required
                                            class="w-full text-[10px] p-1.5 rounded bg-white border border-slate-200"
                                        >
                                        <input 
                                            type="hidden" 
                                            name="items[${itemIndex}][designs][${designIndex}][position]" 
                                            value="${(design.position || '').replace(/"/g, '&quot;')}"
                                        >
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                    
                    <!-- Mockups -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Mockups (${mockups.length})</h4>
                        <div class="grid grid-cols-2 gap-3">
                            ${mockups.map((mockup, mockupIndex) => {
                                const mockupUrl = toDirectUrl(mockup.url || '');
                                return `
                                    <div class="bg-slate-50 p-2 border border-slate-200 rounded-lg">
                                        <div class="aspect-square bg-white rounded mb-2 overflow-hidden flex items-center justify-center">
                                            <img 
                                                alt="Mockup ${mockupIndex + 1} ${mockup.position || ''}" 
                                                class="object-cover w-full h-full" 
                                                src="${mockupUrl}"
                                                onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'100\' height=\'100\'%3E%3Crect fill=\'%23f3f4f6\' width=\'100\' height=\'100\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' dominant-baseline=\'middle\' text-anchor=\'middle\' fill=\'%239ca3af\' font-size=\'12\'%3ENo Image%3C/text%3E%3C/svg%3E'"
                                            >
                                        </div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">${mockup.position || 'Mockup'} Mockup URL</label>
                                        <input 
                                            type="text" 
                                            name="items[${itemIndex}][mockups][${mockupIndex}][url]" 
                                            value="${(mockup.url || '').replace(/"/g, '&quot;')}"
                                            required
                                            class="w-full text-[10px] p-1.5 rounded bg-white border border-slate-200"
                                        >
                                        <input 
                                            type="hidden" 
                                            name="items[${itemIndex}][mockups][${mockupIndex}][position]" 
                                            value="${(mockup.position || '').replace(/"/g, '&quot;')}"
                                        >
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', itemHtml);
    });
    
    // Update currentItemIndex to continue from existing items
    currentItemIndex = orderItems.length;
}

function updateItemTotal(itemIndex) {
    // This will be recalculated on the server, just update the display
    const item = orderItems[itemIndex];
    if (!item) return;
    
    const quantityInput = document.querySelector(`input[name="items[${itemIndex}][quantity]"]`);
    const quantity = parseInt(quantityInput?.value || item.quantity || 1);
    
    let itemTotal = 0;
    if (item.unit_prices && Array.isArray(item.unit_prices)) {
        // Calculate based on quantity (first unit uses base, rest use additional)
        itemTotal = item.unit_prices.slice(0, quantity).reduce((sum, price) => sum + (parseFloat(price) || 0), 0);
    } else {
        itemTotal = (parseFloat(item.price) || 0) * quantity;
    }
    
    const priceElement = quantityInput?.closest('.flex').querySelector('.text-lg');
    if (priceElement) {
        priceElement.textContent = itemTotal.toFixed(2) + ' {{ $order->currency }}';
    }
}

// Track current item index (starts from existing items count)
let currentItemIndex = @json(count($items ?? []));

// Function to add new item
function addNewItem() {
    const container = document.getElementById('order-items-container');
    const itemIndex = currentItemIndex++;
    
    const newItemHtml = `
        <div class="space-y-6 order-item-edit border border-slate-200 rounded-lg p-6 bg-slate-50/50" data-item-index="${itemIndex}">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-700">New Item #${itemIndex + 1}</h3>
                <button 
                    type="button"
                    onclick="removeItem(${itemIndex})"
                    class="px-3 py-1.5 bg-red-100 text-red-600 rounded-md hover:bg-red-200 transition-colors text-sm font-semibold flex items-center gap-1"
                >
                    <span class="material-symbols-outlined text-base">delete</span>
                    Remove
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Select Product <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input 
                            type="hidden" 
                            name="items[${itemIndex}][product_id]" 
                            class="product-id-input" 
                            required
                        >
                        <button 
                            type="button"
                            onclick="openProductModal(${itemIndex})"
                            class="w-full flex items-center justify-between px-4 py-2 rounded border border-slate-300 text-left bg-white hover:border-primary transition-colors product-select-btn"
                        >
                            <span class="product-select-text text-slate-500">Search and select product...</span>
                            <span class="material-symbols-outlined text-slate-400">search</span>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Variant (Optional)</label>
                    <select 
                        name="items[${itemIndex}][variant_id]" 
                        id="variant-select-${itemIndex}"
                        class="variant-select w-full px-4 py-2 rounded border border-slate-300 bg-slate-50 text-slate-400"
                    >
                        <option value="">Select product first</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Quantity <span class="text-red-500">*</span></label>
                    <input 
                        type="number" 
                        name="items[${itemIndex}][quantity]" 
                        value="1" 
                        min="1" 
                        required
                        class="w-full px-4 py-2 rounded border border-slate-300 focus:ring-2 focus:ring-primary focus:border-primary"
                    >
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Product Title</label>
                <input 
                    type="text" 
                    name="items[${itemIndex}][product_title]" 
                    placeholder="Enter product title"
                    class="w-full px-4 py-2 rounded border border-slate-300 focus:ring-2 focus:ring-primary focus:border-primary"
                >
            </div>

            <div class="mb-6">
                <div class="flex justify-between items-center mb-3">
                    <label class="text-sm font-semibold text-slate-700">Design * (at least 1 - PNG only)</label>
                    <button 
                        type="button"
                        onclick="addDesign(${itemIndex})"
                        class="text-xs font-bold text-blue-600 hover:underline flex items-center gap-1"
                    >
                        <span class="material-symbols-outlined text-[12px]">add</span> Add Design
                    </button>
                </div>
                <div class="designs-container space-y-2" data-product="${itemIndex}">
                    <div class="design-item border border-slate-200 rounded-lg p-3 bg-white">
                        <div class="flex items-center gap-2 mb-2">
                            <button type="button" onclick="toggleDesignInput(this, ${itemIndex}, 0)" class="text-xs px-2 py-1 rounded bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                                <span class="input-mode-text">Switch to File Upload</span>
                            </button>
                        </div>
                        <div class="flex gap-3">
                            <div class="flex-1 design-input-wrapper" data-mode="url">
                                <input 
                                    type="text" 
                                    name="items[${itemIndex}][designs][0][url]" 
                                    placeholder="Design PNG URL (Google Drive supported)" 
                                    required
                                    accept=".png"
                                    class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-2 focus:ring-primary focus:border-primary design-url-input"
                                    data-design-index="0"
                                    onchange="validateDesignUrl(this)"
                                >
                            </div>
                            <input 
                                type="text" 
                                name="items[${itemIndex}][designs][0][position]" 
                                placeholder="Position (e.g. Front, Back)" 
                                required
                                class="w-1/3 px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-2 focus:ring-primary focus:border-primary"
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
                        onclick="addMockup(${itemIndex})"
                        class="text-xs font-bold text-blue-600 hover:underline flex items-center gap-1"
                    >
                        <span class="material-symbols-outlined text-[12px]">add</span> Add Mockup
                    </button>
                </div>
                <div class="mockups-container space-y-2" data-product="${itemIndex}">
                    <div class="mockup-item border border-slate-200 rounded-lg p-3 bg-white">
                        <div class="flex items-center gap-2 mb-2">
                            <button type="button" onclick="toggleMockupInput(this, ${itemIndex}, 0)" class="text-xs px-2 py-1 rounded bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                                <span class="input-mode-text">Switch to File Upload</span>
                            </button>
                        </div>
                        <div class="flex gap-3">
                            <div class="flex-1 mockup-input-wrapper" data-mode="url">
                                <input 
                                    type="text" 
                                    name="items[${itemIndex}][mockups][0][url]" 
                                    placeholder="Mockup URL or Google Drive link (All image formats)" 
                                    required
                                    class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-2 focus:ring-primary focus:border-primary mockup-url-input"
                                    data-mockup-index="0"
                                    onchange="validateMockupUrl(this)"
                                >
                            </div>
                            <input 
                                type="text" 
                                name="items[${itemIndex}][mockups][0][position]" 
                                placeholder="Position (e.g. S-Front, Tote-Back)" 
                                required
                                class="w-1/3 px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-2 focus:ring-primary focus:border-primary"
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
    
    container.insertAdjacentHTML('beforeend', newItemHtml);
    updateItemCount();
}

// Function to remove item
function removeItem(itemIndex) {
    const item = document.querySelector(`.order-item-edit[data-item-index="${itemIndex}"]`);
    if (item && confirm('Are you sure you want to remove this item?')) {
        item.remove();
        updateItemCount();
    }
}

// Function to update item count badge
function updateItemCount() {
    const container = document.getElementById('order-items-container');
    const items = container.querySelectorAll('.order-item-edit');
    const count = items.length;
    const badge = document.getElementById('item-count-badge');
    if (badge) {
        badge.textContent = count + ' ' + (count === 1 ? 'Item' : 'Items');
    }
}

// Initialize with order items
document.addEventListener('DOMContentLoaded', function() {
    renderOrderItems();
    updateItemCount();
    
    // Handle shipping method change to enable/disable TikTok Label URL
    const shippingMethodSelect = document.getElementById('shipping_method');
    const tiktokLabelUrl = document.getElementById('tiktok_label_url');
    const tiktokLabelRequired = document.getElementById('tiktok_label_required');
    
    function updateTiktokLabelField() {
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
    
    if (shippingMethodSelect) {
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
    
    // Try to find order item edit first (for new items), then fallback to product-item
    let productItem = document.querySelector(`.order-item-edit[data-item-index="${currentProductModalIndex}"]`);
    if (!productItem) {
        productItem = document.querySelector(`.product-item[data-index="${currentProductModalIndex}"]`);
    }
    
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

