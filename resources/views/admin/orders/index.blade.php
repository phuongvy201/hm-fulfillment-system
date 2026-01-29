@extends('layouts.admin-dashboard')

@section('title', 'Orders Management - ' . config('app.name', 'Laravel'))

@section('header-title', 'Orders Management')
@section('header-subtitle', 'Manage and track all orders')

@section('header-actions')
<div class="flex items-center gap-3">
    @if(isset($routePrefix) && $routePrefix === 'admin')
    <a href="{{ route($routePrefix . '.orders.import-files') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-700 transition-all shadow-sm border border-slate-300 bg-white hover:bg-slate-50">
        Import Files
    </a>
    @endif
    <a href="{{ route($routePrefix . '.orders.import') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-700 transition-all shadow-sm border border-slate-300 bg-white hover:bg-slate-50">
        Import Orders
    </a>
    <a href="{{ route($routePrefix . '.orders.create') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm bg-blue-600 hover:bg-blue-700">
        + Create Order
    </a>
    <a href="{{ route($routePrefix . '.orders.export', request()->query()) }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-700 transition-all shadow-sm border border-slate-300 bg-white hover:bg-slate-50 flex items-center gap-2">
        <span class="material-symbols-outlined text-sm">download</span>
        Export Filtered
    </a>
    <button type="button" id="bulkExportBtn" class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-700 transition-all shadow-sm border border-slate-300 bg-white hover:bg-slate-50 hidden items-center gap-2" onclick="bulkExportOrders()" style="display: none;">
        <span class="material-symbols-outlined text-sm">download</span>
        <span id="bulkExportText">Export Selected</span>
    </button>
    @if(isset($routePrefix) && $routePrefix === 'admin' && (auth()->user()->isSuperAdmin() || auth()->user()->hasRole('fulfillment-staff')))
    <button type="button" id="bulkSubmitBtn" class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm bg-green-600 hover:bg-green-700 hidden items-center gap-2" onclick="openBulkSubmitModal()" style="display: none;">
        <span class="material-symbols-outlined text-sm">send</span>
        <span id="bulkSubmitText">Submit Selected</span>
    </button>
    @endif
    <button type="button" id="bulkDeleteBtn" class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm bg-red-600 hover:bg-red-700 hidden items-center gap-2" onclick="bulkDeleteOrders()" style="display: none;">
        <span class="material-symbols-outlined text-sm">delete</span>
        <span id="bulkDeleteText">Delete Selected</span>
    </button>
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

    <!-- Statistics Cards -->
    @php
        $totalOrders = $orders->total() ?? 0;
        $totalItems = 0;
        $pendingOrders = 0;
        foreach($orders as $order) {
            $items = is_array($order->items) ? $order->items : json_decode($order->items, true) ?? [];
            $totalItems += count($items);
            if($order->status === 'pending') $pendingOrders++;
        }
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-3xl">shopping_cart</span>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Total Orders</p>
                <p class="text-3xl font-bold">{{ number_format($totalOrders) }}</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-3xl">inventory_2</span>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Total Items</p>
                <p class="text-3xl font-bold">{{ $totalItems }}</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-3xl">pending_actions</span>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Pending Orders</p>
                <p class="text-3xl font-bold">{{ $pendingOrders }}</p>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <section class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route($routePrefix . '.orders.index') }}" id="searchForm">
            <!-- Main Search Bar -->
            <div class="flex items-center gap-3 mb-4">
                <div class="flex-1 relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl">search</span>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Search by Order ID, Name or Email..." 
                        class="w-full pl-11 pr-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm"
                    >
                </div>
                
                <!-- Filter Buttons -->
                <div class="flex items-center gap-2">
                    <!-- Filters Dropdown -->
                    <div class="relative">
                        @php
                            $activeFiltersCount = 0;
                            if(request('status')) $activeFiltersCount++;
                            if(request('external_id')) $activeFiltersCount++;
                            if(request('workshop_order_id')) $activeFiltersCount++;
                            if(request('source')) $activeFiltersCount++;
                            if(request('from_date') && request('to_date')) $activeFiltersCount++;
                        @endphp
                        <button 
                            type="button" 
                            onclick="toggleFiltersDropdown()"
                            class="px-4 py-3 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 transition-all flex items-center gap-2 text-sm font-semibold text-slate-700 relative"
                        >
                            <span class="material-symbols-outlined text-lg">tune</span>
                            <span>Filters</span>
                            @if($activeFiltersCount > 0)
                            <span class="absolute -top-1 -right-1 w-5 h-5 bg-primary text-white rounded-full flex items-center justify-center text-[10px] font-bold">
                                {{ $activeFiltersCount }}
                            </span>
                            @endif
                            <span class="material-symbols-outlined text-sm">keyboard_arrow_down</span>
                        </button>
                        <!-- Filters Dropdown Menu -->
                        <div id="filtersDropdown" class="absolute top-full right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-slate-200 z-50 hidden">
                            <div class="p-4 space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-2">External ID</label>
                                    <input 
                                        type="text" 
                                        id="dropdown_external_id"
                                        value="{{ request('external_id') }}" 
                                        placeholder="Enter External ID" 
                                        class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm"
                                    >
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-2">Workshop Order ID</label>
                                    <input 
                                        type="text" 
                                        id="dropdown_workshop_order_id"
                                        value="{{ request('workshop_order_id') }}" 
                                        placeholder="Enter Workshop Order ID" 
                                        class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm"
                                    >
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-2">Status</label>
                                    <select id="dropdown_status" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                                        <option value="">All Statuses</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                                        <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-2">Source</label>
                                    <select id="dropdown_source" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                                        <option value="">All Sources</option>
                                        <option value="manual" {{ request('source') == 'manual' ? 'selected' : '' }}>Manual</option>
                                        <option value="import_file" {{ request('source') == 'import_file' ? 'selected' : '' }}>Imported (File)</option>
                                    </select>
                                </div>
                                <div class="flex items-center gap-2 pt-2 border-t border-slate-200">
                                    <button type="button" onclick="applyFilters()" class="flex-1 px-4 py-2 rounded-lg text-sm font-semibold text-white bg-primary hover:bg-orange-600 transition-colors">
                                        Apply
                                    </button>
                                    <button type="button" onclick="clearFilters()" class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                                        Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Date Range Dropdown -->
                    <div class="relative">
                        <button 
                            type="button" 
                            onclick="toggleDateRangeDropdown()"
                            class="px-4 py-3 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 transition-all flex items-center gap-2 text-sm font-semibold text-slate-700"
                        >
                            <span class="material-symbols-outlined text-lg">calendar_today</span>
                            <span>Date Range</span>
                            <span class="material-symbols-outlined text-sm">keyboard_arrow_down</span>
                        </button>
                        <!-- Date Range Dropdown Menu -->
                        <div id="dateRangeDropdown" class="absolute top-full right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-slate-200 z-50 hidden">
                            <div class="p-4">
                                <label class="block text-xs font-semibold text-slate-700 mb-2">Select Date Range</label>
                                <input 
                                    type="text" 
                                    id="dateRangePicker" 
                                    placeholder="Select date range..."
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-primary focus:border-primary text-sm"
                                    readonly
                                >
                                <div class="flex items-center gap-2 pt-4 mt-4 border-t border-slate-200">
                                    <button type="button" onclick="applyDateRange()" class="flex-1 px-4 py-2 rounded-lg text-sm font-semibold text-white bg-primary hover:bg-orange-600 transition-colors">
                                        Apply
                                    </button>
                                    <button type="button" onclick="clearDateRange()" class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                                        Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if(!isset($isCustomer) || !$isCustomer)
                    <!-- Warehouse Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <select 
                            name="workshop_id" 
                            onchange="this.form.submit()"
                            class="px-4 py-3 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 transition-all flex items-center gap-2 text-sm font-semibold text-slate-700 appearance-none cursor-pointer pr-10"
                            style="background-image: url('data:image/svg+xml;charset=UTF-8,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23334155\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3E%3Cpath d=\'M6 9l6 6 6-6\'/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1rem;"
                        >
                            <option value="">All Warehouses</option>
                            @foreach($workshops as $workshop)
                                <option value="{{ $workshop->id }}" {{ request('workshop_id') == $workshop->id ? 'selected' : '' }}>
                                    {{ $workshop->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    
                    <!-- Action Buttons -->
                    <button 
                        type="submit" 
                        class="px-6 py-3 bg-primary hover:bg-orange-600 text-white font-semibold rounded-lg transition-all shadow-sm"
                    >
                        Search
                    </button>
                    @if(request()->anyFilled(['external_id', 'workshop_order_id', 'search', 'status', 'workshop_id', 'from_date', 'to_date', 'source']))
                    <a 
                        href="{{ route($routePrefix . '.orders.index') }}" 
                        class="px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition-all"
                    >
                        Reset
                    </a>
                    @endif
                </div>
            </div>
            
            <!-- Active Filters Display -->
            @php
                $hasActiveFilters = false;
                $activeFilters = [];
                if(request('status')) {
                    $activeFilters[] = ['key' => 'status', 'label' => 'Status: ' . ucfirst(request('status')), 'value' => request('status')];
                    $hasActiveFilters = true;
                }
                if(request('workshop_id')) {
                    $workshopName = $workshops->firstWhere('id', request('workshop_id'))->name ?? 'Unknown';
                    $activeFilters[] = ['key' => 'workshop_id', 'label' => 'Warehouse: ' . $workshopName, 'value' => request('workshop_id')];
                    $hasActiveFilters = true;
                }
                if(request('from_date') && request('to_date')) {
                    $fromDate = \Carbon\Carbon::parse(request('from_date'))->format('M d');
                    $toDate = \Carbon\Carbon::parse(request('to_date'))->format('M d');
                    $activeFilters[] = ['key' => 'date_range', 'label' => $fromDate . ' - ' . $toDate, 'value' => 'date_range'];
                    $hasActiveFilters = true;
                }
                if(request('external_id')) {
                    $activeFilters[] = ['key' => 'external_id', 'label' => 'External ID: ' . request('external_id'), 'value' => request('external_id')];
                    $hasActiveFilters = true;
                }
                if(request('workshop_order_id')) {
                    $activeFilters[] = ['key' => 'workshop_order_id', 'label' => 'Workshop Order: ' . request('workshop_order_id'), 'value' => request('workshop_order_id')];
                    $hasActiveFilters = true;
                }
                if(request('source')) {
                    $sourceLabel = request('source') == 'import_file' ? 'Imported (File)' : 'Manual';
                    $activeFilters[] = ['key' => 'source', 'label' => 'Source: ' . $sourceLabel, 'value' => request('source')];
                    $hasActiveFilters = true;
                }
            @endphp
            
            @if($hasActiveFilters)
            <div class="flex items-center gap-2 flex-wrap pt-3 border-t border-slate-200">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">ACTIVE:</span>
                @foreach($activeFilters as $filter)
                @php
                    $paramsToRemove = [$filter['key']];
                    if($filter['key'] == 'date_range') {
                        $paramsToRemove = ['from_date', 'to_date'];
                    }
                    $newParams = request()->except($paramsToRemove);
                @endphp
                <a 
                    href="{{ route($routePrefix . '.orders.index', $newParams) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-50 text-slate-700 rounded-full text-xs font-semibold hover:bg-orange-100 transition-colors"
                >
                    <span>{{ $filter['label'] }}</span>
                    <span class="material-symbols-outlined text-sm">close</span>
                </a>
                @endforeach
                <a 
                    href="{{ route($routePrefix . '.orders.index') }}"
                    class="text-xs font-semibold text-slate-600 hover:text-slate-900 transition-colors ml-2"
                >
                    Clear All
                </a>
            </div>
            @endif
            
            <!-- Hidden Inputs for Filters -->
            <input type="hidden" name="external_id" id="filter_external_id" value="{{ request('external_id') }}">
            <input type="hidden" name="workshop_order_id" id="filter_workshop_order_id" value="{{ request('workshop_order_id') }}">
            <input type="hidden" name="status" id="filter_status" value="{{ request('status') }}">
            <input type="hidden" name="source" id="filter_source" value="{{ request('source') }}">
            <input type="hidden" name="from_date" id="from_date" value="{{ request('from_date') }}">
            <input type="hidden" name="to_date" id="to_date" value="{{ request('to_date') }}">
        </form>
    </section>

    <!-- Orders Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-base high-density-table">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="w-14 !px-5"><input class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary" type="checkbox" id="select-all"/></th>
                        <th>Order Identification</th>
                        @if(!isset($isCustomer) || !$isCustomer)
                        <th>Customer</th>
                        <th class="text-center">Warehouse</th>
                        @endif
                        <th class="text-right">Total</th>
                        <th class="text-center">Status & Tracking</th>
                        <th>Created</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                    @php
                        $items = is_array($order->items) ? $order->items : json_decode($order->items, true) ?? [];
                        $itemCount = count($items);
                        $apiRequest = is_array($order->api_request) ? $order->api_request : json_decode($order->api_request, true) ?? [];
                        $externalId = $apiRequest['order_id'] ?? $order->order_number ?? 'N/A';
                        $source = $order->source ?? 'manual';
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors {{ $loop->first ? 'bg-orange-50/30 hover:bg-orange-50/50' : '' }}">
                        <td class="!px-5" onclick="event.stopPropagation()">
                            @php
                                $canDelete = !isset($isCustomer) || !$isCustomer || $order->status === 'on_hold';
                            @endphp
                            <input 
                                class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary order-checkbox {{ !$canDelete ? 'opacity-50 cursor-not-allowed' : '' }}" 
                                type="checkbox" 
                                value="{{ $order->id }}" 
                                data-order-id="{{ $order->id }}"
                                data-status="{{ $order->status }}"
                                @if(!$canDelete) disabled title="Only orders with 'On Hold' status can be deleted" @endif
                            />
                        </td>
                        <td>
                            <div class="flex flex-col gap-1">
                                @if(!isset($isCustomer) || !$isCustomer)
                                <span class="font-bold text-slate-900">#{{ $order->workshop_order_id }}</span>
                                @endif
                                <span class="font-mono text-xs text-slate-500 {{ (!isset($isCustomer) || !$isCustomer) ? 'mt-0.5' : '' }}">EXT: {{ $externalId }}</span>
                                <span class="inline-flex items-center gap-1 mt-0.5">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold
                                        @if($source === 'import_file') bg-emerald-50 text-emerald-700 border border-emerald-100
                                        @else bg-slate-50 text-slate-600 border border-slate-200
                                        @endif">
                                        {{ $source === 'import_file' ? 'Imported (File)' : 'Manual' }}
                                    </span>
                                </span>
                            </div>
                        </td>
                        @if(!isset($isCustomer) || !$isCustomer)
                        <td>
                            <div class="flex flex-col">
                                <span class="font-semibold text-slate-900 text-sm">{{ $order->user->name }}</span>
                                <span class="text-xs text-slate-500/70">{{ $order->user->email ?? '' }}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            @if(isset($order->workshop))
                            <span class="inline-block px-2.5 py-1 bg-slate-100 text-slate-600 rounded text-xs font-bold">{{ $order->workshop->name ?? 'N/A' }}</span>
                            @else
                            <span class="text-[10px] text-slate-400">—</span>
                            @endif
                        </td>
                        @endif
                        <td class="text-right">
                            <span class="font-bold text-slate-900">${{ number_format($order->total_amount, 2) }}</span>
                        </td>
                        <td class="text-center">
                            <div class="flex flex-col items-center gap-1">
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide
                                    @if($order->status === 'on_hold') bg-orange-100 text-orange-700
                                    @elseif($order->status === 'pending') bg-yellow-100 text-yellow-700
                                    @elseif($order->status === 'processing') bg-blue-100 text-blue-700
                                    @elseif($order->status === 'shipped') bg-purple-100 text-purple-700
                                    @elseif($order->status === 'delivered') bg-emerald-100 text-emerald-700
                                    @elseif($order->status === 'cancelled') bg-gray-100 text-gray-700
                                    @else bg-red-100 text-red-700
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
                                @if($order->tracking_number)
                                <span class="text-xs text-slate-600 font-mono">TRK: {{ $order->tracking_number }}</span>
                                @else
                                <span class="text-xs text-slate-300 font-mono">—</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="text-sm text-slate-600 whitespace-nowrap">{{ $order->created_at->format('M d, Y') }}</span>
                            <span class="block text-xs text-slate-400">{{ $order->created_at->format('h:i A') }}</span>
                        </td>
                        <td class="text-right whitespace-nowrap">
                            <div class="flex justify-end gap-1">
                                <a href="{{ route($routePrefix . '.orders.show', $order) }}" class="p-2 hover:bg-white rounded-md text-slate-400 hover:text-primary transition-colors" onclick="event.stopPropagation()" title="View">
                                    <span class="material-symbols-outlined !text-[24px]">visibility</span>
                                </a>
                                <a href="{{ route($routePrefix . '.orders.edit', $order) }}" class="p-2 hover:bg-white rounded-md text-slate-400 hover:text-slate-900 transition-colors" onclick="event.stopPropagation()" title="Edit">
                                    <span class="material-symbols-outlined !text-[24px]">edit</span>
                                </a>
                                <button type="button" class="p-2 hover:bg-white rounded-md text-slate-400 hover:text-slate-900 transition-colors toggle-row-btn" data-order-id="{{ $order->id }}" title="Toggle Details">
                                    <span class="material-symbols-outlined !text-[24px]">keyboard_arrow_down</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <!-- Expandable Row for Items -->
                    <tr id="details-row-{{ $order->id }}" class="hidden">
                        <td class="!p-0 bg-white" colspan="{{ (isset($isCustomer) && $isCustomer) ? '6' : '8' }}">
                            @if(count($items) > 0)
                            <div class="border-l-4 border-primary ml-4 mr-4 my-2">
                                <div class="p-4 space-y-4">
                                    <div class="flex items-center justify-between px-2 mb-2">
                                        <span class="text-sm font-bold text-slate-500 uppercase tracking-wider">Order Items ({{ count($items) }})</span>
                                        @if(count($items) > 1)
                                        <span class="text-xs text-primary font-bold bg-orange-50 px-2.5 py-1 rounded">+{{ count($items) - 1 }} items in package</span>
                                        @endif
                                    </div>
                                    @foreach($items as $item)
                                    <div class="flex flex-col lg:flex-row lg:items-center gap-8 py-3 px-4 border border-slate-100 rounded-lg hover:border-slate-200 transition-colors">
                                        <div class="flex-1 flex items-center gap-4 min-w-[280px]">
                                            <div class="w-16 h-16 bg-slate-50 rounded border border-slate-200 flex items-center justify-center shrink-0 overflow-hidden">
                                                @php
                                                    $thumbnailUrl = '';
                                                    if(isset($item['mockups']) && is_array($item['mockups']) && count($item['mockups']) > 0) {
                                                        $thumbnailUrl = $item['mockups'][0]['url'] ?? '';
                                                    } elseif(isset($item['designs']) && is_array($item['designs']) && count($item['designs']) > 0) {
                                                        $thumbnailUrl = $item['designs'][0]['url'] ?? '';
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
                                                <h4 class="font-bold text-slate-900">{{ $item['variant_name'] ?? $item['product_name'] ?? 'N/A' }}</h4>
                                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                                    <span>Qty: <b class="text-slate-900">{{ $item['quantity'] ?? 1 }}</b></span>
                                                    <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                                    <span>Label: <span class="text-slate-900">{{ $item['label_name'] ?? 'Standard' }}</span></span>
                                                </div>
                                                <p class="text-[10px] text-slate-400 italic">{{ $item['product_title'] ?? 'No special instructions' }}</p>
                                            </div>
                                        </div>
                                        <div class="flex gap-10">
                                            <div class="space-y-2">
                                                <span class="text-xs font-bold uppercase text-slate-400 tracking-tighter">Mockups</span>
                                                <div class="flex gap-2">
                                                    @if(isset($item['mockups']) && is_array($item['mockups']) && count($item['mockups']) > 0)
                                                        @foreach(array_slice($item['mockups'], 0, 2) as $mockup)
                                                        <div class="w-12 h-12 rounded border border-slate-200 overflow-hidden relative group cursor-pointer" onclick="event.stopPropagation(); openImageModal('{{ $mockup['url'] ?? '' }}');">
                                                            <img class="w-full h-full object-cover cursor-pointer" src="{{ $mockup['url'] ?? '' }}" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'40\' height=\'40\'%3E%3Crect fill=\'%23f1f5f9\' width=\'40\' height=\'40\'/%3E%3C/svg%3E'">
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
                                                <span class="text-xs font-bold uppercase text-slate-400 tracking-tighter">Designs</span>
                                                <div class="flex gap-2">
                                                    @if(isset($item['designs']) && is_array($item['designs']) && count($item['designs']) > 0)
                                                        @foreach(array_slice($item['designs'], 0, 2) as $design)
                                                        <div class="w-12 h-12 rounded border border-slate-200 overflow-hidden relative group cursor-pointer" onclick="event.stopPropagation(); openImageModal('{{ $design['url'] ?? '' }}');">
                                                            <img class="w-full h-full object-cover cursor-pointer" src="{{ $design['url'] ?? '' }}" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'40\' height=\'40\'%3E%3Crect fill=\'%23f1f5f9\' width=\'40\' height=\'40\'/%3E%3C/svg%3E'">
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
                        <td colspan="{{ (isset($isCustomer) && $isCustomer) ? '6' : '8' }}" class="px-6 py-12">
                            <div class="text-center">
                                <svg class="mx-auto h-16 w-16 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <h3 class="mt-4 text-lg font-semibold text-slate-900">No Orders Found</h3>
                                <p class="mt-2 text-sm text-slate-500">No orders found matching the filters.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        @if($orders->hasPages() || $orders->total() > 0)
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50 flex items-center justify-between">
            <span class="text-sm text-slate-500">
                Showing <span class="font-semibold text-slate-900">{{ $orders->firstItem() ?? 0 }}</span> to <span class="font-semibold text-slate-900">{{ $orders->lastItem() ?? 0 }}</span> of <span class="font-semibold text-slate-900">{{ $orders->total() }}</span> entries
            </span>
            @if($orders->hasPages())
            <div class="flex items-center gap-1">
                @if($orders->onFirstPage())
                <button class="p-2 hover:bg-slate-200 rounded transition-colors text-slate-400 cursor-not-allowed" disabled>
                    <span class="material-symbols-outlined text-sm">chevron_left</span>
                </button>
                @else
                <a href="{{ $orders->previousPageUrl() }}" class="p-2 hover:bg-slate-200 rounded transition-colors text-slate-400">
                    <span class="material-symbols-outlined text-sm">chevron_left</span>
                </a>
                @endif
                
                @foreach($orders->getUrlRange(1, min(5, $orders->lastPage())) as $page => $url)
                @if($page == $orders->currentPage())
                <button class="w-8 h-8 flex items-center justify-center rounded bg-primary text-white font-bold text-sm">{{ $page }}</button>
                @else
                <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-200 text-sm">{{ $page }}</a>
                @endif
                @endforeach
                
                @if($orders->hasMorePages())
                @if($orders->lastPage() > 5)
                <span class="px-2 text-slate-400">...</span>
                <a href="{{ $orders->url($orders->lastPage()) }}" class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-200 text-sm">{{ $orders->lastPage() }}</a>
                @endif
                <a href="{{ $orders->nextPageUrl() }}" class="p-2 hover:bg-slate-200 rounded transition-colors text-slate-400">
                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                </a>
                @else
                <button class="p-2 hover:bg-slate-200 rounded transition-colors text-slate-400 cursor-not-allowed" disabled>
                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                </button>
                @endif
            </div>
            @endif
        </div>
        @endif
    </div>
</div>

<!-- Bulk Delete Form (Hidden) -->
<form id="bulkDeleteForm" method="POST" action="{{ route($routePrefix . '.orders.bulk-destroy') }}" style="display: none;">
    @csrf
    @method('DELETE')
    <input type="hidden" name="order_ids" id="bulkDeleteOrderIds" value="">
</form>

<!-- Bulk Export Form (Hidden) -->
<form id="bulkExportForm" method="GET" action="{{ route($routePrefix . '.orders.export') }}" style="display: none;">
    <input type="hidden" name="order_ids" id="bulkExportOrderIds" value="">
</form>

@if(isset($routePrefix) && $routePrefix === 'admin' && (auth()->user()->isSuperAdmin() || auth()->user()->hasRole('fulfillment-staff')))
@php
    $workshops = \App\Models\Workshop::where('status', 'active')
        ->where('api_enabled', true)
        ->orderBy('name')
        ->get();
@endphp
@if($workshops && $workshops->count() > 0)
<!-- Bulk Submit Modal -->
<div id="bulkSubmitModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center pl-64">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 z-1101">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900">Submit Orders to Workshop</h3>
            <button type="button" onclick="closeBulkSubmitModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.orders.bulk-submit') }}" id="bulkSubmitForm" onsubmit="return confirm('Submit selected orders to the selected workshop?');">
            @csrf
            <div class="p-6 space-y-4">
                <div>
                    <p class="text-sm text-slate-600 mb-2">
                        <span id="bulkSubmitCount">0</span> order(s) will be submitted
                    </p>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Workshop</label>
                    <select name="workshop_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#F7961D] focus:border-[#F7961D] text-sm">
                        <option value="">-- Select Workshop --</option>
                        @foreach($workshops as $workshop)
                        <option value="{{ $workshop->id }}">{{ $workshop->name }} ({{ $workshop->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <button type="button" onclick="closeBulkSubmitModal()" class="flex-1 px-4 py-2 rounded-lg text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 rounded-lg text-sm font-semibold text-white bg-green-500 hover:bg-green-600 transition-colors">
                        Submit
                    </button>
                </div>
            </div>
            <input type="hidden" name="order_ids" id="bulkSubmitOrderIds" value="">
        </form>
    </div>
</div>
@endif
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
    padding: 1rem 1.25rem;
    font-size: 0.9375rem;
    font-weight: 600;
}
.high-density-table td {
    padding: 1rem 1.25rem;
    font-size: 0.9375rem;
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

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
.flatpickr-calendar {
    font-family: 'Inter', sans-serif;
}
.flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange {
    background: #f7951d;
    border-color: #f7951d;
}
.flatpickr-day.selected:hover, .flatpickr-day.startRange:hover, .flatpickr-day.endRange:hover {
    background: #d67a0f;
    border-color: #d67a0f;
}
/* Dropdown positioning */
#filtersDropdown, #dateRangeDropdown {
    max-height: 80vh;
    overflow-y: auto;
}
/* Ensure flatpickr calendar appears above dropdown */
.flatpickr-calendar {
    z-index: 9999 !important;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
let dateRangePickerInstance = null;

// Initialize Date Range Picker
document.addEventListener('DOMContentLoaded', function() {
    const dateRangeInput = document.getElementById('dateRangePicker');
    const fromDateInput = document.getElementById('from_date');
    const toDateInput = document.getElementById('to_date');
    
    if (dateRangeInput) {
        // Prepare default dates
        let defaultDates = null;
        if (fromDateInput?.value && toDateInput?.value) {
            defaultDates = [fromDateInput.value, toDateInput.value];
        } else if (fromDateInput?.value) {
            defaultDates = [fromDateInput.value];
        }
        
        // Initialize Flatpickr with range mode
        dateRangePickerInstance = flatpickr(dateRangeInput, {
            mode: 'range',
            dateFormat: 'Y-m-d',
            allowInput: false,
            placeholder: 'Select date range...',
            defaultDate: defaultDates,
            onChange: function(selectedDates, dateStr, instance) {
                // Store selected dates in instance for later use
                instance.selectedDates = selectedDates;
            }
        });
    }
});

// Filters Dropdown Functions
function toggleFiltersDropdown() {
    const dropdown = document.getElementById('filtersDropdown');
    const dateRangeDropdown = document.getElementById('dateRangeDropdown');
    
    // Close date range dropdown if open
    if (dateRangeDropdown) {
        dateRangeDropdown.classList.add('hidden');
    }
    
    if (dropdown) {
        dropdown.classList.toggle('hidden');
    }
}

function applyFilters() {
    const externalId = document.getElementById('dropdown_external_id')?.value || '';
    const workshopOrderId = document.getElementById('dropdown_workshop_order_id')?.value || '';
    const status = document.getElementById('dropdown_status')?.value || '';
    const source = document.getElementById('dropdown_source')?.value || '';
    
    // Update hidden inputs
    document.getElementById('filter_external_id').value = externalId;
    document.getElementById('filter_workshop_order_id').value = workshopOrderId;
    document.getElementById('filter_status').value = status;
    document.getElementById('filter_source').value = source;
    
    // Close dropdown and submit form
    const dropdown = document.getElementById('filtersDropdown');
    if (dropdown) {
        dropdown.classList.add('hidden');
    }
    document.getElementById('searchForm').submit();
}

function clearFilters() {
    document.getElementById('dropdown_external_id').value = '';
    document.getElementById('dropdown_workshop_order_id').value = '';
    document.getElementById('dropdown_status').value = '';
    document.getElementById('dropdown_source').value = '';
    
    // Update hidden inputs
    document.getElementById('filter_external_id').value = '';
    document.getElementById('filter_workshop_order_id').value = '';
    document.getElementById('filter_status').value = '';
    document.getElementById('filter_source').value = '';
    
    // Close dropdown and submit form
    const dropdown = document.getElementById('filtersDropdown');
    if (dropdown) {
        dropdown.classList.add('hidden');
    }
    document.getElementById('searchForm').submit();
}

// Date Range Dropdown Functions
function toggleDateRangeDropdown() {
    const dropdown = document.getElementById('dateRangeDropdown');
    const filtersDropdown = document.getElementById('filtersDropdown');
    
    // Close filters dropdown if open
    if (filtersDropdown) {
        filtersDropdown.classList.add('hidden');
    }
    
    if (dropdown) {
        const isHidden = dropdown.classList.contains('hidden');
        dropdown.classList.toggle('hidden');
        
        // Open flatpickr when dropdown opens
        if (isHidden && dateRangePickerInstance) {
            setTimeout(() => {
                dateRangePickerInstance.open();
            }, 100);
        }
    }
}

function applyDateRange() {
    const fromDateInput = document.getElementById('from_date');
    const toDateInput = document.getElementById('to_date');
    
    if (dateRangePickerInstance && dateRangePickerInstance.selectedDates) {
        const selectedDates = dateRangePickerInstance.selectedDates;
        
        if (selectedDates.length === 2) {
            // Both dates selected - format as Y-m-d
            const formatDate = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };
            fromDateInput.value = formatDate(selectedDates[0]);
            toDateInput.value = formatDate(selectedDates[1]);
        } else if (selectedDates.length === 1) {
            // Only start date selected
            const formatDate = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };
            fromDateInput.value = formatDate(selectedDates[0]);
            toDateInput.value = '';
        } else {
            // No dates selected
            fromDateInput.value = '';
            toDateInput.value = '';
        }
    }
    
    // Close dropdown and submit form
    const dropdown = document.getElementById('dateRangeDropdown');
    if (dropdown) {
        dropdown.classList.add('hidden');
    }
    if (dateRangePickerInstance && dateRangePickerInstance.isOpen) {
        dateRangePickerInstance.close();
    }
    document.getElementById('searchForm').submit();
}

function clearDateRange() {
    const fromDateInput = document.getElementById('from_date');
    const toDateInput = document.getElementById('to_date');
    
    fromDateInput.value = '';
    toDateInput.value = '';
    
    // Clear flatpickr
    if (dateRangePickerInstance) {
        dateRangePickerInstance.clear();
    }
    
    // Close dropdown and submit form
    const dropdown = document.getElementById('dateRangeDropdown');
    if (dropdown) {
        dropdown.classList.add('hidden');
    }
    document.getElementById('searchForm').submit();
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const filtersDropdown = document.getElementById('filtersDropdown');
    const dateRangeDropdown = document.getElementById('dateRangeDropdown');
    
    if (filtersDropdown && !filtersDropdown.contains(event.target) && !event.target.closest('button[onclick="toggleFiltersDropdown()"]')) {
        filtersDropdown.classList.add('hidden');
    }
    
    if (dateRangeDropdown && !dateRangeDropdown.contains(event.target) && !event.target.closest('button[onclick="toggleDateRangeDropdown()"]')) {
        dateRangeDropdown.classList.add('hidden');
    }
});

// Define toggle function globally first
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
    } else {
        console.warn('Details row not found for order:', orderId);
    }
}

// Initialize event listeners when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to all toggle buttons
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

// Select all checkbox
document.getElementById('select-all')?.addEventListener('change', function() {
    document.querySelectorAll('.order-checkbox:not(:disabled)').forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateBulkDeleteButton();
});

// Update bulk delete, export, and submit button visibility based on selected checkboxes
function updateBulkDeleteButton() {
    // Only count enabled checkboxes that are checked
    const checkedBoxes = document.querySelectorAll('.order-checkbox:checked:not(:disabled)');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkDeleteText = document.getElementById('bulkDeleteText');
    const bulkExportBtn = document.getElementById('bulkExportBtn');
    const bulkExportText = document.getElementById('bulkExportText');
    const bulkSubmitBtn = document.getElementById('bulkSubmitBtn');
    const bulkSubmitText = document.getElementById('bulkSubmitText');
    
    if (bulkDeleteBtn) {
        if (checkedBoxes.length > 0) {
            bulkDeleteBtn.classList.remove('hidden');
            bulkDeleteBtn.classList.add('flex');
            bulkDeleteBtn.style.display = 'flex';
            if (bulkDeleteText) {
                bulkDeleteText.textContent = `Delete Selected (${checkedBoxes.length})`;
            }
        } else {
            bulkDeleteBtn.classList.add('hidden');
            bulkDeleteBtn.classList.remove('flex');
            bulkDeleteBtn.style.display = 'none';
            if (bulkDeleteText) {
                bulkDeleteText.textContent = 'Delete Selected';
            }
        }
    }
    
    if (bulkExportBtn) {
        if (checkedBoxes.length > 0) {
            bulkExportBtn.classList.remove('hidden');
            bulkExportBtn.classList.add('flex');
            bulkExportBtn.style.display = 'flex';
            if (bulkExportText) {
                bulkExportText.textContent = `Export Selected (${checkedBoxes.length})`;
            }
        } else {
            bulkExportBtn.classList.add('hidden');
            bulkExportBtn.classList.remove('flex');
            bulkExportBtn.style.display = 'none';
            if (bulkExportText) {
                bulkExportText.textContent = 'Export Selected';
            }
        }
    }

    if (bulkSubmitBtn) {
        if (checkedBoxes.length > 0) {
            bulkSubmitBtn.classList.remove('hidden');
            bulkSubmitBtn.classList.add('flex');
            bulkSubmitBtn.style.display = 'flex';
            if (bulkSubmitText) {
                bulkSubmitText.textContent = `Submit Selected (${checkedBoxes.length})`;
            }
        } else {
            bulkSubmitBtn.classList.add('hidden');
            bulkSubmitBtn.classList.remove('flex');
            bulkSubmitBtn.style.display = 'none';
            if (bulkSubmitText) {
                bulkSubmitText.textContent = 'Submit Selected';
            }
        }
    }
}

// Add event listeners to all checkboxes
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.order-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkDeleteButton);
    });
    updateBulkDeleteButton();
});

