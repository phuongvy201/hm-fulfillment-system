@extends('layouts.admin-dashboard')

@section('title', 'Import File Details - ' . config('app.name', 'Laravel'))

@section('header-title')
<div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
    <a href="{{ route($routePrefix . '.orders.import-files') }}" class="hover:text-[#F7961D] transition-colors">Import Files</a>
    <span class="material-symbols-outlined text-xs">chevron_right</span>
    <span class="text-slate-900 font-medium">{{ $importFile->original_name }}</span>
</div>
<h2 class="text-2xl font-bold text-slate-900">Import File Details</h2>
@endsection

@section('header-subtitle', 'Review and process orders from import file')

@section('header-actions')
<div class="flex items-center gap-3">
    <a href="{{ route($routePrefix . '.orders.import-files') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
        Back to Files
    </a>
    <a href="{{ $importFile->file_url }}" target="_blank" class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm bg-blue-600 hover:bg-blue-700">
        Download File
    </a>
    @if(auth()->user()->isSuperAdmin())
    <button type="button" onclick="openSubmitModal()" class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm bg-green-600 hover:bg-green-700">
        Submit Orders to Workshop
    </button>
    @endif
</div>
@endsection

@section('content')
<div class="space-y-6">
    @if(auth()->user()->isSuperAdmin())
    <!-- Submit Orders Action Bar -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Submit Orders to Workshop</h3>
                <p class="text-xs text-slate-500 mt-1">Select orders from the list below and submit them to their assigned workshops</p>
            </div>
            <button type="button" onclick="openSubmitModal()" class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm bg-green-600 hover:bg-green-700">
                Submit Orders to Workshop
            </button>
        </div>
    </div>
    @endif

    <!-- File Info Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">File Name</p>
                <p class="text-sm font-semibold text-slate-900">{{ $importFile->original_name }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Uploaded By</p>
                <p class="text-sm font-semibold text-slate-900">{{ $importFile->uploader->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Status</p>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide
                    @if($importFile->status === 'pending') bg-yellow-100 text-yellow-700
                    @elseif($importFile->status === 'processing') bg-blue-100 text-blue-700
                    @elseif($importFile->status === 'completed') bg-emerald-100 text-emerald-700
                    @else bg-red-100 text-red-700
                    @endif">
                    {{ ucfirst($importFile->status) }}
                </span>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Uploaded At</p>
                <p class="text-sm font-semibold text-slate-900">{{ $importFile->created_at->format('M d, Y h:i A') }}</p>
            </div>
        </div>
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-slate-200">
            <div class="text-center">
                <p class="text-2xl font-bold text-slate-900">{{ $importFile->total_orders }}</p>
                <p class="text-xs text-slate-500 mt-1">Total Items</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-green-600">{{ $importFile->processed_orders }}</p>
                <p class="text-xs text-slate-500 mt-1">Processed</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-red-600">{{ $importFile->failed_orders }}</p>
                <p class="text-xs text-slate-500 mt-1">Failed</p>
            </div>
        </div>
    </div>

    <!-- Orders Table (Similar to index.blade.php) -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-sm high-density-table">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="w-12 !px-4"><input class="rounded border-slate-300 text-primary focus:ring-primary" type="checkbox" id="select-all"/></th>
                        <th>Order Identification</th>
                        <th>Customer</th>
                        <th class="text-right">Items</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($groupedOrders as $externalId => $orderGroup)
                    @php
                        $items = $orderGroup['items'] ?? [];
                        $itemCount = count($items);
                        $shippingAddress = $orderGroup['shipping_address'] ?? [];
                        $customerInfo = $orderGroup['customer_info'] ?? [];
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="!px-4" onclick="event.stopPropagation()">
                            <input class="rounded border-slate-300 text-primary focus:ring-primary order-checkbox" type="checkbox" value="{{ $externalId }}"/>
                        </td>
                        <td>
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-900">EXT: {{ $externalId }}</span>
                                <span class="text-[10px] text-slate-500 mt-0.5">{{ $itemCount }} item(s)</span>
                                @if(isset($orderGroup['workshop']) && $orderGroup['workshop'])
                                <span class="text-[10px] text-blue-600 mt-0.5">Workshop: {{ $orderGroup['workshop']->name }}</span>
                                @elseif(!isset($orderGroup['workshop_id']) || !$orderGroup['workshop_id'])
                                <span class="text-[10px] text-orange-600 mt-0.5">No Workshop</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="flex flex-col">
                                <span class="font-semibold text-slate-900">{{ $customerInfo['name'] ?? 'N/A' }}</span>
                                <span class="text-xs text-slate-500/70">{{ $customerInfo['email'] ?? '' }}</span>
                            </div>
                        </td>
                        <td class="text-right">
                            <span class="font-bold text-slate-900">{{ $itemCount }}</span>
                        </td>
                        <td class="text-center">
                            @php
                                $order = $orderGroup['order'] ?? null;
                                $status = $order ? $order->status : 'pending';
                            @endphp
                            @if($status === 'processing' || $status === 'submitted')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-blue-100 text-blue-700">
                                {{ ucfirst($status) }}
                            </span>
                            @elseif($status === 'failed')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-red-100 text-red-700">
                                Failed
                            </span>
                            @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-yellow-100 text-yellow-700">
                                Pending Review
                            </span>
                            @endif
                        </td>
                        <td class="text-right whitespace-nowrap">
                            <div class="flex justify-end gap-1">
                                <button type="button" class="p-1.5 hover:bg-white rounded-md text-slate-400 hover:text-slate-900 transition-colors toggle-row-btn" data-order-id="{{ $externalId }}" title="Toggle Details">
                                    <span class="material-symbols-outlined !text-[20px]">keyboard_arrow_down</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <!-- Expandable Row for Items -->
                    <tr id="details-row-{{ $externalId }}" class="hidden">
                        <td class="!p-0 bg-white" colspan="6">
                            @if(count($items) > 0)
                            <div class="border-l-4 border-primary ml-4 mr-4 my-2">
                                <div class="p-4 space-y-4">
                                    <div class="flex items-center justify-between px-2 mb-2">
                                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Order Items ({{ count($items) }})</span>
                                        @if(count($items) > 1)
                                        <span class="text-[10px] text-primary font-bold bg-orange-50 px-2 py-0.5 rounded">+{{ count($items) - 1 }} items in package</span>
                                        @endif
                                    </div>
                                    @foreach($items as $item)
                                    <div class="flex flex-col lg:flex-row lg:items-center gap-8 py-3 px-4 border border-slate-100 rounded-lg hover:border-slate-200 transition-colors">
                                        <div class="flex-1 flex items-center gap-4 min-w-[280px]">
                                            <div class="w-14 h-14 bg-slate-50 rounded border border-slate-200 flex items-center justify-center shrink-0 overflow-hidden">
                                                @php
                                                    $thumbnailUrl = '';
                                                    // Get first mockup or design URL
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        $mockupKey = 'mockup_url_' . $i;
                                                        if (!empty($item[$mockupKey])) {
                                                            $thumbnailUrl = $item[$mockupKey];
                                                            break;
                                                        }
                                                    }
                                                    if (!$thumbnailUrl) {
                                                        for ($i = 1; $i <= 5; $i++) {
                                                            $designKey = 'design_url_' . $i;
                                                            if (!empty($item[$designKey])) {
                                                                $thumbnailUrl = $item[$designKey];
                                                                break;
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                @if($thumbnailUrl)
                                                <img alt="Thumbnail" class="w-full h-full object-cover" src="{{ $thumbnailUrl }}" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'56\' height=\'56\'%3E%3Crect fill=\'%23f1f5f9\' width=\'56\' height=\'56\'/%3E%3Ctext fill=\'%2394a3b8\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' font-size=\'8\'%3ENo Image%3C/text%3E%3C/svg%3E'">
                                                @else
                                                <div class="w-full h-full flex items-center justify-center">
                                                    <span class="material-symbols-outlined text-slate-400 text-2xl">image</span>
                                                </div>
                                                @endif
                                            </div>
                                            <div class="space-y-0.5">
                                                <h4 class="font-bold text-slate-900 text-sm">{{ $item['part_number'] ?? 'N/A' }}</h4>
                                                <div class="flex items-center gap-2 text-[11px] text-slate-500">
                                                    <span>Qty: <b class="text-slate-900">{{ $item['quantity'] ?? 1 }}</b></span>
                                                    @if(!empty($item['title']))
                                                    <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                                    <span class="text-slate-900">{{ $item['title'] }}</span>
                                                    @endif
                                                </div>
                                                @if(!empty($item['description']))
                                                <p class="text-[10px] text-slate-400 italic">{{ $item['description'] }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex gap-10">
                                            <div class="space-y-2">
                                                <span class="text-[10px] font-bold uppercase text-slate-400 tracking-tighter">Mockups</span>
                                                <div class="flex gap-2">
                                                    @php
                                                        $mockups = [];
                                                        for ($i = 1; $i <= 5; $i++) {
                                                            $mockupKey = 'mockup_url_' . $i;
                                                            $positionKey = 'position_' . $i;
                                                            if (!empty($item[$mockupKey])) {
                                                                $mockups[] = [
                                                                    'url' => $item[$mockupKey],
                                                                    'position' => $item[$positionKey] ?? 'Position ' . $i
                                                                ];
                                                            }
                                                        }
                                                    @endphp
                                                    @if(count($mockups) > 0)
                                                        @foreach(array_slice($mockups, 0, 2) as $mockup)
                                                        <div class="w-10 h-10 rounded border border-slate-200 overflow-hidden relative group" onclick="event.stopPropagation(); openImageModal('{{ $mockup['url'] }}');">
                                                            <img class="w-full h-full object-cover cursor-pointer" src="{{ $mockup['url'] }}" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'40\' height=\'40\'%3E%3Crect fill=\'%23f1f5f9\' width=\'40\' height=\'40\'/%3E%3C/svg%3E'">
                                                            <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity cursor-pointer">
                                                                <span class="material-symbols-outlined !text-white !text-xs">zoom_in</span>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    @else
                                                        <span class="text-[10px] text-slate-400">—</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="space-y-2">
                                                <span class="text-[10px] font-bold uppercase text-slate-400 tracking-tighter">Designs</span>
                                                <div class="flex gap-2">
                                                    @php
                                                        $designs = [];
                                                        for ($i = 1; $i <= 5; $i++) {
                                                            $designKey = 'design_url_' . $i;
                                                            $positionKey = 'position_' . $i;
                                                            if (!empty($item[$designKey])) {
                                                                $designs[] = [
                                                                    'url' => $item[$designKey],
                                                                    'position' => $item[$positionKey] ?? 'Position ' . $i
                                                                ];
                                                            }
                                                        }
                                                    @endphp
                                                    @if(count($designs) > 0)
                                                        @foreach(array_slice($designs, 0, 2) as $design)
                                                        <div class="w-10 h-10 rounded border border-slate-200 overflow-hidden relative group" onclick="event.stopPropagation(); openImageModal('{{ $design['url'] }}');">
                                                            <img class="w-full h-full object-cover cursor-pointer" src="{{ $design['url'] }}" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'40\' height=\'40\'%3E%3Crect fill=\'%23f1f5f9\' width=\'40\' height=\'40\'/%3E%3C/svg%3E'">
                                                            <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity cursor-pointer">
                                                                <span class="material-symbols-outlined !text-white !text-xs">download</span>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    @else
                                                        <span class="text-[10px] text-slate-400">—</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                    <!-- Shipping Address -->
                                    <div class="mt-4 p-4 bg-slate-50 rounded-lg border border-slate-200">
                                        <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Shipping Address</h5>
                                        <div class="text-sm text-slate-600">
                                            <p><strong>{{ $shippingAddress['name'] ?? 'N/A' }}</strong></p>
                                            <p>{{ $shippingAddress['address'] ?? '' }}</p>
                                            @if(!empty($shippingAddress['address2']))
                                            <p>{{ $shippingAddress['address2'] }}</p>
                                            @endif
                                            <p>{{ $shippingAddress['city'] ?? '' }}, {{ $shippingAddress['state'] ?? '' }} {{ $shippingAddress['postal_code'] ?? '' }}</p>
                                            <p>{{ $shippingAddress['country'] ?? '' }}</p>
                                            @if(!empty($shippingAddress['email']))
                                            <p class="mt-2 text-xs text-slate-500">Email: {{ $shippingAddress['email'] }}</p>
                                            @endif
                                            @if(!empty($shippingAddress['phone']))
                                            <p class="text-xs text-slate-500">Phone: {{ $shippingAddress['phone'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="border-l-4 border-primary ml-4 mr-4 my-2">
                                <div class="p-4">
                                    <p class="text-sm text-slate-400 italic">No items in this order</p>
                                </div>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12">
                            <div class="text-center">
                                <svg class="mx-auto h-16 w-16 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <h3 class="mt-4 text-lg font-semibold text-slate-900">No Orders Found</h3>
                                <p class="mt-2 text-sm text-slate-500">No orders found in this import file.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Submit Orders Modal -->
@if(auth()->user()->isSuperAdmin())
<div id="submitOrdersModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm hidden" onclick="closeSubmitModal()">
    <div class="relative bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] flex flex-col overflow-hidden" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-slate-200 bg-slate-50">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Submit Orders to Workshop</h3>
                <p class="text-sm text-slate-500 mt-1">Select orders to submit and assign workshops</p>
            </div>
            <button onclick="closeSubmitModal()" class="p-2 hover:bg-slate-200 rounded-lg transition-colors text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <!-- Content -->
        <form id="submitOrdersForm" method="POST" action="{{ route($routePrefix . '.orders.import-files.submit-orders', $importFile) }}" class="flex-1 flex flex-col overflow-hidden">
            @csrf
            <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
                <div class="space-y-4">
                    @foreach($groupedOrders as $externalId => $orderGroup)
                    @php
                        $order = $orderGroup['order'] ?? null;
                        $workshopId = $orderGroup['workshop_id'] ?? null;
                        $workshop = $orderGroup['workshop'] ?? null;
                        $canSelectWorkshop = !$workshopId;
                    @endphp
                    <div class="border border-slate-200 rounded-lg p-4 hover:border-slate-300 transition-colors">
                        <div class="flex items-start gap-4">
                            <input 
                                type="checkbox" 
                                name="external_ids[]" 
                                value="{{ $externalId }}"
                                class="mt-1 rounded border-slate-300 text-primary focus:ring-primary order-submit-checkbox"
                                data-external-id="{{ $externalId }}"
                                data-workshop-id="{{ $workshopId }}"
                                onchange="toggleWorkshopSelection('{{ $externalId }}', this.checked)"
                            >
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <span class="font-bold text-slate-900">EXT: {{ $externalId }}</span>
                                        @if($workshop)
                                        <span class="ml-2 text-xs text-blue-600">Workshop: {{ $workshop->name }}</span>
                                        @else
                                        <span class="ml-2 text-xs text-orange-600">No Workshop Assigned</span>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($canSelectWorkshop)
                                <div class="mt-2">
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Select Workshop:</label>
                                    <select 
                                        name="workshop_mapping[{{ $externalId }}]"
                                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-primary focus:border-primary workshop-select"
                                        data-external-id="{{ $externalId }}"
                                        required
                                        disabled
                                    >
                                        <option value="">-- Select Workshop --</option>
                                        @foreach($workshops as $ws)
                                        <option value="{{ $ws->id }}">{{ $ws->name }} ({{ $ws->code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                @else
                                <input type="hidden" name="workshop_mapping[{{ $externalId }}]" value="{{ $workshopId }}">
                                <p class="text-xs text-slate-500 mt-1">Will be submitted to: <strong>{{ $workshop->name }}</strong></p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Footer -->
            <div class="border-t border-slate-200 p-6 bg-slate-50">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-600">
                        <span id="selectedCount">0</span> order(s) selected
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="closeSubmitModal()" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm bg-green-600 hover:bg-green-700">
                            Submit Selected Orders
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Image Modal - Compact Design & Mockup Review -->
<div id="imageModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;" onclick="closeImageModal()">
    <div class="relative bg-white rounded-xl shadow-2xl max-w-5xl w-full max-h-[90vh] flex flex-col overflow-hidden" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-slate-200 bg-slate-50">
            <div class="flex items-center gap-3">
                <h3 id="imageFileName" class="text-sm font-semibold text-slate-900 truncate">image.png</h3>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">READY FOR REVIEW</span>
            </div>
            <button onclick="closeImageModal()" class="p-2 hover:bg-slate-200 rounded-lg transition-colors text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Image Area -->
            <div class="flex-1 relative flex flex-col items-center justify-center p-6 bg-slate-50 overflow-auto">
                <div class="relative max-w-full max-h-full flex items-center justify-center">
                    <img id="modalImage" alt="Design Preview" class="max-w-full max-h-[60vh] object-contain shadow-lg rounded transition-transform" src="" style="transform: scale(1);">
                    <div id="imageInfo" class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-black/70 text-white px-3 py-1 rounded text-xs font-mono">
                        Zoom: 100%
                    </div>
                </div>
                <!-- Zoom Controls -->
                <div class="mt-4 flex items-center gap-2 bg-white border border-slate-200 p-2 rounded-lg shadow-sm">
                    <button onclick="zoomIn()" class="p-2 hover:bg-slate-100 rounded transition-colors text-slate-600">
                        <span class="material-symbols-outlined text-lg">zoom_in</span>
                    </button>
                    <button onclick="zoomOut()" class="p-2 hover:bg-slate-100 rounded transition-colors text-slate-600">
                        <span class="material-symbols-outlined text-lg">zoom_out</span>
                    </button>
                    <div class="w-px h-6 bg-slate-200 mx-1"></div>
                    <button onclick="fitScreen()" class="p-2 hover:bg-slate-100 rounded transition-colors text-slate-600">
                        <span class="material-symbols-outlined text-lg">fit_screen</span>
                    </button>
                    <button onclick="resetZoom()" class="p-2 hover:bg-slate-100 rounded transition-colors text-slate-600">
                        <span class="material-symbols-outlined text-lg">refresh</span>
                    </button>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="w-64 bg-white border-l border-slate-200 flex flex-col">
                <div class="p-4 space-y-4 flex-1 overflow-y-auto custom-scrollbar">
                    <div class="space-y-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">File Details</h4>
                        <div class="space-y-3">
                            <div>
                                <p class="text-[10px] font-semibold text-slate-400 uppercase">File Type</p>
                                <p id="imageFileType" class="text-sm font-mono text-slate-700">PNG</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold text-slate-400 uppercase">Position</p>
                                <p id="imagePosition" class="text-sm font-mono text-slate-700">—</p>
                            </div>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-slate-200">
                        <button onclick="downloadImage()" class="w-full py-2.5 bg-primary hover:bg-orange-500 text-white font-semibold rounded-lg transition-all flex items-center justify-center gap-2 text-sm">
                            <span class="material-symbols-outlined">download</span>
                            Download
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer with Thumbnails -->
        <div id="imageThumbnailsBar" class="border-t border-slate-200 bg-white p-3" onclick="event.stopPropagation()">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Gallery</span>
                    <div id="thumbnailContainer" class="flex items-center gap-2">
                        <!-- Thumbnails will be inserted here -->
                    </div>
                </div>
                <div class="ml-auto flex items-center gap-2">
                    <button onclick="previousImage()" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">arrow_back</span>
                        Previous
                    </button>
                    <button onclick="nextImage()" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm transition-all flex items-center gap-2">
                        Next
                        <span class="material-symbols-outlined text-lg">arrow_forward</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.high-density-table th {
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
}
.high-density-table td {
    padding: 0.75rem 1rem;
}
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
.overlay-backdrop {
    background: radial-gradient(circle, #1e293b 0%, #0f172a 100%);
}
.metadata-panel {
    background: rgba(15, 23, 42, 0.95);
    backdrop-filter: blur(12px);
}
</style>

@push('scripts')
<script>
function toggleRowDetails(orderId) {
    const row = document.getElementById('details-row-' + orderId);
    const btn = document.querySelector('.toggle-row-btn[data-order-id="' + orderId + '"]');
    const icon = btn?.querySelector('span');
    
    if (row) {
        const isHidden = row.classList.contains('hidden');
        row.classList.toggle('hidden');
        if (icon) {
            if (isHidden) {
                icon.textContent = 'keyboard_arrow_up';
            } else {
                icon.textContent = 'keyboard_arrow_down';
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toggle-row-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            const orderId = this.getAttribute('data-order-id');
            if (orderId) {
                toggleRowDetails(orderId);
            }
        });
    });
});

// Image Modal State
let imageModalState = {
    images: [],
    currentIndex: 0,
    zoomLevel: 1,
    imageData: {}
};

function openImageModal(imageUrl, imageData = {}) {
    console.log('openImageModal called with:', imageUrl);
    const modal = document.getElementById('imageModal');
    const img = document.getElementById('modalImage');
    
    if (!modal) {
        console.error('Modal element not found!');
        return;
    }
    
    if (!img) {
        console.error('Modal image element not found!');
        return;
    }
    
    // Collect all images from the current order/item context
    const contextImages = collectContextImages(imageUrl);
    console.log('Collected images:', contextImages);
    
    imageModalState.images = contextImages;
    imageModalState.currentIndex = contextImages.findIndex(img => img.url === imageUrl);
    if (imageModalState.currentIndex === -1) imageModalState.currentIndex = 0;
    imageModalState.imageData = imageData;
    imageModalState.zoomLevel = 1;
    
    updateModalImage();
    updateThumbnails();
    updateMetadata();
    
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    console.log('Modal should be visible now');
}

function collectContextImages(currentUrl) {
    // Collect all images from the current expanded row
    const images = [];
    const expandedRow = document.querySelector('tr[id^="details-row-"]:not(.hidden)');
    
    if (expandedRow) {
        // Collect all images with onclick containing openImageModal
        expandedRow.querySelectorAll('img[onclick]').forEach(img => {
            const onclickAttr = img.getAttribute('onclick');
            if (onclickAttr && onclickAttr.includes('openImageModal')) {
                // Try to extract URL from onclick attribute
                const match = onclickAttr.match(/openImageModal\(['"]([^'"]+)['"]\)/);
                if (match && match[1]) {
                    const url = match[1];
                    if (url && !images.find(i => i.url === url)) {
                        // Determine if it's mockup or design based on parent structure
                        const parentSection = img.closest('.space-y-2');
                        const isMockup = parentSection && parentSection.querySelector('span.text-\\[10px\\]')?.textContent?.includes('Mockup');
                        images.push({
                            url: url,
                            type: isMockup ? 'mockup' : 'design',
                            position: isMockup ? 'Mockup' : 'Design'
                        });
                    }
                }
            }
        });
    }
    
    // If no context images found, just use the current one
    if (images.length === 0) {
        images.push({ url: currentUrl, type: 'image', position: 'Image' });
    }
    
    return images;
}

function updateModalImage() {
    const img = document.getElementById('modalImage');
    const info = document.getElementById('imageInfo');
    
    if (imageModalState.images.length === 0) return;
    
    const currentImage = imageModalState.images[imageModalState.currentIndex];
    if (img) {
        img.src = currentImage.url;
        img.style.transform = `scale(${imageModalState.zoomLevel})`;
    }
    
    if (info) {
        info.textContent = `Zoom: ${Math.round(imageModalState.zoomLevel * 100)}%`;
    }
}

function updateThumbnails() {
    const container = document.getElementById('thumbnailContainer');
    if (!container) return;
    
    container.innerHTML = '';
    
    imageModalState.images.forEach((image, index) => {
        const isActive = index === imageModalState.currentIndex;
        const thumbnail = document.createElement('div');
        thumbnail.className = `relative group cursor-pointer ${isActive ? 'ring-2 ring-primary' : 'hover:ring-2 hover:ring-slate-300'} rounded overflow-hidden w-12 h-12 bg-slate-100 border ${isActive ? 'border-primary' : 'border-slate-200'} transition-all`;
        thumbnail.onclick = (e) => {
            e.stopPropagation();
            imageModalState.currentIndex = index;
            imageModalState.zoomLevel = 1;
            updateModalImage();
            updateThumbnails();
        };
        
        const img = document.createElement('img');
        img.src = image.url;
        img.alt = `Thumbnail ${index + 1}`;
        img.className = `w-full h-full object-cover ${isActive ? '' : 'opacity-60 group-hover:opacity-100'}`;
        img.onerror = function() { this.src = 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'48\' height=\'48\'%3E%3Crect fill=\'%23f1f5f9\' width=\'48\' height=\'48\'/%3E%3C/svg%3E'; };
        
        thumbnail.appendChild(img);
        container.appendChild(thumbnail);
    });
}

function updateMetadata() {
    const currentImage = imageModalState.images[imageModalState.currentIndex];
    if (!currentImage) return;
    
    const fileName = document.getElementById('imageFileName');
    const fileType = document.getElementById('imageFileType');
    const position = document.getElementById('imagePosition');
    
    if (fileName) {
        const urlParts = currentImage.url.split('/');
        fileName.textContent = urlParts[urlParts.length - 1] || 'image.png';
    }
    
    if (fileType) {
        const ext = currentImage.url.split('.').pop()?.toUpperCase() || 'PNG';
        fileType.textContent = ext;
    }
    
    if (position) {
        position.textContent = currentImage.position || '—';
    }
}

function zoomIn() {
    imageModalState.zoomLevel = Math.min(imageModalState.zoomLevel + 0.25, 3);
    updateModalImage();
}

function zoomOut() {
    imageModalState.zoomLevel = Math.max(imageModalState.zoomLevel - 0.25, 0.5);
    updateModalImage();
}

function fitScreen() {
    const img = document.getElementById('modalImage');
    if (!img) return;
    
    // Reset zoom and let CSS handle fitting
    imageModalState.zoomLevel = 1;
    img.style.transform = 'scale(1)';
    updateModalImage();
}

function resetZoom() {
    imageModalState.zoomLevel = 1;
    updateModalImage();
}

function previousImage() {
    if (imageModalState.images.length === 0) return;
    imageModalState.currentIndex = (imageModalState.currentIndex - 1 + imageModalState.images.length) % imageModalState.images.length;
    imageModalState.zoomLevel = 1;
    updateModalImage();
    updateThumbnails();
    updateMetadata();
}

function nextImage() {
    if (imageModalState.images.length === 0) return;
    imageModalState.currentIndex = (imageModalState.currentIndex + 1) % imageModalState.images.length;
    imageModalState.zoomLevel = 1;
    updateModalImage();
    updateThumbnails();
    updateMetadata();
}

function downloadImage() {
    const currentImage = imageModalState.images[imageModalState.currentIndex];
    if (currentImage) {
        const link = document.createElement('a');
        link.href = currentImage.url;
        link.download = currentImage.url.split('/').pop() || 'image.png';
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.style.display = 'none';
        document.body.style.overflow = '';
        imageModalState.zoomLevel = 1;
    }
}

document.getElementById('select-all')?.addEventListener('change', function() {
    document.querySelectorAll('.order-checkbox').forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
        closeSubmitModal();
    } else if (e.key === 'ArrowLeft' && !document.getElementById('imageModal')?.classList.contains('hidden')) {
        previousImage();
    } else if (e.key === 'ArrowRight' && !document.getElementById('imageModal')?.classList.contains('hidden')) {
        nextImage();
    }
});

// Submit Orders Modal Functions
function openSubmitModal() {
    const modal = document.getElementById('submitOrdersModal');
    if (modal) {
        modal.classList.remove('hidden');
        updateSelectedCount();
    }
}

function closeSubmitModal() {
    const modal = document.getElementById('submitOrdersModal');
    if (modal) {
        modal.classList.add('hidden');
        // Reset form
        document.querySelectorAll('.order-submit-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.workshop-select').forEach(sel => sel.value = '');
        updateSelectedCount();
    }
}

function toggleWorkshopSelection(externalId, checked) {
    const select = document.querySelector(`.workshop-select[data-external-id="${externalId}"]`);
    if (select) {
        select.disabled = !checked;
        if (!checked) {
            select.value = '';
        }
    }
    updateSelectedCount();
}

function updateSelectedCount() {
    const checked = document.querySelectorAll('.order-submit-checkbox:checked').length;
    const countEl = document.getElementById('selectedCount');
    if (countEl) {
        countEl.textContent = checked;
    }
}

// Select all checkbox
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('select-all');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.order-submit-checkbox').forEach(checkbox => {
                checkbox.checked = this.checked;
                const externalId = checkbox.getAttribute('data-external-id');
                toggleWorkshopSelection(externalId, this.checked);
            });
            updateSelectedCount();
        });
    }
    
    // Update count when checkboxes change
    document.querySelectorAll('.order-submit-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });
    
    // Form submission validation
    const form = document.getElementById('submitOrdersForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const checked = document.querySelectorAll('.order-submit-checkbox:checked');
            if (checked.length === 0) {
                e.preventDefault();
                alert('Please select at least one order to submit.');
                return false;
            }
            
            // Validate workshop selection for orders without workshop
            let hasError = false;
            checked.forEach(checkbox => {
                const externalId = checkbox.getAttribute('data-external-id');
                const workshopId = checkbox.getAttribute('data-workshop-id');
                if (!workshopId) {
                    const select = document.querySelector(`.workshop-select[data-external-id="${externalId}"]`);
                    if (select && !select.value) {
                        hasError = true;
                        select.classList.add('border-red-500');
                    }
                }
            });
            
            if (hasError) {
                e.preventDefault();
                alert('Please select workshop for all orders that do not have a workshop assigned.');
                return false;
            }
        });
    }
});
</script>
@endpush
@endsection

@php
    $activeMenu = 'orders';
@endphp

