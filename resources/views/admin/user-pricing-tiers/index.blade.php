@extends('layouts.admin-dashboard')

@section('title', 'User Pricing Tiers Management - ' . config('app.name', 'Laravel'))

@section('header-title', 'User Pricing Tiers Management')
@section('header-subtitle', 'Quản lý pricing tier của khách hàng')

@section('content')
<div class="space-y-6">
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="GET" action="{{ route('admin.user-pricing-tiers.index') }}" class="flex items-end gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tìm kiếm</label>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Tên hoặc email..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pricing Tier</label>
                <select 
                    name="tier_id" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">Tất cả tiers</option>
                    @foreach($tiers as $tier)
                        <option value="{{ $tier->id }}" {{ request('tier_id') == $tier->id ? 'selected' : '' }}>
                            {{ $tier->name }} ({{ $tier->slug }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button 
                    type="submit"
                    class="px-6 py-2 rounded-lg text-sm font-semibold text-white transition-colors bg-blue-500 hover:bg-blue-600"
                >
                    Lọc
                </button>
            </div>
            @if(request('search') || request('tier_id'))
            <div>
                <a 
                    href="{{ route('admin.user-pricing-tiers.index') }}"
                    class="px-6 py-2 rounded-lg text-sm font-semibold transition-colors border border-gray-300 text-gray-700 hover:bg-gray-100"
                >
                    Xóa bộ lọc
                </a>
            </div>
            @endif
        </form>
    </div>

    <!-- Users List -->
    <div class="space-y-4">
        @forelse($users as $user)
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
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full whitespace-nowrap" style="background-color: #DBEAFE; color: #2563EB;">
                                        {{ $user->role->name }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-4 text-sm text-gray-600 flex-wrap">
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
                        <a 
                            href="{{ route('admin.user-pricing-tiers.edit', $user) }}" 
                            class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white transition-all shadow-sm"
                            style="background-color: #10B981;"
                            onmouseover="this.style.backgroundColor='#059669'; this.style.transform='translateY(-1px)';"
                            onmouseout="this.style.backgroundColor='#10B981'; this.style.transform='translateY(0)';"
                        >
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Chỉnh sửa Tier
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12">
            <div class="text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-semibold text-gray-900">Không có users nào</h3>
                <p class="mt-2 text-sm text-gray-500">Không tìm thấy users nào phù hợp với bộ lọc.</p>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($users->hasPages())
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-6 py-4">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection

@php
    $activeMenu = 'user-pricing-tiers';
@endphp

