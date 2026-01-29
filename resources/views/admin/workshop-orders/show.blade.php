@extends('layouts.admin-dashboard')

@section('title', 'Workshop Order Details - ' . config('app.name', 'Laravel'))

@section('header-title')
<div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
    <a href="{{ route('admin.workshops.index') }}" class="hover:text-[#F7961D] transition-colors">Workshops</a>
    <span class="material-symbols-outlined text-xs">chevron_right</span>
    <a href="{{ route('admin.workshops.show', $workshop) }}" class="hover:text-[#F7961D] transition-colors">{{ $workshop->name }}</a>
    <span class="material-symbols-outlined text-xs">chevron_right</span>
    <a href="{{ route('admin.workshops.orders.index', $workshop) }}" class="hover:text-[#F7961D] transition-colors">Orders</a>
    <span class="material-symbols-outlined text-xs">chevron_right</span>
    <span class="text-slate-900 font-medium">Order #{{ $orderId }}</span>
</div>
<h2 class="text-3xl font-extrabold text-slate-900">Order Details</h2>
@endsection

@section('header-subtitle', 'View order details from ' . $workshop->name)

@section('header-actions')
<div class="flex items-center gap-3">
    <a href="{{ route('admin.workshops.orders.index', $workshop) }}" class="px-5 py-2.5 border border-slate-200 rounded-lg text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">
        Back to Orders
    </a>
    @php
        $orderData = is_array($order) ? $order : (array)$order;
        $statusRaw = $orderData['status'] ?? 'unknown';
        $statusKey = is_numeric($statusRaw) ? (int)$statusRaw : strtolower((string)$statusRaw);
        $terminalStatuses = [3, 4, 'shipped', 'refunded', 'completed'];
        $isTerminal = in_array($statusKey, $terminalStatuses, true);
        // Twofifteen không có API update order nên luôn disable Edit
        $canEdit = false;
        $canCancel = !$isTerminal;
    @endphp
    @if($canCancel)
    <button onclick="openCancelModal()" class="px-5 py-2.5 bg-[#EF4444] rounded-lg text-sm font-bold text-white hover:bg-red-700 transition-colors shadow-sm">
        Cancel Order
    </button>
    @endif
</div>
@endsection

