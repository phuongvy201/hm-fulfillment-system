@extends('layouts.admin-dashboard') 

@section('title', 'Wallet Management - ' . config('app.name', 'Laravel'))

@section('header-title', 'Wallet Management')
@section('header-subtitle', 'View and manage customer wallets')

@section('content')
<div class="space-y-6 w-full max-w-full overflow-x-hidden">
    <!-- Filters Section - Compact -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <form method="GET" action="{{ route('admin.wallets.index') }}" id="filterForm">
            <div class="flex items-center gap-3 p-3">
                <!-- Search -->
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search by name or email..."
                           class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Per Page -->
                <div class="w-28">
                    <select name="per_page" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        @foreach([12,25,50,100] as $size)
                            <option value="{{ $size }}" {{ (int)request('per_page', $users->perPage() ?? 12) === $size ? 'selected' : '' }}>
                                {{ $size }}/page
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Buttons -->
                <div class="flex items-center gap-2">
                    <button type="submit" 
                            class="inline-flex items-center px-3 py-1.5 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Search
                    </button>
                    @if(request('search'))
                        <a href="{{ route('admin.wallets.index') }}" 
                           class="inline-flex items-center px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Clear
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Active Filters Display - Compact -->
    @if(request('search'))
    <div class="bg-blue-50 border border-blue-200 rounded-lg px-3 py-2">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center flex-wrap gap-2">
                <span class="text-xs font-semibold text-blue-900">Active:</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-200 text-blue-800">
                    Search: "{{ Str::limit(request('search'), 20) }}"
                    <button onclick="removeFilter('search')" class="ml-1.5 text-blue-600 hover:text-blue-900">×</button>
                </span>
            </div>
            <span class="text-xs text-blue-700 font-medium">{{ $users->total() }} found</span>
        </div>
    </div>
    @endif

    <!-- Wallets Table -->
    @if($users->count() > 0)
    <div class="bg-white shadow-md rounded-xl border border-gray-200 overflow-hidden w-full max-w-full">
        <!-- Scroll Hint -->
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center space-x-2 text-sm text-gray-600">
                <svg class="w-4 h-4 text-green-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-medium">⬅️ Drag horizontally to see more columns ➡️</span>
            </div>
            <div class="text-xs text-gray-500">
                <span class="font-semibold">{{ $users->total() }}</span> customers
            </div>
        </div>
        
        <!-- Table Container with Horizontal & Vertical Scroll -->
        <div class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-280px)] scrollbar-custom" 
             id="walletsTableContainer"
             style="overscroll-behavior: contain;">
            <table class="min-w-[1400px] w-full divide-y divide-gray-200" style="table-layout: auto;">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 sticky top-0 z-10 shadow-sm">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider" style="min-width: 250px;">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span>Customer</span>
                            </div>
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider" style="min-width: 160px;">
                            <div class="flex items-center justify-end space-x-2">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                                <span>Available Balance</span>
                            </div>
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider" style="min-width: 140px;">
                            <div class="flex items-center justify-end space-x-2">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                <span>Credit Limit</span>
                            </div>
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider" style="min-width: 140px;">
                            <div class="flex items-center justify-end space-x-2">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                                <span>Current Debt</span>
                            </div>
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider" style="min-width: 140px;">
                            <div class="flex items-center justify-end space-x-2">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                <span>Remaining Credit</span>
                            </div>
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider" style="min-width: 180px;">
                            <div class="flex items-center justify-end space-x-2">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                <span>Total Payment Capacity</span>
                            </div>
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider" style="min-width: 150px;">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $user)
                    <tr class="hover:bg-green-50 transition-colors duration-150">
                        <!-- Customer -->
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white text-sm shadow-md bg-gradient-to-br from-green-500 to-green-600">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0 flex-1" style="max-width: 200px;">
                                    <p class="text-sm font-bold text-gray-900 truncate" title="{{ $user->name }}">
                                        {{ $user->name }}
                                    </p>
                                    <p class="text-xs text-gray-500 truncate" title="{{ $user->email }}">
                                        {{ $user->email }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        
                        <!-- Available Balance -->
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <div class="text-lg font-bold text-green-600">
                                ${{ number_format($user->available_balance, 2) }}
                            </div>
                            <div class="text-xs text-gray-500">USD</div>
                        </td>
                        
                        <!-- Credit Limit -->
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <div class="text-lg font-bold text-blue-600">
                                ${{ number_format($user->credit_limit, 2) }}
                            </div>
                            <div class="text-xs text-gray-500">USD</div>
                        </td>
                        
                        <!-- Current Debt -->
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <div class="text-lg font-bold {{ $user->current_debt > 0 ? 'text-red-600' : 'text-gray-600' }}">
                                ${{ number_format($user->current_debt, 2) }}
                            </div>
                            <div class="text-xs text-gray-500">USD</div>
                        </td>
                        
                        <!-- Remaining Credit -->
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <div class="text-lg font-bold text-indigo-600">
                                ${{ number_format($user->remaining_credit, 2) }}
                            </div>
                            <div class="text-xs text-gray-500">USD</div>
                        </td>
                        
                        <!-- Total Payment Capacity -->
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <div class="text-lg font-bold text-purple-600">
                                ${{ number_format($user->total_payment_capacity, 2) }}
                            </div>
                            <div class="text-xs text-gray-500">USD</div>
                        </td>
                        
                        <!-- Actions -->
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="{{ route('admin.wallets.show', $user) }}" 
                                   class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white transition-colors bg-blue-600 hover:bg-blue-700 rounded-lg"
                                   title="View Details">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    View
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Table Footer with Stats -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-6 text-sm">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                        <span class="text-gray-600">Total Balance: <span class="font-semibold text-gray-900">${{ number_format($users->sum('available_balance'), 2) }}</span></span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                        <span class="text-gray-600">Total Debt: <span class="font-semibold text-gray-900">${{ number_format($users->sum('current_debt'), 2) }}</span></span>
                    </div>
                </div>
                <div class="text-sm text-gray-600">
                    Total: <span class="font-bold text-gray-900">{{ $users->total() }}</span> customers
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- Empty State -->
    <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
        <div class="w-20 h-20 bg-gradient-to-br from-green-100 to-emerald-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No customers found</h3>
        <p class="text-gray-500 mb-6">Get started by viewing customer wallets.</p>
    </div>
    @endif

    <!-- Pagination -->
    @if($users->hasPages())
    <div class="bg-white px-6 py-4 flex items-center justify-between border-t border-gray-200 rounded-b-xl shadow-md">
        <div class="flex-1 flex justify-between sm:hidden">
            @if($users->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-500 bg-white cursor-not-allowed">
                    Previous
                </span>
            @else
                <a href="{{ $users->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    Previous
                </a>
            @endif

            @if($users->hasMorePages())
                <a href="{{ $users->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    Next
                </a>
            @else
                <span class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-500 bg-white cursor-not-allowed">
                    Next
                </span>
            @endif
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-600">
                    Showing
                    <span class="font-semibold text-gray-900">{{ $users->firstItem() }}</span>
                    to
                    <span class="font-semibold text-gray-900">{{ $users->lastItem() }}</span>
                    of
                    <span class="font-semibold text-gray-900">{{ $users->total() }}</span>
                    results
                </p>
            </div>
            <div>
                {{ $users->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

<style>
/* Custom Scrollbar Styles - Always Visible */
.scrollbar-custom {
    scrollbar-width: thin;
    scrollbar-color: #10b981 #e5e7eb;
}

/* Webkit browsers (Chrome, Safari, Edge) */
.scrollbar-custom::-webkit-scrollbar {
    width: 12px;
    height: 12px;
}

.scrollbar-custom::-webkit-scrollbar-track {
    background: #e5e7eb;
    border-radius: 10px;
    margin: 4px;
}

.scrollbar-custom::-webkit-scrollbar-thumb:horizontal {
    background: linear-gradient(to right, #10b981, #14b8a6);
    border-radius: 10px;
    border: 2px solid #e5e7eb;
    min-width: 40px;
}

.scrollbar-custom::-webkit-scrollbar-thumb:vertical {
    background: linear-gradient(to bottom, #10b981, #14b8a6);
    border-radius: 10px;
    border: 2px solid #e5e7eb;
}

.scrollbar-custom::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to right, #059669, #0d9488);
    box-shadow: 0 0 6px rgba(16, 185, 129, 0.5);
}

.scrollbar-custom::-webkit-scrollbar-corner {
    background: #e5e7eb;
    border-radius: 4px;
}

.scrollbar-custom::-webkit-scrollbar-thumb {
    visibility: visible;
}

.scrollbar-custom {
    scrollbar-width: auto;
}
</style>

<script>
// Prevent page scroll when scrolling table
document.addEventListener('DOMContentLoaded', function() {
    const tableContainer = document.getElementById('walletsTableContainer');
    
    if (tableContainer) {
        tableContainer.addEventListener('wheel', function(e) {
            const isScrollable = tableContainer.scrollHeight > tableContainer.clientHeight || 
                                tableContainer.scrollWidth > tableContainer.clientWidth;
            
            if (isScrollable) {
                e.stopPropagation();
            }
        }, { passive: false });
    }
});

// Filter Functions
function removeFilter(filterName) {
    const url = new URL(window.location.href);
    url.searchParams.delete(filterName);
    window.location.href = url.toString();
}
</script>
@endsection

@php
    $activeMenu = 'wallets';
@endphp
