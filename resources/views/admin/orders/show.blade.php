@extends('layouts.app')

@section('title', 'Order Details - ' . config('app.name', 'Laravel'))

@section('header-title', 'Order: ' . $order->order_number)
@section('header-subtitle', 'Order details and tracking')

@section('header-actions')
<a href="{{ route('admin.orders.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
    ← Back to Orders
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
    </div>
    @endif

    <!-- Order Info -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Order Information</h3>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Order Number</dt>
                <dd class="text-base font-semibold text-gray-900">{{ $order->order_number }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Status</dt>
                <dd>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full
                        @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                        @elseif($order->status === 'shipped') bg-purple-100 text-purple-800
                        @elseif($order->status === 'delivered') bg-green-100 text-green-800
                        @elseif($order->status === 'cancelled') bg-gray-100 text-gray-800
                        @else bg-red-100 text-red-800
                        @endif">
                        {{ strtoupper($order->status) }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">User</dt>
                <dd class="text-base text-gray-900">{{ $order->user->name }} ({{ $order->user->email }})</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Workshop</dt>
                <dd class="text-base text-gray-900">{{ $order->workshop->name }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Total Amount</dt>
                <dd class="text-base font-semibold text-gray-900">{{ number_format($order->total_amount, 2) }} {{ $order->currency }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Payment Status</dt>
                <dd>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full
                        @if($order->payment_status === 'paid') bg-green-100 text-green-800
                        @elseif($order->payment_status === 'refunded') bg-gray-100 text-gray-800
                        @else bg-yellow-100 text-yellow-800
                        @endif">
                        {{ strtoupper($order->payment_status) }}
                    </span>
                </dd>
            </div>
            @if($order->workshop_order_id)
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Workshop Order ID</dt>
                <dd class="text-base font-mono text-gray-900">{{ $order->workshop_order_id }}</dd>
            </div>
            @endif
            @if($order->tracking_number)
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Tracking Number</dt>
                <dd class="text-base font-mono text-gray-900">
                    {{ $order->tracking_number }}
                    @if($order->tracking_url)
                    <a href="{{ $order->tracking_url }}" target="_blank" class="ml-2 text-blue-600 hover:underline text-sm">View Tracking</a>
                    @endif
                </dd>
            </div>
            @endif
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Created At</dt>
                <dd class="text-base text-gray-900">{{ $order->created_at->format('d/m/Y H:i') }}</dd>
            </div>
            @if($order->submitted_at)
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Submitted At</dt>
                <dd class="text-base text-gray-900">{{ $order->submitted_at->format('d/m/Y H:i') }}</dd>
            </div>
            @endif
            @if($order->notes)
            <div class="md:col-span-2">
                <dt class="text-sm font-medium text-gray-500 mb-1">Notes</dt>
                <dd class="text-base text-gray-900">{{ $order->notes }}</dd>
            </div>
            @endif
            @if($order->error_message)
            <div class="md:col-span-2">
                <dt class="text-sm font-medium text-red-600 mb-1">Error Message</dt>
                <dd class="text-base text-red-600">{{ $order->error_message }}</dd>
            </div>
            @endif
        </dl>
    </div>

    <!-- Order Items -->
    @if($order->items && count($order->items) > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">📦 Order Items</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Variant</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($order->items as $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $item['product_name'] ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $item['variant_name'] ?? 'Default' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $item['quantity'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($item['price'], 2) }} {{ $order->currency }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                            {{ number_format($item['quantity'] * $item['price'], 2) }} {{ $order->currency }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-right text-sm font-semibold text-gray-900">Total:</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                            {{ number_format($order->total_amount, 2) }} {{ $order->currency }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

    <!-- Shipping Address -->
    @if($order->shipping_address)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">🚚 Shipping Address</h3>
        <div class="text-sm text-gray-700">
            <p class="font-semibold">{{ $order->shipping_address['name'] ?? '' }}</p>
            <p>{{ $order->shipping_address['address'] ?? '' }}</p>
            <p>{{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['state'] ?? '' }} {{ $order->shipping_address['postal_code'] ?? '' }}</p>
            <p>{{ $order->shipping_address['country'] ?? '' }}</p>
            @if(isset($order->shipping_address['phone']))
            <p class="mt-2"><strong>Phone:</strong> {{ $order->shipping_address['phone'] }}</p>
            @endif
        </div>
    </div>
    @endif

    <!-- Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">⚙️ Actions</h3>
        <div class="flex items-center gap-4 flex-wrap">
            @if($order->status === 'pending' && $order->workshop->api_enabled)
            <form method="POST" action="{{ route('admin.orders.submit', $order) }}" class="inline">
                @csrf
                <button type="submit" class="px-6 py-2 rounded-lg text-sm font-semibold text-white transition-colors bg-green-500 hover:bg-green-600" onclick="return confirm('Submit this order to workshop?');">
                    Submit to Workshop
                </button>
            </form>
            @endif

            @if($order->workshop_order_id && $order->workshop->api_enabled)
            <form method="POST" action="{{ route('admin.orders.tracking', $order) }}" class="inline">
                @csrf
                <button type="submit" class="px-6 py-2 rounded-lg text-sm font-semibold text-white transition-colors bg-blue-500 hover:bg-blue-600">
                    Get Tracking Info
                </button>
            </form>
            @endif

            <!-- Update Status Form -->
            <form method="POST" action="{{ route('admin.orders.update-status', $order) }}" class="inline">
                @csrf
                <div class="flex items-center gap-2">
                    <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="failed" {{ $order->status === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                    <input type="text" name="tracking_number" value="{{ $order->tracking_number }}" placeholder="Tracking #" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <input type="url" name="tracking_url" value="{{ $order->tracking_url }}" placeholder="Tracking URL" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <button type="submit" class="px-6 py-2 rounded-lg text-sm font-semibold text-white transition-colors bg-gray-500 hover:bg-gray-600">
                        Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- API Request/Response (Debug) -->
    @if($order->api_request || $order->api_response)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">🔍 API Debug Info</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @if($order->api_request)
            <div>
                <h4 class="text-sm font-semibold text-gray-700 mb-2">API Request</h4>
                <pre class="bg-gray-50 p-4 rounded-lg text-xs overflow-auto max-h-64">{{ json_encode($order->api_request, JSON_PRETTY_PRINT) }}</pre>
            </div>
            @endif
            @if($order->api_response)
            <div>
                <h4 class="text-sm font-semibold text-gray-700 mb-2">API Response</h4>
                <pre class="bg-gray-50 p-4 rounded-lg text-xs overflow-auto max-h-64">{{ json_encode($order->api_response, JSON_PRETTY_PRINT) }}</pre>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection

@php
    $activeMenu = 'orders';
@endphp


































