@extends('layouts.admin-dashboard')

@section('title', 'Pricing Tiers Management - ' . config('app.name', 'Laravel'))

@section('header-title', 'Pricing Tiers Management')
@section('header-subtitle', 'Manage pricing tiers (wood, silver, gold, diamond, etc.)')

@section('header-actions')
<a href="{{ route('admin.pricing-tiers.create') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm" style="background-color: #2563EB;" onmouseover="this.style.backgroundColor='#1D4ED8';" onmouseout="this.style.backgroundColor='#2563EB';">
    + Add Pricing Tier
</a>
@endsection

@section('content')
<div class="space-y-6 w-full max-w-full overflow-x-hidden">
    <!-- Filters Section - Compact -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <form method="GET" action="{{ route('admin.pricing-tiers.index') }}" id="filterForm">
            <div class="flex items-center gap-3 p-3">
                <!-- Search -->
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search by name or slug..."
                           class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Status Filter -->
                <div class="w-40">
                    <select name="status" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Auto Assign Filter -->
                <div class="w-48">
                    <select name="auto_assign" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Types</option>
                        <option value="1" {{ request('auto_assign') == '1' ? 'selected' : '' }}>Auto Assign</option>
                        <option value="0" {{ request('auto_assign') == '0' ? 'selected' : '' }}>Manual Assign</option>
                    </select>
                </div>

                <!-- Per Page -->
                <div class="w-28">
                    <select name="per_page" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        @foreach([12,25,50,100] as $size)
                            <option value="{{ $size }}" {{ (int)request('per_page', $tiers->perPage() ?? 12) === $size ? 'selected' : '' }}>
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
                    @if(request()->anyFilled(['status', 'search', 'auto_assign']))
                        <a href="{{ route('admin.pricing-tiers.index') }}" 
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
    @if(request()->anyFilled(['status', 'search', 'auto_assign']))
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
                @if(request('status'))
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-200 text-blue-800">
                        Status: {{ ucfirst(request('status')) }}
                        <button onclick="removeFilter('status')" class="ml-1.5 text-blue-600 hover:text-blue-900">×</button>
                    </span>
                @endif
                @if(request('auto_assign') !== '')
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-200 text-blue-800">
                        Type: {{ request('auto_assign') == '1' ? 'Auto Assign' : 'Manual Assign' }}
                        <button onclick="removeFilter('auto_assign')" class="ml-1.5 text-blue-600 hover:text-blue-900">×</button>
                    </span>
                @endif
            </div>
            <span class="text-xs text-blue-700 font-medium">{{ $tiers->total() }} found</span>
        </div>
    </div>
    @endif

    <!-- Pricing Tiers List -->
    @if($tiers->count() > 0)
    <div class="bg-white shadow-md rounded-xl border border-gray-200 overflow-hidden w-full max-w-full">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center space-x-2 text-sm text-gray-600">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
                <span class="font-medium">Pricing Tiers Overview</span>
            </div>
            <div class="text-xs text-gray-500">
                <span class="font-semibold">{{ $tiers->total() }}</span> tiers
            </div>
        </div>

        <!-- Pricing Tiers Grid/List -->
        <div class="p-6">
            <div class="grid grid-cols-1 gap-6">
                @foreach($tiers as $tier)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-200 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center justify-between flex-wrap gap-4">
                            <!-- Left: Tier Info -->
                            <div class="flex items-center gap-4 flex-1 min-w-0">
                                <!-- Priority Badge -->
                                <div class="w-14 h-14 rounded-xl flex items-center justify-center font-bold text-white text-lg shadow-md shrink-0" style="background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);">
                                    {{ $tier->priority }}
                                </div>

                                <!-- Tier Details -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3 mb-2 flex-wrap">
                                        <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $tier->name }}</h3>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full whitespace-nowrap" style="background-color: #DBEAFE; color: #1E40AF;">
                                            {{ $tier->slug }}
                                        </span>
                                        @if($tier->status === 'active')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full whitespace-nowrap" style="background-color: #D1FAE5; color: #065F46;">
                                                Active
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full whitespace-nowrap" style="background-color: #FEE2E2; color: #991B1B;">
                                                Inactive
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-4 text-sm text-gray-500 flex-wrap">
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            <span>{{ $tier->user_pricing_tiers_count }} {{ $tier->user_pricing_tiers_count === 1 ? 'user' : 'users' }}</span>
                                        </div>
                                        @if($tier->auto_assign && $tier->min_orders !== null)
                                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold whitespace-nowrap" style="background-color: #DBEAFE; color: #1E40AF;">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                ≥ {{ number_format($tier->min_orders) }} đơn/tháng
                                            </span>
                                        @elseif(!$tier->auto_assign)
                                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold whitespace-nowrap" style="background-color: #F3E8FF; color: #7C3AED;">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Gán thủ công
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold whitespace-nowrap" style="background-color: #FEF3C7; color: #92400E;">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                                Mặc định
                                            </span>
                                        @endif
                                        @if($tier->reset_period && $tier->reset_period !== 'never')
                                            <div class="flex items-center gap-1.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span class="text-xs">
                                                    Reset: {{ $tier->reset_period === 'monthly' ? 'Hàng tháng' : ($tier->reset_period === 'quarterly' ? 'Hàng quý' : 'Hàng năm') }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Actions -->
                            <div class="flex items-center gap-2 shrink-0">
                                <a href="{{ route('admin.pricing-tiers.show', $tier) }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all border" style="color: #2563EB; border-color: #DBEAFE; background-color: #EFF6FF;" onmouseover="this.style.backgroundColor='#DBEAFE'; this.style.borderColor='#2563EB';" onmouseout="this.style.backgroundColor='#EFF6FF'; this.style.borderColor='#DBEAFE';">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    View
                                </a>
                                <a href="{{ route('admin.pricing-tiers.edit', $tier) }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all border" style="color: #2563EB; border-color: #DBEAFE; background-color: #EFF6FF;" onmouseover="this.style.backgroundColor='#DBEAFE'; this.style.borderColor='#2563EB';" onmouseout="this.style.backgroundColor='#EFF6FF'; this.style.borderColor='#DBEAFE';">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Edit
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        
        <!-- Table Footer with Stats -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-6 text-sm">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                        <span class="text-gray-600">Active: <span class="font-semibold text-gray-900">{{ $tiers->where('status', 'active')->count() }}</span></span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 rounded-full bg-gray-500"></div>
                        <span class="text-gray-600">Inactive: <span class="font-semibold text-gray-900">{{ $tiers->where('status', 'inactive')->count() }}</span></span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                        <span class="text-gray-600">Total Users: <span class="font-semibold text-gray-900">{{ $tiers->sum('user_pricing_tiers_count') }}</span></span>
                    </div>
                </div>
                <div class="text-sm text-gray-600">
                    Total: <span class="font-bold text-gray-900">{{ $tiers->total() }}</span> tiers
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- Empty State -->
    <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
        <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No pricing tiers found</h3>
        <p class="text-gray-500 mb-6">Get started by creating a new pricing tier.</p>
        <a href="{{ route('admin.pricing-tiers.create') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add First Tier
        </a>
    </div>
    @endif

    <!-- Pagination -->
    @if($tiers->hasPages())
    <div class="bg-white px-6 py-4 flex items-center justify-between border-t border-gray-200 rounded-b-xl shadow-md">
        <div class="flex-1 flex justify-between sm:hidden">
            @if($tiers->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-500 bg-white cursor-not-allowed">
                    Previous
                </span>
            @else
                <a href="{{ $tiers->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    Previous
                </a>
            @endif

            @if($tiers->hasMorePages())
                <a href="{{ $tiers->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">
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
                    <span class="font-semibold text-gray-900">{{ $tiers->firstItem() }}</span>
                    to
                    <span class="font-semibold text-gray-900">{{ $tiers->lastItem() }}</span>
                    of
                    <span class="font-semibold text-gray-900">{{ $tiers->total() }}</span>
                    results
                </p>
            </div>
            <div>
                {{ $tiers->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

<script>
// Filter Functions
function removeFilter(filterName) {
    const url = new URL(window.location.href);
    url.searchParams.delete(filterName);
    window.location.href = url.toString();
}
</script>
@endsection

@php
    $activeMenu = 'pricing-tiers';
@endphp