// Bulk delete function
function bulkDeleteOrders() {
    // Only get enabled checkboxes that are checked
    const checkedBoxes = document.querySelectorAll('.order-checkbox:checked:not(:disabled)');
    
    if (checkedBoxes.length === 0) {
        alert('Please select at least one order to delete. Note: Only orders with "On Hold" status can be deleted.');
        return;
    }
    
    // Filter out any orders that shouldn't be deleted (for customer, only on_hold)
    const orderIds = Array.from(checkedBoxes)
        .filter(cb => {
            const status = cb.getAttribute('data-status');
            // For customer, only allow on_hold orders (this should already be filtered by disabled attribute, but double-check)
            return !status || status === 'on_hold';
        })
        .map(cb => cb.value);
    
    if (orderIds.length === 0) {
        alert('No valid orders selected for deletion. Only orders with "On Hold" status can be deleted.');
        return;
    }
    
    const count = orderIds.length;
    
    if (!confirm(`Are you sure you want to delete ${count} order(s)? This action cannot be undone.`)) {
        return;
    }
    
    // Set order IDs in hidden input
    document.getElementById('bulkDeleteOrderIds').value = JSON.stringify(orderIds);
    
    // Submit form
    document.getElementById('bulkDeleteForm').submit();
}

// Bulk export function
function bulkExportOrders() {
    const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
    
    if (checkedBoxes.length === 0) {
        alert('Please select at least one order to export.');
        return;
    }
    
    const orderIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    // Set order IDs in hidden input
    document.getElementById('bulkExportOrderIds').value = JSON.stringify(orderIds);
    
    // Submit form
    document.getElementById('bulkExportForm').submit();
}

// Bulk submit functions
function openBulkSubmitModal() {
    const checkedBoxes = document.querySelectorAll('.order-checkbox:checked:not(:disabled)');
    
    if (checkedBoxes.length === 0) {
        alert('Please select at least one order to submit.');
        return;
    }
    
    const orderIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    // Set order IDs in hidden input
    document.getElementById('bulkSubmitOrderIds').value = JSON.stringify(orderIds);
    
    // Update count in modal
    const countElement = document.getElementById('bulkSubmitCount');
    if (countElement) {
        countElement.textContent = checkedBoxes.length;
    }
    
    // Show modal
    const modal = document.getElementById('bulkSubmitModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeBulkSubmitModal() {
    const modal = document.getElementById('bulkSubmitModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

// Close bulk submit modal when clicking outside
document.getElementById('bulkSubmitModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeBulkSubmitModal();
    }
});

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
    } else if (e.key === 'ArrowLeft' && !document.getElementById('imageModal')?.classList.contains('hidden')) {
        previousImage();
    } else if (e.key === 'ArrowRight' && !document.getElementById('imageModal')?.classList.contains('hidden')) {
        nextImage();
    }
});
</script>
@endpush
@endsection

@php
    $activeMenu = 'orders';
@endphp
