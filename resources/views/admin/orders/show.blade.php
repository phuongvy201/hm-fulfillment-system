@extends('layouts.admin-dashboard')

@section('title', 'Order Details - ' . config('app.name', 'Laravel'))

@section('header-title')
<div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
    <a href="{{ route($routePrefix . '.orders.index') }}" class="hover:text-[#F7961D] transition-colors">Orders</a>
    <span class="material-symbols-outlined text-xs">chevron_right</span>
    <span class="text-slate-900 font-medium">Order #{{ $order->order_number }}</span>
</div>
<div class="flex items-center gap-3">
    <h2 class="text-2xl font-bold text-slate-900">Order #{{ $order->order_number }}</h2>
    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full uppercase tracking-wide
        @if($order->status === 'pending') bg-yellow-100 text-yellow-700
        @elseif($order->status === 'on_hold') bg-orange-100 text-orange-700
        @elseif($order->status === 'processing') bg-blue-100 text-blue-700
        @elseif($order->status === 'shipped') bg-purple-100 text-purple-700
        @elseif($order->status === 'delivered') bg-green-100 text-green-700
        @elseif($order->status === 'cancelled') bg-gray-100 text-gray-700
        @else bg-red-100 text-red-700
        @endif">
        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
    </span>
</div>
@endsection

@section('header-subtitle', '')

@section('header-actions')
<div class="flex items-center gap-3">
    @if(!$order->workshop_order_id)
    <a href="{{ route($routePrefix . '.orders.edit', $order) }}" class="flex items-center gap-2 px-4 py-2 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
        <span class="material-symbols-outlined text-[20px]">edit</span> Edit
    </a>
    @endif
    @if(!isset($isCustomer) || !$isCustomer)
    <a href="{{ route($routePrefix . '.orders.print-label', $order) }}" target="_blank" class="flex items-center gap-2 px-4 py-2 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
        <span class="material-symbols-outlined text-[20px]">print</span> Print Label
    </a>
    @if($order->status !== 'cancelled')
    <form method="POST" action="{{ route('admin.orders.update-status', $order) }}" class="inline" onsubmit="return confirm('Are you sure you want to cancel this order?');">
        @csrf
        <input type="hidden" name="status" value="cancelled">
        <button type="submit" class="flex items-center gap-2 px-4 py-2 border border-red-200 rounded-lg text-sm font-semibold text-red-600 hover:bg-red-50 transition-colors">
            <span class="material-symbols-outlined text-[20px]">cancel</span> Cancel Order
        </button>
    </form>
    @endif
    @endif
