@extends('layouts.admin-dashboard') 

@section('title', 'Credit Management - ' . config('app.name', 'Laravel'))

@section('header-title', 'Credit Management')
@section('header-subtitle', 'Quản lý công nợ của khách hàng')

@section('content')
<div class="space-y-6 w-full max-w-full overflow-x-hidden">
    <!-- Filters Section - Compact -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <form method="GET" action="{{ route('admin.credits.index') }}" id="filterForm">
            <div class="flex items-center gap-3 p-3">
                <!-- Search -->
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search by name or email..."
                           class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Credit Status -->
                <div class="w-48">
                    <select name="enabled" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Status</option>
                        <option value="1" {{ request('enabled') == '1' ? 'selected' : '' }}>Credit Enabled</option>
                        <option value="0" {{ request('enabled') == '0' ? 'selected' : '' }}>Credit Disabled</option>
                    </select>
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
                        Filter
                    </button>
                    @if(request()->anyFilled(['enabled', 'search']))
                        <a href="{{ route('admin.credits.index') }}" 
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
    @if(request()->anyFilled(['enabled', 'search']))
    <div class="bg-blue-50 border border-blue-200 rounded-lg px-3 py-2">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center flex-wrap gap-2">
                <span class="text-xs font-semibold text-blue-900">Active:</span>
                @if(request('search'))
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-200 text-blue-800">
                        Search: "{{ Str::limit(request('search'), 20) }}"
                        <button onclick="removeFilter('search')" class="ml-1.5 text-blue-600 hover:text-blue-900">×</button>
                    </span>
                @endif
                @if(request('enabled') !== '')
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-200 text-blue-800">
                        Status: {{ request('enabled') == '1' ? 'Enabled' : 'Disabled' }}
                        <button onclick="removeFilter('enabled')" class="ml-1.5 text-blue-600 hover:text-blue-900">×</button>
                    </span>
                @endif
            </div>
            <span class="text-xs text-blue-700 font-medium">{{ $users->total() }} found</span>
        </div>
    </div>
    @endif

    <!-- Credits Table -->
    @if($users->count() > 0)
    <div class="bg-white shadow-md rounded-xl border border-gray-200 overflow-hidden w-full max-w-full">
        <!-- Scroll Hint -->
        <div class="bg-gradient-to-r from-purple-50 to-blue-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center space-x-2 text-sm text-gray-600">
                <svg class="w-4 h-4 text-purple-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-medium">⬅️ Drag horizontally to see more columns ➡️</span>
            </div>
            <div class="text-xs text-gray-500">
                <span class="font-semibold">{{ $users->total() }}</span> users
            </div>
        </div>
        
        <!-- Table Container with Horizontal & Vertical Scroll -->
        <div class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-280px)] scrollbar-custom" 
             id="creditsTableContainer"
             style="overscroll-behavior: contain;">
            <table class="min-w-[1200px] w-full divide-y divide-gray-200" style="table-layout: auto;">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 sticky top-0 z-10 shadow-sm">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider" style="min-width: 250px;">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span>Customer</span>
                            </div>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider" style="min-width: 150px;">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Credit Status</span>
                            </div>
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider" style="min-width: 140px;">
                            <div class="flex items-center justify-end space-x-2">
                                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                                <span>Current Credit</span>
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
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                <span>Available Credit</span>
                            </div>
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider" style="min-width: 150px;">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $user)
                    <tr class="hover:bg-purple-50 transition-colors duration-150">
                        <!-- Customer -->
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white text-sm shadow-md bg-gradient-to-br from-purple-500 to-purple-600">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0 flex-1" style="max-width: 200px;">
                                    <p class="text-sm font-bold text-gray-900 truncate" title="{{ $user->name }}">
                                        {{ $user->name }}
                                    </p>
                                    <p class="text-xs text-gray-500 truncate" title="{{ $user->email }}">
                                        {{ $user->email }}
                                    </p>
                                    @if($user->role)
                                        <span class="inline-flex items-center px-2 py-0.5 mt-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $user->role->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        
                        <!-- Credit Status -->
                        <td class="px-6 py-4">
                            @if($user->credit && $user->credit->enabled)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase bg-green-100 text-green-800 border border-green-300">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Enabled
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase bg-gray-100 text-gray-800 border border-gray-300">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    Disabled
                                </span>
                            @endif
                        </td>
                        
                        <!-- Current Credit -->
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            @if($user->credit && $user->credit->enabled)
                                <div class="text-lg font-bold text-orange-600">
                                    ${{ number_format($user->credit->current_credit, 2) }}
                                </div>
                            @else
                                <span class="text-sm text-gray-400 italic">-</span>
                            @endif
                        </td>
                        
                        <!-- Credit Limit -->
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            @if($user->credit && $user->credit->enabled)
                                <div class="text-lg font-bold text-blue-600">
                                    ${{ number_format($user->credit->credit_limit, 2) }}
                                </div>
                            @else
                                <span class="text-sm text-gray-400 italic">-</span>
                            @endif
                        </td>
                        
                        <!-- Available Credit -->
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            @if($user->credit && $user->credit->enabled)
                                <div class="text-lg font-bold text-emerald-600">
                                    ${{ number_format($user->credit->available_credit, 2) }}
                                </div>
                            @else
                                <span class="text-sm text-gray-400 italic">-</span>
                            @endif
                        </td>
                        
                        <!-- Actions -->
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="{{ route('admin.credits.edit', $user) }}" 
                                   class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white transition-colors bg-purple-600 hover:bg-purple-700 rounded-lg"
                                   title="Manage Credit">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Manage
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
                        <span class="text-gray-600">Enabled: <span class="font-semibold text-gray-900">{{ $users->where('credit.enabled', true)->count() }}</span></span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 rounded-full bg-gray-500"></div>
                        <span class="text-gray-600">Disabled: <span class="font-semibold text-gray-900">{{ $users->where('credit.enabled', false)->count() }}</span></span>
                    </div>
                </div>
                <div class="text-sm text-gray-600">
                    Total: <span class="font-bold text-gray-900">{{ $users->total() }}</span> users
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- Empty State -->
    <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
        <div class="w-20 h-20 bg-gradient-to-br from-purple-100 to-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-12 h-12 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No users found</h3>
        <p class="text-gray-500 mb-6">Get started by managing credit for users.</p>
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
    scrollbar-color: #8B5CF6 #e5e7eb;
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
    background: linear-gradient(to right, #8B5CF6, #7C3AED);
    border-radius: 10px;
    border: 2px solid #e5e7eb;
    min-width: 40px;
}

.scrollbar-custom::-webkit-scrollbar-thumb:vertical {
    background: linear-gradient(to bottom, #8B5CF6, #7C3AED);
    border-radius: 10px;
    border: 2px solid #e5e7eb;
}

.scrollbar-custom::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to right, #7C3AED, #6D28D9);
    box-shadow: 0 0 6px rgba(139, 92, 246, 0.5);
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
    const tableContainer = document.getElementById('creditsTableContainer');
    
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
    $activeMenu = 'credits';
@endphp
