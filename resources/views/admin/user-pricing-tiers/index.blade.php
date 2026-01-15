@extends('layouts.admin-dashboard')

@section('title', 'User Pricing Tiers Management - ' . config('app.name', 'Laravel'))

@section('header-title', 'User Pricing Tiers Management')
@section('header-subtitle', 'Quản lý pricing tier của khách hàng')

@section('content')
<div class="space-y-6 w-full max-w-full overflow-x-hidden">
    <!-- Filters Section - Compact -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <form method="GET" action="{{ route('admin.user-pricing-tiers.index') }}" id="filterForm">
            <div class="flex items-center gap-3 p-3">
                <!-- Search -->
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Tên hoặc email..."
                           class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Tier Filter -->
                <div class="w-56">
                    <select name="tier_id" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tất cả tiers</option>
                        @foreach($tiers as $tier)
                            <option value="{{ $tier->id }}" {{ request('tier_id') == $tier->id ? 'selected' : '' }}>
                                {{ $tier->name }} ({{ $tier->slug }})
                            </option>
                        @endforeach
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
                    @if(request()->anyFilled(['search', 'tier_id']))
                        <a href="{{ route('admin.user-pricing-tiers.index') }}" 
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
    @if(request()->anyFilled(['search', 'tier_id']))
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
                @if(request('tier_id'))
                    @php
                        $selectedTier = $tiers->firstWhere('id', request('tier_id'));
                    @endphp
                    @if($selectedTier)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-200 text-blue-800">
                            Tier: {{ $selectedTier->name }}
                            <button onclick="removeFilter('tier_id')" class="ml-1.5 text-blue-600 hover:text-blue-900">×</button>
                        </span>
                    @endif
                @endif
            </div>
            <span class="text-xs text-blue-700 font-medium">{{ $users->total() }} found</span>
        </div>
    </div>
    @endif

    <!-- Users List -->
    @if($users->count() > 0)
    <div class="bg-white shadow-md rounded-xl border border-gray-200 overflow-hidden w-full max-w-full">
        <!-- Header -->
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center space-x-2 text-sm text-gray-600">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span class="font-medium">Users with Pricing Tiers</span>
            </div>
            <div class="text-xs text-gray-500">
                <span class="font-semibold">{{ $users->total() }}</span> users
            </div>
        </div>

        <!-- Users Grid/List -->
        <div class="p-6">
            <div class="grid grid-cols-1 gap-6">
                @foreach($users as $user)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-200 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center justify-between flex-wrap gap-4">
                            <!-- Left: User Info -->
                            <div class="flex items-center gap-4 flex-1 min-w-0">
                                <!-- Avatar -->
                                <div class="relative shrink-0">
                                    <div class="w-14 h-14 rounded-full flex items-center justify-center font-bold text-white text-lg shadow-md" style="background: linear-gradient(135deg, #10B981 0%, #059669 100%);">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    @if($user->pricingTier && $user->pricingTier->pricingTier)
                                        <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full border-2 border-white flex items-center justify-center shadow-sm" style="background-color: #3B82F6;">
                                            <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <!-- User Details -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3 mb-2 flex-wrap">
                                        <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $user->name }}</h3>
                                        @if($user->role)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full whitespace-nowrap" style="background-color: #DBEAFE; color: #2563EB;">
                                                {{ $user->role->name }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-4 text-sm text-gray-500 flex-wrap">
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            <span class="truncate">{{ $user->email }}</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Pricing Tier Info -->
                                    <div class="mt-3 flex items-center gap-3 flex-wrap">
                                        @if($user->pricingTier && $user->pricingTier->pricingTier)
                                            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg" style="background-color: #EFF6FF; border: 1px solid #DBEAFE;">
                                                <svg class="w-4 h-4" style="color: #1E40AF;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span class="text-sm font-semibold" style="color: #1E40AF;">
                                                    {{ $user->pricingTier->pricingTier->name }}
                                                </span>
                                                <span class="text-xs text-gray-500">({{ $user->pricingTier->pricingTier->slug }})</span>
                                            </div>
                                            @if($user->pricingTier->assigned_at)
                                                <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <span>Gán vào: {{ $user->pricingTier->assigned_at->format('d/m/Y H:i') }}</span>
                                                </div>
                                            @endif
                                        @else
                                            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gray-50 border border-gray-200">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span class="text-sm text-gray-500 italic">Chưa có pricing tier</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Actions -->
                            <div class="flex items-center gap-2 shrink-0">
                                <a href="{{ route('admin.user-pricing-tiers.edit', $user) }}" 
                                   class="inline-flex items-center px-5 py-2.5 rounded-lg text-sm font-semibold text-white transition-all shadow-sm"
                                   style="background-color: #10B981;"
                                   onmouseover="this.style.backgroundColor='#059669'; this.style.transform='translateY(-1px)';"
                                   onmouseout="this.style.backgroundColor='#10B981'; this.style.transform='translateY(0)';">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Chỉnh sửa Tier
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
                        <span class="text-gray-600">With Tier: <span class="font-semibold text-gray-900">{{ $users->filter(fn($u) => $u->pricingTier && $u->pricingTier->pricingTier)->count() }}</span></span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 rounded-full bg-gray-500"></div>
                        <span class="text-gray-600">Without Tier: <span class="font-semibold text-gray-900">{{ $users->filter(fn($u) => !$u->pricingTier || !$u->pricingTier->pricingTier)->count() }}</span></span>
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
        <div class="w-20 h-20 bg-gradient-to-br from-green-100 to-emerald-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Không có users nào</h3>
        <p class="text-gray-500 mb-6">Không tìm thấy users nào phù hợp với bộ lọc.</p>
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
    $activeMenu = 'user-pricing-tiers';
@endphp