</div>
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

    @php
        $items = is_array($order->items) ? $order->items : json_decode($order->items, true) ?? [];
        $itemCount = count($items);
        // Calculate subtotal from unit_prices if available, otherwise fallback to price * quantity
        $subtotal = 0;
        foreach ($items as $item) {
            if (isset($item['unit_prices']) && is_array($item['unit_prices'])) {
                // Use unit_prices: first unit = base_price, remaining units = additional_item_price
                $subtotal += array_sum($item['unit_prices']);
            } else {
                // Fallback: use price * quantity (for old orders without unit_prices)
                $subtotal += ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
            }
        }
        $total = $subtotal; // Total equals subtotal (shipping and tax are included in product prices)

        // Get order metadata from api_request
        $apiRequest = is_array($order->api_request) ? $order->api_request : json_decode($order->api_request, true) ?? [];
        $shippingMethod = $apiRequest['shipping_method'] ?? null;
        $brand = $apiRequest['brand'] ?? null;
        $channel = $apiRequest['channel'] ?? null;
        $comment = $apiRequest['comment'] ?? null;
        $labelName = $apiRequest['label_name'] ?? null;
        $labelType = $apiRequest['label_type'] ?? null;
        $storeName = $apiRequest['store_name'] ?? null;
        $salesChannel = $apiRequest['sales_channel'] ?? null;

        // Convert Google Drive share/view link to direct download link so <img> can render it (if public).
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Order Items -->
        <div class="lg:col-span-1 space-y-6">
            <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-bold text-slate-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#F7961D]">shopping_bag</span>
                        Order Items
                    </h3>
                    <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2 py-1 rounded">{{ $itemCount }} {{ $itemCount == 1 ? 'Item' : 'Items' }}</span>
                </div>
                <div class="p-0">
                    @forelse($items as $item)
                    @php
                        $designs = $item['designs'] ?? [];
                        $mockups = $item['mockups'] ?? [];
                    @endphp
                    <div class="p-6 border-b border-slate-50 hover:bg-slate-50/50 transition-colors {{ !$loop->last ? 'border-b' : '' }}">
                        <div class="mb-4">
                            <p class="text-sm font-bold text-slate-900 truncate">{{ $item['product_name'] ?? 'N/A' }}</p>
                            <p class="text-xs text-slate-500 mb-2">{{ $item['variant_name'] ?? 'Default' }}</p>
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-slate-700">QTY: {{ $item['quantity'] ?? 1 }}</p>
                                @php
                                    // Calculate item total from unit_prices if available
                                    $itemTotal = 0;
                                    if (isset($item['unit_prices']) && is_array($item['unit_prices'])) {
                                        $itemTotal = array_sum($item['unit_prices']);
                                    } else {
                                        // Fallback: use price * quantity
                                        $itemTotal = ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
                                    }
                                @endphp
                                <p class="text-sm font-bold text-slate-900">{{ number_format($itemTotal, 2) }} {{ $order->currency }}</p>
                            </div>
                        </div>

                        <!-- Designs Section -->
                        @if(count($designs) > 0)
                        <div class="mb-4">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Designs ({{ count($designs) }})</p>
                            <div class="grid grid-cols-2 gap-3">
                                @foreach($designs as $index => $design)
                                @if(isset($design['url']))
                                @php
                                    $designUrl = $toDirectUrl($design['url']);
                                @endphp
                                <a href="{{ $design['url'] }}" target="_blank" class="block rounded-lg border border-blue-100 bg-blue-50 overflow-hidden hover:bg-blue-100/40 transition-colors">
                                    <div class="relative w-full aspect-square bg-white">
                                        <img
                                            src="{{ $designUrl }}"
                                            alt="Design {{ $index + 1 }}"
                                            class="w-full h-full object-cover"
                                            onerror="this.style.display='none'; this.parentElement.querySelector('.preview-fallback').style.display='flex';"
                                        >
                                        <div class="preview-fallback hidden absolute inset-0 items-center justify-center text-xs text-slate-400">
                                            No preview
                                        </div>
                                        @if(str_contains(strtolower($design['url']), 'drive.google.com'))
                                        <div class="absolute top-2 right-2 bg-white/90 rounded-full p-1.5 shadow-sm">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 2L2 7l10 5 10-5-10-5z" fill="#EA4335"/>
                                                <path d="M2 17l10 5 10-5M2 12l10 5 10-5" fill="#34A853"/>
                                                <path d="M2 7l10 5 10-5" fill="#FBBC04"/>
                                                <path d="M12 2L2 7l10 5 10-5-10-5z" fill="#4285F4" opacity="0.1"/>
                                            </svg>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="p-2">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="text-xs font-bold text-slate-900 truncate">
                                                Design {{ $index + 1 }}
                                                @if(isset($design['position']))
                                                <span class="text-slate-500 font-semibold">({{ $design['position'] }})</span>
                                                @endif
                                            </p>
                                            <div class="flex items-center gap-1">
                                                @if(str_contains(strtolower($design['url']), 'drive.google.com'))
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M12 2L2 7l10 5 10-5-10-5z" fill="#EA4335"/>
                                                    <path d="M2 17l10 5 10-5M2 12l10 5 10-5" fill="#34A853"/>
                                                    <path d="M2 7l10 5 10-5" fill="#FBBC04"/>
                                                </svg>
                                                @endif
                                                <span class="material-symbols-outlined text-[16px] text-blue-600">open_in_new</span>
                                            </div>
                                        </div>
                                        <p class="text-[10px] text-blue-700 truncate" title="{{ $design['url'] }}">{{ Str::limit($design['url'], 40) }}</p>
                                    </div>
                                </a>
                                @endif
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Mockups Section -->
                        @if(count($mockups) > 0)    
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Mockups ({{ count($mockups) }})</p>
                            <div class="grid grid-cols-2 gap-3">
                                @foreach($mockups as $index => $mockup)
                                @if(isset($mockup['url']))
                                @php
                                    $mockupUrl = $toDirectUrl($mockup['url']);
                                @endphp
                                <a href="{{ $mockup['url'] }}" target="_blank" class="block rounded-lg border border-orange-100 bg-orange-50 overflow-hidden hover:bg-orange-100/40 transition-colors">
                                    <div class="relative w-full aspect-square bg-white">
                                        <img
                                            src="{{ $mockupUrl }}"
                                            alt="Mockup {{ $index + 1 }}"
                                            class="w-full h-full object-cover"
                                            onerror="this.style.display='none'; this.parentElement.querySelector('.preview-fallback').style.display='flex';"
                                        >
                                        <div class="preview-fallback hidden absolute inset-0 items-center justify-center text-xs text-slate-400">
                                            No preview
                                        </div>
                                        @if(str_contains(strtolower($mockup['url']), 'drive.google.com'))
                                        <div class="absolute top-2 right-2 bg-white/90 rounded-full p-1.5 shadow-sm">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 2L2 7l10 5 10-5-10-5z" fill="#EA4335"/>
                                                <path d="M2 17l10 5 10-5M2 12l10 5 10-5" fill="#34A853"/>
                                                <path d="M2 7l10 5 10-5" fill="#FBBC04"/>
                                            </svg>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="p-2">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="text-xs font-bold text-slate-900 truncate">
                                                Mockup {{ $index + 1 }}
                                                @if(isset($mockup['position']))
                                                <span class="text-slate-500 font-semibold">({{ $mockup['position'] }})</span>
                                                @endif
                                            </p>
                                            <div class="flex items-center gap-1">
                                                @if(str_contains(strtolower($mockup['url']), 'drive.google.com'))
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M12 2L2 7l10 5 10-5-10-5z" fill="#EA4335"/>
                                                    <path d="M2 17l10 5 10-5M2 12l10 5 10-5" fill="#34A853"/>
                                                    <path d="M2 7l10 5 10-5" fill="#FBBC04"/>
                                                </svg>
                                                @endif
                                                <span class="material-symbols-outlined text-[16px] text-orange-600">open_in_new</span>
                                            </div>
                                        </div>
                                        <p class="text-[10px] text-orange-700 truncate" title="{{ $mockup['url'] }}">{{ Str::limit($mockup['url'], 40) }}</p>
                                    </div>
                                </a>
                                @endif
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if(count($designs) === 0 && count($mockups) === 0)
                        <p class="text-xs text-slate-400 italic">No designs or mockups available</p>
                        @endif
                    </div>
                    @empty
                    <div class="p-6 text-center text-slate-500 text-sm">No items found</div>
                    @endforelse
                </div>
                <div class="p-6 bg-slate-50 flex justify-between items-center">
                    <span class="text-sm font-medium text-slate-500">Subtotal</span>
                    <span class="text-lg font-bold text-slate-900">{{ number_format($subtotal, 2) }} {{ $order->currency }}</span>
                </div>
            </section>
        </div>

        <!-- Middle Column: Shipping Details & Activity -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Shipping Details -->
            @if($order->shipping_address)
            <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#F7961D]">local_shipping</span>
                    <h3 class="font-bold text-slate-900">Shipping Details</h3>
                </div>
                <div class="p-6 space-y-4">
                    <!-- Order Information -->
                    @if($brand || $channel || $storeName || $salesChannel)
                    <div class="pb-4 border-b border-slate-100">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Order Information</p>
                        <div class="space-y-2">
                            @if($brand)
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-500">Brand</span>
                                <span class="text-sm font-semibold text-slate-900">{{ $brand }}</span>
                            </div>
                            @endif
                            @if($channel)
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-500">Channel</span>
                                <span class="text-sm font-semibold text-slate-900">{{ $channel }}</span>
                            </div>
                            @endif
                            @if($storeName)
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-500">Store Name</span>
                                <span class="text-sm font-semibold text-slate-900">{{ $storeName }}</span>
                            </div>
                            @endif
                            @if($salesChannel)
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-500">Sales Channel</span>
                                <span class="text-sm font-semibold text-slate-900">{{ $salesChannel }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Shipping Method -->
                    @if($shippingMethod)
                    <div class="pb-4 border-b border-slate-100">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Shipping Method</p>
                        <div class="flex items-center gap-2">
                            @if($shippingMethod === 'tiktok_label')
                            <span class="px-2.5 py-1 bg-green-100 text-green-700 rounded text-xs font-bold">TikTok Label</span>
                            @else
                            <span class="px-2.5 py-1 bg-slate-100 text-slate-700 rounded text-xs font-bold">{{ ucfirst(str_replace('_', ' ', $shippingMethod)) }}</span>
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="pb-4 border-b border-slate-100">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Shipping Method</p>
                        <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded text-xs font-bold">Ship by Seller</span>
                    </div>
                    @endif

                    <!-- Label Information -->
                    @if($labelName || $labelType)
                    <div class="pb-4 border-b border-slate-100">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Label Information</p>
                        <div class="space-y-2">
                            @if($labelName)
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-500">Label Name</span>
                                <span class="text-sm font-semibold text-slate-900">{{ $labelName }}</span>
                            </div>
                            @endif
                            @if($labelType)
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-500">Label Type</span>
                                <span class="text-sm font-semibold text-slate-900">{{ $labelType }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Comment -->
                    @if($comment)
                    <div class="pb-4 border-b border-slate-100">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Comment</p>
                        <p class="text-sm text-slate-700">{{ $comment }}</p>
                    </div>
                    @endif

                    <!-- Recipient -->
            <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Recipient</p>
                        <p class="text-sm font-semibold text-slate-900">{{ $order->shipping_address['name'] ?? 'N/A' }}</p>
                        @if(isset($order->shipping_address['email']))
                        <p class="text-sm text-slate-500">{{ $order->shipping_address['email'] }}</p>
                        @endif
                        @if(isset($order->shipping_address['phone']))
                        <p class="text-sm text-slate-500">{{ $order->shipping_address['phone'] }}</p>
                        @endif
            </div>
            <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Shipping Address</p>
                        <p class="text-sm text-slate-700 leading-relaxed">
                            {{ $order->shipping_address['address'] ?? '' }}@if(isset($order->shipping_address['address2'])), {{ $order->shipping_address['address2'] }}@endif<br/>
                            {{ $order->shipping_address['city'] ?? '' }}{{ isset($order->shipping_address['state']) ? ', ' . $order->shipping_address['state'] : '' }} {{ $order->shipping_address['postal_code'] ?? '' }}<br/>
                            @php
                                $countries = ['US' => 'United States', 'GB' => 'United Kingdom', 'CA' => 'Canada', 'AU' => 'Australia', 'VN' => 'Vietnam'];
                                $countryCode = $order->shipping_address['country'] ?? '';
                                $countryName = $countries[$countryCode] ?? $countryCode;
                            @endphp
                            {{ $countryName }}
                        </p>
                    </div>
                    @if($order->tracking_number)
                    <div class="pt-4 border-t border-slate-100">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Tracking Information</p>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-mono font-medium text-slate-900">{{ $order->tracking_number }}</span>
                            @if($order->tracking_url)
                            <a href="{{ $order->tracking_url }}" target="_blank" class="text-[#F7961D] hover:underline text-xs font-bold">Track Order</a>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($order->tiktok_label_url)
                    <div class="pt-4 border-t border-slate-100">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">TikTok Label URL</p>
                        <div class="flex items-center gap-2 p-2 bg-green-50 rounded-lg border border-green-100">
                            <span class="material-symbols-outlined text-green-600 text-sm">label</span>
                            <a href="{{ $order->tiktok_label_url }}" target="_blank" class="text-xs text-green-600 hover:underline truncate flex-1" title="{{ $order->tiktok_label_url }}">
                                {{ Str::limit($order->tiktok_label_url, 50) }}
                            </a>
                            <a href="{{ $order->tiktok_label_url }}" target="_blank" class="text-green-600 hover:text-green-700 transition-colors">
                                <span class="material-symbols-outlined text-sm">open_in_new</span>
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </section>
            @endif

            <!-- Order Activity -->
            <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#F7961D]">history</span>
                    <h3 class="font-bold text-slate-900">Order Activity</h3>
                </div>
                <div class="p-6">
                    <div class="relative space-y-6">
                        <div class="absolute left-3 top-2 bottom-2 w-0.5 bg-slate-100"></div>
                        
                        <!-- Order Created -->
                        <div class="relative flex gap-4">
                            <div class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center z-10">
                                <span class="material-symbols-outlined text-white text-[14px]">check</span>
            </div>
            <div>
                                <p class="text-sm font-bold text-slate-900">Order Created</p>
                                <p class="text-xs text-slate-500">{{ $order->created_at->format('M d, Y - h:i A') }}</p>
            </div>
            </div>

                        <!-- Paid -->
                        @if($order->payment_status === 'paid')
                        <div class="relative flex gap-4">
                            <div class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center z-10">
                                <span class="material-symbols-outlined text-white text-[14px]">check</span>
            </div>
            <div>
                                <p class="text-sm font-bold text-slate-900">Paid</p>
                                <p class="text-xs text-slate-500">{{ $order->created_at->format('M d, Y - h:i A') }}</p>
            </div>
            </div>
            @endif

                        <!-- Current Status -->
                        @if(in_array($order->status, ['processing', 'shipped', 'delivered']))
                        <div class="relative flex gap-4">
                            <div class="w-6 h-6 rounded-full bg-[#F7961D] flex items-center justify-center z-10 {{ $order->status === 'processing' ? 'animate-pulse' : '' }}">
                                @if($order->status === 'processing')
                                <div class="w-2 h-2 bg-white rounded-full"></div>
                                @else
                                <span class="material-symbols-outlined text-white text-[14px]">check</span>
                                @endif
                            </div>
            <div>
                                <p class="text-sm font-bold text-slate-900">{{ ucfirst($order->status) }}</p>
                                @if($order->submitted_at)
                                <p class="text-xs text-slate-500">{{ $order->submitted_at->format('M d, Y - h:i A') }}</p>
                                @else
                                <p class="text-xs text-slate-500">{{ $order->updated_at->format('M d, Y - h:i A') }}</p>
                    @endif
                            </div>
            </div>
            @endif

                        <!-- Future Status -->
                        @if(!in_array($order->status, ['delivered', 'cancelled', 'failed']))
                        <div class="relative flex gap-4 opacity-50">
                            <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center z-10"></div>
            <div>
                                <p class="text-sm font-bold text-slate-900">
                                    @if(in_array($order->status, ['pending', 'processing'])) Shipped
                                    @else Delivered
                                    @endif
                                </p>
                                <p class="text-xs text-slate-500">Not yet {{ strtolower($order->status === 'shipped' ? 'delivered' : 'shipped') }}</p>
            </div>
            </div>
            @endif
                    </div>
                </div>
            </section>
        </div>

        <!-- Right Column: Order Summary & Notes -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Order Summary -->
            <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#F7961D]">receipt_long</span>
                    <h3 class="font-bold text-slate-900">Order Summary</h3>
                </div>
                <div class="p-6 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Subtotal</span>
                        <span class="font-medium text-slate-900">{{ number_format($subtotal, 2) }} {{ $order->currency }}</span>
                    </div>
                    <div class="pt-4 border-t border-slate-100 flex justify-between items-center">
                        <span class="text-base font-bold text-slate-900">Total Amount</span>
                        <span class="text-xl font-bold text-[#F7961D]">{{ number_format($order->total_amount, 2) }} {{ $order->currency }}</span>
                    </div>
                </div>
            </section>

            <!-- Currency Statistics -->
            <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#F7961D]">payments</span>
                    <h3 class="font-bold text-slate-900">Currency Statistics</h3>
                </div>
                <div class="p-6 space-y-4">
                    @php
                        $orderCurrency = $order->currency ?? 'USD';
                        $orderAmount = floatval($order->total_amount);
                        
                        // Convert to USD
                        if (isset($pricingService)) {
                            $orderAmountUSD = $pricingService->convertCurrency($orderAmount, $orderCurrency, 'USD');
                        } else {
                            $pricingService = app(\App\Services\PricingService::class);
                            $orderAmountUSD = $pricingService->convertCurrency($orderAmount, $orderCurrency, 'USD');
                        }
                        
                        // Get wallet transactions
                        $walletTransactions = $walletTransactions ?? \App\Models\WalletTransaction::where('reference_type', \App\Models\Order::class)
                            ->where('reference_id', $order->id)
                            ->where('user_id', $order->user_id)
                            ->get();
                        
                        $walletPaidUSD = 0;
                        $creditUsedUSD = 0;
                        foreach ($walletTransactions as $transaction) {
                            if ($transaction->type === 'payment' && $transaction->amount < 0) {
                                $walletPaidUSD += abs($transaction->amount);
                            } elseif ($transaction->type === 'credit_used' && $transaction->amount < 0) {
                                $creditUsedUSD += abs($transaction->amount);
                            }
                        }
                    @endphp
                    
                    <!-- Order Currency -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Order Currency</span>
                            <span class="text-sm font-bold text-slate-900">{{ $orderCurrency }}</span>
            </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Order Amount</span>
                            <span class="text-sm font-semibold text-slate-900">{{ number_format($orderAmount, 2) }} {{ $orderCurrency }}</span>
            </div>
    </div>

                    <!-- USD Conversion -->
                    @if($orderCurrency !== 'USD')
                    <div class="pt-3 border-t border-slate-100 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Converted to USD</span>
                            <span class="text-sm font-bold text-slate-900">{{ number_format($orderAmountUSD, 2) }} USD</span>
                        </div>
                        <div class="text-xs text-slate-500">
                            Exchange rate applied for payment processing
        </div>
    </div>
    @endif

                    <!-- Payment Breakdown (if paid) -->
                    @if($order->payment_status === 'paid' && ($walletPaidUSD > 0 || $creditUsedUSD > 0))
                    <div class="pt-3 border-t border-slate-100 space-y-2">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Payment Breakdown (USD)</p>
                        @if($walletPaidUSD > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Paid from Wallet</span>
                            <span class="text-sm font-semibold text-slate-900">{{ number_format($walletPaidUSD, 2) }} USD</span>
                        </div>
                        @endif
                        @if($creditUsedUSD > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Paid from Credit</span>
                            <span class="text-sm font-semibold text-slate-900">{{ number_format($creditUsedUSD, 2) }} USD</span>
                        </div>
            @endif
                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-sm font-bold text-slate-900">Total Paid (USD)</span>
                            <span class="text-sm font-bold text-green-600">{{ number_format($walletPaidUSD + $creditUsedUSD, 2) }} USD</span>
        </div>
    </div>
    @endif

                    <!-- Currency Info -->
                    <div class="pt-3 border-t border-slate-100">
                        <div class="bg-slate-50 rounded-lg p-3 space-y-1">
                            <p class="text-xs font-semibold text-slate-600">Note:</p>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Orders are processed in {{ $orderCurrency }}. Payment is automatically converted to USD for wallet/credit deduction.
                                @if($orderCurrency !== 'USD')
                                Current rate: 1 {{ $orderCurrency }} = {{ number_format($orderAmountUSD / max($orderAmount, 0.01), 4) }} USD
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Notes -->
            <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-bold text-slate-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#F7961D]">sticky_note_2</span>
                        Notes
                    </h3>
                    @if(!isset($isCustomer) || !$isCustomer)
                    <button class="text-[#F7961D] text-xs font-bold hover:underline">Add Note</button>
                    @endif
                </div>
                <div class="p-6 space-y-4">
                    @if($order->notes)
                    <div class="bg-blue-50 p-3 rounded-lg border border-blue-100">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-[10px] font-bold bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded uppercase">Internal</span>
                            <span class="text-[10px] text-slate-400 font-medium">{{ $order->created_at->format('M d, h:i A') }}</span>
                        </div>
                        <p class="text-sm text-slate-700">{{ $order->notes }}</p>
                    </div>
                    @endif
                    @if($order->error_message)
                    <div class="bg-red-50 p-3 rounded-lg border border-red-100">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-[10px] font-bold bg-red-100 text-red-600 px-1.5 py-0.5 rounded uppercase">Error</span>
                            <span class="text-[10px] text-slate-400 font-medium">{{ $order->updated_at->format('M d, h:i A') }}</span>
                        </div>
                        <p class="text-sm text-slate-700">{{ $order->error_message }}</p>
                    </div>
                    @endif
                    @if(!$order->notes && !$order->error_message)
                    <p class="text-sm text-slate-400 italic text-center py-4">No notes available</p>
                    @endif
                </div>
            </section>

            @if(!isset($isCustomer) || !$isCustomer)
    <!-- Actions -->
            <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900">Admin Actions</h3>
                </div>
                <div class="p-6 space-y-3">
            @php
                $canSubmit = !$order->workshop_order_id && 
                             ($order->source === 'manual' || !$order->source) &&
                             (auth()->user()->isSuperAdmin() || auth()->user()->hasRole('fulfillment-staff'));
                $hasWorkshop = $order->workshop_id && $order->workshop;
                $workshopApiEnabled = $hasWorkshop && $order->workshop->api_enabled;
            @endphp

            @if($canSubmit)
                @if(!$hasWorkshop)
                    <!-- No workshop assigned - show button to open modal -->
                    <button type="button" onclick="openSubmitModal()" class="w-full px-4 py-2 rounded-lg text-sm font-semibold text-white transition-colors bg-green-500 hover:bg-green-600">
                        Submit to Workshop
                    </button>
                @elseif($workshopApiEnabled)
                    <!-- Workshop assigned and API enabled - submit directly -->
                    <form method="POST" action="{{ route('admin.orders.submit', $order) }}" class="inline w-full" onsubmit="return confirm('Submit this order to {{ $order->workshop->name }}?');">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 rounded-lg text-sm font-semibold text-white transition-colors bg-green-500 hover:bg-green-600">
                            Submit to {{ $order->workshop->name }}
                        </button>
                    </form>
                @else
                    <!-- Workshop assigned but API not enabled -->
                    <div class="w-full px-4 py-2 rounded-lg text-sm font-semibold text-slate-500 bg-slate-100 text-center">
                        Workshop API not enabled
                    </div>
                @endif
            @endif

            @if($order->workshop_order_id && $hasWorkshop && $workshopApiEnabled)
                    <form method="POST" action="{{ route('admin.orders.tracking', $order) }}" class="inline w-full">
                @csrf
                        <button type="submit" class="w-full px-4 py-2 rounded-lg text-sm font-semibold text-white transition-colors bg-blue-500 hover:bg-blue-600">
                    Get Tracking Info
                </button>
            </form>
            @endif

            <!-- Update Status Form -->
                    <form method="POST" action="{{ route('admin.orders.update-status', $order) }}" class="space-y-2">
                @csrf
                        <select name="status" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D] text-sm">
                        <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="failed" {{ $order->status === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                        <input type="text" name="tracking_number" value="{{ $order->tracking_number }}" placeholder="Tracking #" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D] text-sm">
                        <input type="url" name="tracking_url" value="{{ $order->tracking_url }}" placeholder="Tracking URL" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D] text-sm">
                        <button type="submit" class="w-full px-4 py-2 rounded-lg text-sm font-semibold text-white transition-colors bg-slate-500 hover:bg-slate-600">
                        Update Status
                    </button>
            </form>
        </div>
            </section>
            @endif
        </div>
    </div>
</div>

@if(!isset($isCustomer) || !$isCustomer)
@if(isset($workshops) && $workshops && $workshops->count() > 0)
<!-- Submit to Workshop Modal -->
<div id="submitWorkshopModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center pl-64">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 z-1101">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900">Select Workshop</h3>
            <button type="button" onclick="closeSubmitModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.orders.submit', $order) }}" id="submitWorkshopForm" onsubmit="return confirm('Submit this order to the selected workshop?');">
            @csrf
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Workshop</label>
                    <select name="workshop_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D] text-sm">
                        <option value="">-- Select Workshop --</option>
                        @foreach($workshops as $workshop)
                        <option value="{{ $workshop->id }}">{{ $workshop->name }} ({{ $workshop->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <button type="button" onclick="closeSubmitModal()" class="flex-1 px-4 py-2 rounded-lg text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 rounded-lg text-sm font-semibold text-white bg-green-500 hover:bg-green-600 transition-colors">
                        Submit
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openSubmitModal() {
    document.getElementById('submitWorkshopModal').classList.remove('hidden');
    document.getElementById('submitWorkshopModal').classList.add('flex');
}

function closeSubmitModal() {
    document.getElementById('submitWorkshopModal').classList.add('hidden');
    document.getElementById('submitWorkshopModal').classList.remove('flex');
}

// Close modal when clicking outside
document.getElementById('submitWorkshopModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeSubmitModal();
    }
});
</script>
@endif
@endif

@endsection

@php
    $activeMenu = 'orders';
@endphp





