@section('content')
<div class="space-y-6">
    @php
        $orderData = is_array($order) ? $order : (array)$order;
        $statusRaw = $orderData['status'] ?? 'unknown';
        $statusMap = [
            0 => ['label' => 'Created', 'color' => 'bg-blue-50 text-blue-600 border-blue-100'],
            1 => ['label' => 'Processing Payment', 'color' => 'bg-yellow-50 text-yellow-600 border-yellow-100'],
            2 => ['label' => 'Paid', 'color' => 'bg-green-50 text-green-600 border-green-100'],
            3 => ['label' => 'Shipped', 'color' => 'bg-purple-50 text-purple-600 border-purple-100'],
            4 => ['label' => 'Refunded', 'color' => 'bg-red-50 text-red-600 border-red-100'],
            'created' => ['label' => 'Created', 'color' => 'bg-blue-50 text-blue-600 border-blue-100'],
            'processing' => ['label' => 'Processing', 'color' => 'bg-yellow-50 text-yellow-600 border-yellow-100'],
            'paid' => ['label' => 'Paid', 'color' => 'bg-green-50 text-green-600 border-green-100'],
            'shipped' => ['label' => 'Shipped', 'color' => 'bg-purple-50 text-purple-600 border-purple-100'],
            'refunded' => ['label' => 'Refunded', 'color' => 'bg-red-50 text-red-600 border-red-100'],
            'received' => ['label' => 'Received', 'color' => 'bg-blue-50 text-blue-600 border-blue-100'],
            'completed' => ['label' => 'Completed', 'color' => 'bg-purple-50 text-purple-600 border-purple-100'],
        ];
        $statusKey = is_numeric($statusRaw) ? (int)$statusRaw : strtolower((string)$statusRaw);
        $statusInfo = $statusMap[$statusKey] ?? ['label' => ucfirst((string)$statusRaw), 'color' => 'bg-gray-50 text-gray-600 border-gray-100'];
    @endphp

    <!-- Order Overview -->
    <section class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Order ID</p>
                <p class="text-base font-bold text-slate-900">{{ $orderData['id'] ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">External ID</p>
                <p class="text-base font-semibold text-slate-600">{{ $orderData['external_id'] ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Status</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $statusInfo['color'] }} border uppercase">
                    {{ $statusInfo['label'] }}
                </span>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Created At</p>
                <p class="text-base font-semibold text-slate-600">
                    @if(isset($orderData['created_at']))
                        {{ \Carbon\Carbon::parse($orderData['created_at'])->format('M d, Y H:i') }}
                    @else
                        N/A
                    @endif
                </p>
            </div>
        </div>
    </section>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left Column (5/12) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Buyer Information -->
            <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-400">person</span>
                    <h3 class="font-bold text-slate-900">Buyer Information</h3>
                </div>
                <div class="p-6 grid grid-cols-2 gap-y-6 gap-x-4">
                    <div class="col-span-2">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Email</p>
                        <p class="text-sm font-medium text-slate-700">{{ $orderData['buyer_email'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Channel</p>
                        <p class="text-sm font-medium text-slate-700">{{ $orderData['channel'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Type</p>
                        <p class="text-sm font-medium text-slate-700">{{ $orderData['type'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Brand</p>
                        <p class="text-sm font-medium text-slate-700">{{ $orderData['brand'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Deleted</p>
                        <p class="text-sm font-bold {{ !empty($orderData['deleted']) ? 'text-red-600' : 'text-green-600' }}">
                            {{ !empty($orderData['deleted']) ? 'Yes' : 'No' }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Billing Address -->
            @if(isset($orderData['billing_address']) && is_array($orderData['billing_address']))
            @php $billing = $orderData['billing_address']; @endphp
            <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-400">payments</span>
                    <h3 class="font-bold text-slate-900">Billing Address</h3>
                </div>
                <div class="p-6 grid grid-cols-2 gap-y-4">
                    <div class="col-span-1">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Name</p>
                        <p class="text-sm font-medium text-slate-700">{{ trim(($billing['firstName'] ?? '') . ' ' . ($billing['lastName'] ?? '')) ?: 'N/A' }}</p>
                    </div>
                    <div class="col-span-1">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Company</p>
                        <p class="text-sm font-medium text-slate-700">{{ $billing['company'] ?? 'N/A' }}</p>
                    </div>
                    <div class="col-span-1">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Phone</p>
                        <p class="text-sm font-medium text-slate-700">{{ $billing['phone1'] ?? 'N/A' }}</p>
                    </div>
                    <div class="col-span-1">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">VAT Number</p>
                        <p class="text-sm font-medium text-slate-700">{{ $billing['vatNumber'] ?? 'N/A' }}</p>
                    </div>
                </div>
            </section>
            @endif

            <!-- Shipping Address -->
            @if(isset($orderData['shipping_address']))
            @php $shipping = is_array($orderData['shipping_address']) ? $orderData['shipping_address'] : []; @endphp
            <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-400">local_shipping</span>
                    <h3 class="font-bold text-slate-900">Shipping Address</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Name</p>
                            <p class="text-sm font-medium text-slate-700">{{ trim(($shipping['firstName'] ?? '') . ' ' . ($shipping['lastName'] ?? '')) ?: 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Phone</p>
                            <p class="text-sm font-medium text-slate-700">{{ $shipping['phone1'] ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Address</p>
                        <p class="text-sm font-medium text-slate-700 leading-relaxed">
                            @if(!empty($shipping['address1']))
                                {{ $shipping['address1'] }}<br/>
                            @endif
                            @if(!empty($shipping['address2']))
                                {{ $shipping['address2'] }}<br/>
                            @endif
                            @if(!empty($shipping['city']) || !empty($shipping['county']))
                                {{ trim(($shipping['city'] ?? '') . ($shipping['county'] ? ', ' . $shipping['county'] : '')) }}<br/>
                            @endif
                            @if(!empty($shipping['postcode']) || !empty($shipping['country']))
                                {{ trim(($shipping['postcode'] ?? '') . ($shipping['country'] ? ', ' . $shipping['country'] : '')) }}
                            @endif
                            @if(empty($shipping['address1']) && empty($shipping['address2']) && empty($shipping['city']))
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
            </section>
            @endif
        </div>

        <!-- Right Column (7/12) -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Order Items -->
            @if(isset($orderData['items']) && is_array($orderData['items']))
            <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-bold text-slate-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-slate-400">check_box</span>
                        Order Items ({{ count($orderData['items']) }})
                    </h3>
                </div>
                <div class="p-6">
                    @foreach($orderData['items'] as $index => $item)
                    @php
                        $itemData = is_array($item) ? $item : (array)$item;
                        $currency = $itemData['retailCurrency'] ?? 'GBP';
                    @endphp
                    <div class="flex items-start justify-between mb-6 {{ $index > 0 ? 'pt-6 border-t border-slate-100' : '' }}">
                        <div class="flex items-center gap-4 flex-1">
                            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-slate-500 font-bold text-sm">#{{ $index + 1 }}</span>
                            <div class="flex-1">
                                <h4 class="text-lg font-bold text-slate-900">{{ $itemData['title'] ?? 'N/A' }}</h4>
                                <div class="flex items-center gap-3 mt-1">
                                    @if(isset($itemData['pn']))
                                    <span class="text-xs font-semibold text-slate-500 bg-slate-50 px-2 py-0.5 rounded border border-slate-100">SKU: {{ $itemData['pn'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Quantity</p>
                            <p class="text-lg font-extrabold text-slate-900">{{ $itemData['quantity'] ?? 0 }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        <div>
                            @if(isset($itemData['options']) && is_array($itemData['options']) && count($itemData['options']) > 0)
                            <p class="text-[10px] font-bold text-slate-400 uppercase mb-2">Options</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($itemData['options'] as $opt)
                                @php $optData = is_array($opt) ? $opt : (array)$opt; @endphp
                                <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded text-xs font-bold border border-slate-200">
                                    {{ $optData['type'] ?? 'Option' }}: {{ $optData['value'] ?? '' }}
                                </span>
                                @endforeach
                            </div>
                            @endif
                            <div class="mt-6">
                                <p class="text-[10px] font-bold text-slate-400 uppercase mb-2">Details</p>
                                <ul class="space-y-1.5">
                                    @if(isset($itemData['price']))
                                    <li class="flex items-center gap-2 text-xs text-slate-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                        <span class="font-bold">Unit Cost:</span> {{ $currency === 'GBP' ? '£' : $currency . ' ' }}{{ number_format((float)$itemData['price'], 2) }}
                                    </li>
                                    @endif
                                    @if(isset($itemData['retailPrice']))
                                    <li class="flex items-center gap-2 text-xs text-slate-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                        <span class="font-bold">Retail Price:</span> {{ $currency === 'GBP' ? '£' : $currency . ' ' }}{{ number_format((float)$itemData['retailPrice'], 2) }}
                                    </li>
                                    @endif
                                    @if(isset($orderData['fulfillments']) && is_array($orderData['fulfillments']) && count($orderData['fulfillments']) > 0)
                                    <li class="flex items-center gap-2 text-xs text-slate-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                        <span class="font-bold">Fulfillment Status:</span> {{ $orderData['fulfillments'][0]['status'] ?? 'Pending' }}
                                    </li>
                                    @else
                                    <li class="flex items-center gap-2 text-xs text-slate-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                        <span class="font-bold">Fulfillment Status:</span> Pending
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                        <div class="space-y-6">
                            @if(isset($itemData['mockups']) && is_array($itemData['mockups']) && count($itemData['mockups']) > 0)
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase mb-2">Mockups</p>
                                <div class="grid grid-cols-2 gap-3">
                                    @foreach($itemData['mockups'] as $mockup)
                                    @php
                                        $mockupData = is_array($mockup) ? $mockup : (array)$mockup;
                                        $mockupUrl = $mockupData['src'] ?? $mockupData['url'] ?? '';
                                        $mockupTitle = $mockupData['title'] ?? 'Mockup';
                                    @endphp
                                    @if($mockupUrl)
                                    <a href="{{ $mockupUrl }}" target="_blank" class="group relative aspect-square bg-slate-50 border border-slate-100 rounded-lg overflow-hidden hover:border-[#F7961D] transition-colors cursor-pointer">
                                        <img alt="{{ $mockupTitle }}" class="w-full h-full object-contain p-2" src="{{ $mockupUrl }}"/>
                                        <div class="absolute inset-x-0 bottom-0 bg-black/60 py-1 text-center">
                                            <p class="text-[9px] font-bold text-white uppercase">{{ str_replace(['Printing ', ' Side'], '', $mockupTitle) }}</p>
                                        </div>
                                    </a>
                                    @endif
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            @if(isset($itemData['designs']) && is_array($itemData['designs']) && count($itemData['designs']) > 0)
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase mb-2">Designs</p>
                                <div class="grid grid-cols-2 gap-3">
                                    @foreach($itemData['designs'] as $design)
                                    @php
                                        $designData = is_array($design) ? $design : (array)$design;
                                        $designUrl = $designData['src'] ?? $designData['url'] ?? '';
                                        $designTitle = $designData['title'] ?? 'Design';
                                    @endphp
                                    @if($designUrl)
                                    <a href="{{ $designUrl }}" target="_blank" class="group relative aspect-square bg-slate-50 border border-slate-100 rounded-lg overflow-hidden hover:border-[#F7961D] transition-colors cursor-pointer">
                                        <img alt="{{ $designTitle }}" class="w-full h-full object-contain p-2" src="{{ $designUrl }}"/>
                                        <div class="absolute inset-x-0 bottom-0 bg-black/60 py-1 text-center">
                                            <p class="text-[9px] font-bold text-white uppercase">{{ str_replace(['Printing ', ' Side', 'DTG '], ['', '', ''], $designTitle) }}</p>
                                        </div>
                                    </a>
                                    @endif
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Financial Summary -->
            @if(isset($orderData['summary']) && is_array($orderData['summary']))
            @php $summary = $orderData['summary']; $currency = $summary['currency'] ?? 'GBP'; @endphp
            <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden ml-auto max-w-sm">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900">Financial Summary</h3>
                </div>
                <div class="p-6 space-y-3">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-500 font-medium">Currency</span>
                        <span class="font-bold text-slate-900">{{ $currency }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-500 font-medium">Subtotal</span>
                        <span class="font-bold text-slate-900">{{ $currency === 'GBP' ? '£' : $currency . ' ' }}{{ number_format((float)($summary['subtotalPrice'] ?? 0), 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-500 font-medium">Shipping</span>
                        <span class="font-bold text-slate-900">{{ $currency === 'GBP' ? '£' : $currency . ' ' }}{{ number_format((float)($summary['shippingPrice'] ?? 0), 2) }}</span>
                    </div>
                    <div class="pt-4 mt-2 border-t border-slate-100 flex justify-between items-center">
                        <span class="text-base font-bold text-slate-900">Total</span>
                        <span class="text-2xl font-extrabold text-[#F7961D]">{{ $currency === 'GBP' ? '£' : $currency . ' ' }}{{ number_format((float)($summary['total'] ?? 0), 2) }}</span>
                    </div>
                </div>
            </section>
            @endif

            <!-- Payment -->
            @if(isset($orderData['payment']) && is_array($orderData['payment']))
            @php $pay = $orderData['payment']; @endphp
            <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Payment Method</p>
                        <p class="text-sm font-semibold text-slate-700">{{ $pay['paymentMethod'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Paid At</p>
                        <p class="text-sm font-semibold text-slate-700">
                            @if(!empty($pay['paid_at']))
                                {{ \Carbon\Carbon::parse($pay['paid_at'])->format('M d, Y H:i') }}
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
            </section>
            @endif

            <!-- Shipping Info -->
            @if(isset($orderData['shipping']) && is_array($orderData['shipping']))
            @php $ship = $orderData['shipping']; @endphp
            <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Shipping Method</p>
                        <p class="text-sm font-semibold text-slate-700">{{ $ship['shippingMethod'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Tracking Number</p>
                        <p class="text-sm font-semibold text-slate-700">{{ $ship['trackingNumber'] ?? 'N/A' }}</p>
                    </div>
                    @if(!empty($ship['shipped_at']))
                    <div class="col-span-2">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Shipped At</p>
                        <p class="text-sm font-semibold text-slate-700">{{ \Carbon\Carbon::parse($ship['shipped_at'])->format('M d, Y H:i') }}</p>
                    </div>
                    @endif
                </div>
            </section>
            @endif

            <!-- Fulfillments -->
            @if(isset($orderData['fulfillments']) && is_array($orderData['fulfillments']) && count($orderData['fulfillments']) > 0)
            <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900">Fulfillments ({{ count($orderData['fulfillments']) }})</h3>
                </div>
                <div class="p-6 space-y-4">
                    @foreach($orderData['fulfillments'] as $fi => $ful)
                    @php $fulData = is_array($ful) ? $ful : (array)$ful; @endphp
                    <div class="border border-slate-200 rounded-lg p-4">
                        <div class="grid grid-cols-2 gap-4 text-sm mb-3">
                            <div>
                                <span class="text-slate-500">Created:</span>
                                <span class="font-semibold text-slate-900 ml-1">
                                    @if(!empty($fulData['created_at']))
                                        {{ \Carbon\Carbon::parse($fulData['created_at'])->format('M d, Y H:i') }}
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                            <div>
                                <span class="text-slate-500">Status:</span>
                                <span class="font-semibold text-slate-900 ml-1">{{ $fulData['status'] ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-slate-500">Tracking:</span>
                                <span class="font-semibold text-slate-900 ml-1">{{ $fulData['trackingNo'] ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-slate-500">Company:</span>
                                <span class="font-semibold text-slate-900 ml-1">{{ $fulData['trackingCompany'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                        @if(!empty($fulData['trackingUrl']))
                        <div class="mb-2">
                            <a href="{{ $fulData['trackingUrl'] }}" target="_blank" class="text-sm text-blue-600 hover:underline">Tracking URL</a>
                        </div>
                        @endif
                        @if(!empty($fulData['notes']))
                        <div class="mt-2 pt-2 border-t border-slate-200">
                            <p class="text-xs text-slate-500 mb-1">Notes:</p>
                            <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ $fulData['notes'] }}</p>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Comments -->
            @if(isset($orderData['comments']) && !empty($orderData['comments']))
            <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900">Comments</h3>
                </div>
                <div class="p-6">
                    <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ $orderData['comments'] }}</p>
                </div>
            </section>
            @endif

            <!-- API Information -->
            <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-400">api</span>
                    <h3 class="font-bold text-slate-900">API Information</h3>
                </div>
                <div class="p-6 space-y-6">
                    <!-- API Request -->
                    @if(isset($apiRequest) && !empty($apiRequest))
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <span class="material-symbols-outlined text-slate-400 text-base">send</span>
                                API Request
                            </h4>
                            @if(isset($localOrder))
                            <span class="text-xs text-slate-500">From Local Database</span>
                            @endif
                        </div>
                        <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 overflow-x-auto">
                            <pre class="text-xs text-slate-700 whitespace-pre-wrap font-mono">{{ json_encode($apiRequest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                    @endif

                    <!-- API Response -->
                    @if(isset($apiResponse) && !empty($apiResponse))
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <span class="material-symbols-outlined text-slate-400 text-base">download</span>
                                API Response (Local Database)
                            </h4>
                            @if(isset($apiResponse['status']))
                            <span class="px-2 py-1 rounded text-xs font-bold {{ $apiResponse['status'] >= 200 && $apiResponse['status'] < 300 ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }}">
                                HTTP {{ $apiResponse['status'] }}
                            </span>
                            @endif
                        </div>
                        <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 overflow-x-auto">
                            <pre class="text-xs text-slate-700 whitespace-pre-wrap font-mono">{{ json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                    @endif

                    <!-- Raw API Response (from Workshop API) -->
                    @if(isset($result) && isset($result['data']))
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <span class="material-symbols-outlined text-slate-400 text-base">cloud_download</span>
                                Raw API Response (from Workshop)
                            </h4>
                            <span class="px-2 py-1 rounded text-xs font-bold bg-blue-50 text-blue-600">
                                Live Data
                            </span>
                        </div>
                        <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 overflow-x-auto">
                            <pre class="text-xs text-slate-700 whitespace-pre-wrap font-mono">{{ json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                    @endif

                    <!-- Local Order Info -->
                    @if(isset($localOrder))
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <span class="material-symbols-outlined text-slate-400 text-base">database</span>
                                Local Order Information
                            </h4>
                            <a href="{{ route('admin.orders.show', $localOrder->id) }}" class="text-xs text-blue-600 hover:underline font-semibold">
                                View Local Order →
                            </a>
                        </div>
                        <div class="bg-slate-50 border border-slate-200 rounded-lg p-4">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Order Number</p>
                                    <p class="font-semibold text-slate-900">{{ $localOrder->order_number }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Status</p>
                                    <p class="font-semibold text-slate-900">{{ $localOrder->status }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Tracking Number</p>
                                    <p class="font-semibold text-slate-900">{{ $localOrder->tracking_number ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Submitted At</p>
                                    <p class="font-semibold text-slate-900">
                                        {{ $localOrder->submitted_at ? $localOrder->submitted_at->format('M d, Y H:i') : 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <p class="text-sm text-yellow-800 flex items-center gap-2">
                            <span class="material-symbols-outlined text-base">info</span>
                            <span>This order is not found in local database. It may have been created directly in the workshop.</span>
                        </p>
                    </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</div>

<!-- Cancel Order Modal -->
@if($canCancel)
<div id="cancelModal" class="fixed inset-0 z-1101 p-4 bg-black/60 backdrop-blur-sm hidden pl-64" onclick="closeCancelModal()">
    <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full mx-auto" onclick="event.stopPropagation()">
        <div class="p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-4">Cancel Order</h3>
            <form method="POST" action="{{ route('admin.workshops.orders.cancel', [$workshop, $orderId]) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-600 mb-2">Reason (Optional)</label>
                    <textarea name="reason" rows="3" 
                              class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-primary focus:border-primary"
                              placeholder="Enter cancellation reason..."></textarea>
                </div>
                <div class="flex items-center justify-end gap-3">
                    <button type="button" onclick="closeCancelModal()" 
                            class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm bg-red-600 hover:bg-red-700">
                        Confirm Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
function openCancelModal() {
    document.getElementById('cancelModal').classList.remove('hidden');
}

function closeCancelModal() {
    document.getElementById('cancelModal').classList.add('hidden');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCancelModal();
    }
});
</script>
@endpush

@php
    $activeMenu = 'workshop-orders';
@endphp
@endsection
