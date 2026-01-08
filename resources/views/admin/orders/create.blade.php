@extends('layouts.app')

@section('title', 'Create Order - ' . config('app.name', 'Laravel'))

@section('header-title', 'Create New Order')
@section('header-subtitle', 'Create and submit order to workshop')

@section('header-actions')
<a href="{{ route('admin.orders.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
    ← Back to Orders
</a>
@endsection

@section('content')
<div class="max-w-4xl">
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

        <form method="POST" action="{{ route('admin.orders.store') }}" id="order-form">
            @csrf

            <div class="space-y-6">
                <!-- Basic Info -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="user_id" class="block text-sm font-semibold mb-2" style="color: #111827;">Customer <span class="text-red-500">*</span></label>
                            <select 
                                id="user_id" 
                                name="user_id"
                                required
                                class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                                style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
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
                            <label for="workshop_id" class="block text-sm font-semibold mb-2" style="color: #111827;">Workshop <span class="text-red-500">*</span></label>
                            <select 
                                id="workshop_id" 
                                name="workshop_id"
                                required
                                class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                                style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                            >
                                <option value="">Select Workshop</option>
                                @foreach($workshops as $workshop)
                                    <option value="{{ $workshop->id }}" {{ old('workshop_id') == $workshop->id ? 'selected' : '' }}>
                                        {{ $workshop->name }} ({{ $workshop->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Items</h3>
                    <div id="items-container" class="space-y-4">
                        <div class="item-row p-4 border rounded-lg" style="border-color: #E5E7EB;">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Product ID</label>
                                    <input type="number" name="items[0][product_id]" required class="w-full px-3 py-2 border rounded-lg" style="border-color: #D1D5DB;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Variant ID (Optional)</label>
                                    <input type="number" name="items[0][variant_id]" class="w-full px-3 py-2 border rounded-lg" style="border-color: #D1D5DB;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                                    <input type="number" name="items[0][quantity]" value="1" min="1" required class="w-full px-3 py-2 border rounded-lg" style="border-color: #D1D5DB;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Price</label>
                                    <input type="number" name="items[0][price]" step="0.01" min="0" required class="w-full px-3 py-2 border rounded-lg" style="border-color: #D1D5DB;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="addItemRow()" class="mt-4 px-4 py-2 rounded-lg text-sm font-semibold text-white transition-colors bg-blue-500 hover:bg-blue-600">
                        + Add Item
                    </button>
                </div>

                <!-- Shipping Address -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Shipping Address</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                            <input type="text" name="shipping_address[name]" required class="w-full px-3 py-2 border rounded-lg" style="border-color: #D1D5DB;" value="{{ old('shipping_address.name') }}">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address *</label>
                            <input type="text" name="shipping_address[address]" required class="w-full px-3 py-2 border rounded-lg" style="border-color: #D1D5DB;" value="{{ old('shipping_address.address') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                            <input type="text" name="shipping_address[city]" required class="w-full px-3 py-2 border rounded-lg" style="border-color: #D1D5DB;" value="{{ old('shipping_address.city') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">State/Province</label>
                            <input type="text" name="shipping_address[state]" class="w-full px-3 py-2 border rounded-lg" style="border-color: #D1D5DB;" value="{{ old('shipping_address.state') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code *</label>
                            <input type="text" name="shipping_address[postal_code]" required class="w-full px-3 py-2 border rounded-lg" style="border-color: #D1D5DB;" value="{{ old('shipping_address.postal_code') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Country *</label>
                            <input type="text" name="shipping_address[country]" required class="w-full px-3 py-2 border rounded-lg" style="border-color: #D1D5DB;" value="{{ old('shipping_address.country') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                            <input type="text" name="shipping_address[phone]" class="w-full px-3 py-2 border rounded-lg" style="border-color: #D1D5DB;" value="{{ old('shipping_address.phone') }}">
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Total Amount *</label>
                            <input type="number" name="total_amount" step="0.01" min="0" required class="w-full px-3 py-2 border rounded-lg" style="border-color: #D1D5DB;" value="{{ old('total_amount') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Currency *</label>
                            <select name="currency" required class="w-full px-3 py-2 border rounded-lg" style="border-color: #D1D5DB;">
                                <option value="USD" {{ old('currency', 'USD') === 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR</option>
                                <option value="GBP" {{ old('currency') === 'GBP' ? 'selected' : '' }}>GBP</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea name="notes" rows="2" class="w-full px-3 py-2 border rounded-lg" style="border-color: #D1D5DB;">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Options -->
                <div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="auto_submit" name="auto_submit" value="1" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="auto_submit" class="text-sm font-medium text-gray-700">Auto submit to workshop after creation</label>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-4">
                    <button 
                        type="submit"
                        class="px-6 py-3 rounded-lg font-semibold text-white transition-all"
                        style="background-color: #2563EB;"
                        onmouseover="this.style.backgroundColor='#1D4ED8';"
                        onmouseout="this.style.backgroundColor='#2563EB';"
                    >
                        Create Order
                    </button>
                    <a href="{{ route('admin.orders.index') }}" class="px-6 py-3 rounded-lg font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let itemIndex = 1;

function addItemRow() {
    const container = document.getElementById('items-container');
    const newRow = document.createElement('div');
    newRow.className = 'item-row p-4 border rounded-lg';
    newRow.style.borderColor = '#E5E7EB';
    newRow.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Product ID</label>
                <input type="number" name="items[${itemIndex}][product_id]" required class="w-full px-3 py-2 border rounded-lg" style="border-color: #D1D5DB;">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Variant ID</label>
                <input type="number" name="items[${itemIndex}][variant_id]" class="w-full px-3 py-2 border rounded-lg" style="border-color: #D1D5DB;">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                <input type="number" name="items[${itemIndex}][quantity]" value="1" min="1" required class="w-full px-3 py-2 border rounded-lg" style="border-color: #D1D5DB;">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Price</label>
                <input type="number" name="items[${itemIndex}][price]" step="0.01" min="0" required class="w-full px-3 py-2 border rounded-lg" style="border-color: #D1D5DB;">
            </div>
            <div class="flex items-end">
                <button type="button" onclick="this.closest('.item-row').remove()" class="w-full px-4 py-2 rounded-lg text-sm font-semibold text-white transition-colors bg-red-500 hover:bg-red-600">
                    Remove
                </button>
            </div>
        </div>
    `;
    container.appendChild(newRow);
    itemIndex++;
}
</script>
@endpush
@endsection

@php
    $activeMenu = 'orders';
@endphp


































