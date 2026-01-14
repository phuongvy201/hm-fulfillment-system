@extends('layouts.admin-dashboard') 

@section('title', 'Credit Management - ' . config('app.name', 'Laravel'))

@section('header-title', 'Credit Management')
@section('header-subtitle', 'Quản lý công nợ của khách hàng')

@section('content')
<div class="space-y-6">
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="GET" action="{{ route('admin.credits.index') }}" class="flex items-end gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Credit Status</label>
                <select name="enabled" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Tất cả</option>
                    <option value="1" {{ request('enabled') == '1' ? 'selected' : '' }}>Đã bật credit</option>
                    <option value="0" {{ request('enabled') == '0' ? 'selected' : '' }}>Chưa bật credit</option>
                </select>
            </div>
            <div>
                <button type="submit" class="px-6 py-2 rounded-lg text-sm font-semibold text-white transition-colors bg-blue-500 hover:bg-blue-600">
                    Lọc
                </button>
            </div>
            @if(request('enabled') !== '')
            <div>
                <a href="{{ route('admin.credits.index') }}" class="px-6 py-2 rounded-lg text-sm font-semibold transition-colors border border-gray-300 text-gray-700 hover:bg-gray-100">
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
                    <div class="flex items-center gap-4 flex-1 min-w-0">
                        <div class="w-14 h-14 rounded-full flex items-center justify-center font-bold text-white text-lg shadow-md bg-gradient-to-br from-purple-500 to-purple-600">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
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
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="truncate">{{ $user->email }}</span>
                                </div>
                            </div>
                            
                            <!-- Credit Info -->
                            <div class="mt-3 flex items-center gap-4 flex-wrap">
                                @if($user->credit && $user->credit->enabled)
                                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-purple-50 border border-purple-200">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-sm font-semibold text-purple-900">Credit: {{ number_format($user->credit->current_credit, 2) }} / {{ number_format($user->credit->credit_limit, 2) }}</span>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Còn lại: {{ number_format($user->credit->available_credit, 2) }}
                                    </div>
                                @else
                                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gray-50 border border-gray-200">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-sm text-gray-500 italic">Chưa có credit</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a 
                            href="{{ route('admin.credits.edit', $user) }}" 
                            class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white transition-all shadow-sm"
                            style="background-color: #8B5CF6;"
                            onmouseover="this.style.backgroundColor='#7C3AED'; this.style.transform='translateY(-1px)';"
                            onmouseout="this.style.backgroundColor='#8B5CF6'; this.style.transform='translateY(0)';"
                        >
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Quản lý Credit
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
    $activeMenu = 'credits';
@endphp






































