@extends('layouts.admin-dashboard')

@section('title', 'Permissions Management - ' . config('app.name', 'Laravel'))

@section('header-title', '🔐 Permissions Management')
@section('header-subtitle', 'Quản lý permissions và gán cho roles')

@section('header-actions')
<a href="{{ route('admin.permissions.create') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm" style="background-color: #2563EB;" onmouseover="this.style.backgroundColor='#1D4ED8';" onmouseout="this.style.backgroundColor='#2563EB';">
    + Thêm Permission
</a>
@endsection

@section('content')
<div class="space-y-6 w-full max-w-full overflow-x-hidden">
    <!-- Filters Section - Compact -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <form method="GET" action="{{ route('admin.permissions.index') }}" id="filterForm">
            <div class="flex items-center gap-3 p-3">
                <!-- Search -->
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Tìm theo tên, slug, mô tả..."
                           class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Group Filter -->
                <div class="w-48">
                    <select name="group" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tất cả nhóm</option>
                        @foreach($groups as $group)
                            <option value="{{ $group }}" {{ request('group') == $group ? 'selected' : '' }}>
                                {{ $group }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Per Page -->
                <div class="w-28">
                    <select name="per_page" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        @foreach([12,25,50,100] as $size)
                            <option value="{{ $size }}" {{ (int)request('per_page', $permissions->perPage() ?? 12) === $size ? 'selected' : '' }}>
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
                    @if(request()->anyFilled(['search', 'group']))
                        <a href="{{ route('admin.permissions.index') }}" 
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
    @if(request()->anyFilled(['search', 'group']))
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
                @if(request('group'))
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-200 text-blue-800">
                        Group: {{ request('group') }}
                        <button onclick="removeFilter('group')" class="ml-1.5 text-blue-600 hover:text-blue-900">×</button>
                    </span>
                @endif
            </div>
            <span class="text-xs text-blue-700 font-medium">{{ $permissions->total() }} found</span>
        </div>
    </div>
    @endif

    <!-- Permissions List -->
    @if($permissions->count() > 0)
    <div class="bg-white shadow-md rounded-xl border border-gray-200 overflow-hidden w-full max-w-full">
        <!-- Scroll Hint -->
        <div class="bg-gradient-to-r from-purple-50 to-indigo-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center space-x-2 text-sm text-gray-600">
                <svg class="w-4 h-4 text-purple-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-medium">⬅️ Drag horizontally to see more columns ➡️</span>
            </div>
            <div class="text-xs text-gray-500">
                <span class="font-semibold">{{ $permissions->total() }}</span> permissions
            </div>
        </div>
        
        <!-- Table Container with Horizontal & Vertical Scroll -->
        <div class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-280px)] scrollbar-custom" 
             id="permissionsTableContainer"
             style="overscroll-behavior: contain;">
            <table class="min-w-[1200px] w-full divide-y divide-gray-200" style="table-layout: auto;">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 sticky top-0 z-10 shadow-sm">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider" style="min-width: 200px;">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                <span>Permission</span>
                            </div>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider" style="min-width: 180px;">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                </svg>
                                <span>Slug</span>
                            </div>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider" style="min-width: 150px;">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                <span>Nhóm</span>
                            </div>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider" style="min-width: 120px;">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <span>Roles</span>
                            </div>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider" style="min-width: 300px;">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span>Mô tả</span>
                            </div>
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider" style="min-width: 200px;">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($permissions as $permission)
                        <tr class="hover:bg-purple-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium" style="color: #111827;">{{ $permission->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <code class="text-xs px-2 py-1 rounded font-mono" style="background-color: #F3F4F6; color: #1F2937;">{{ $permission->slug }}</code>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($permission->group)
                                    <span class="px-2 py-1 text-xs rounded font-medium" style="background-color: #DBEAFE; color: #1E40AF;">
                                        {{ $permission->group }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium" style="color: #6B7280;">
                                    {{ $permission->roles_count }} role(s)
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-600" style="max-width: 400px;">
                                    {{ $permission->description ?: '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.permissions.edit', $permission) }}" 
                                       class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium border transition-all"
                                       style="color: #2563EB; border-color: #DBEAFE; background-color: #EFF6FF;"
                                       onmouseover="this.style.backgroundColor='#DBEAFE';"
                                       onmouseout="this.style.backgroundColor='#EFF6FF';">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Sửa
                                    </a>
                                    <form method="POST" 
                                          action="{{ route('admin.permissions.destroy', $permission) }}" 
                                          class="inline"
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa permission này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium border transition-all"
                                                style="color: #EF4444; border-color: #FEE2E2; background-color: #FEF2F2;"
                                                onmouseover="this.style.backgroundColor='#FEE2E2';"
                                                onmouseout="this.style.backgroundColor='#FEF2F2';">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Xóa
                                        </button>
                                    </form>
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
                        <div class="w-3 h-3 rounded-full bg-purple-500"></div>
                        <span class="text-gray-600">Total Groups: <span class="font-semibold text-gray-900">{{ $groups->count() }}</span></span>
                    </div>
                </div>
                <div class="text-sm text-gray-600">
                    Total: <span class="font-bold text-gray-900">{{ $permissions->total() }}</span> permissions
                </div>
            </div>
        </div>
        
        @if($permissions->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $permissions->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
    @else
    <!-- Empty State -->
    <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
        <div class="w-20 h-20 bg-gradient-to-br from-purple-100 to-indigo-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-12 h-12 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Không tìm thấy permissions nào</h3>
        <p class="text-gray-500 mb-6">Không tìm thấy permissions nào phù hợp với bộ lọc.</p>
    </div>
    @endif

    <!-- Role Permissions Management -->
    <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
        <h3 class="text-lg font-semibold mb-4" style="color: #111827;">⚙️ Quản lý Permissions cho Roles</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($roles as $role)
                <div class="p-4 rounded-lg border hover:shadow-md transition-shadow" style="border-color: #E5E7EB; background-color: #F9FAFB;">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium" style="color: #111827;">{{ $role->name }}</h4>
                        <span class="text-xs px-2 py-1 rounded" style="background-color: #DBEAFE; color: #1E40AF;">
                            {{ $role->slug }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-3">{{ $role->description ?: 'Không có mô tả' }}</p>
                    <a href="{{ route('admin.roles.permissions', $role) }}" 
                       class="block w-full text-center px-4 py-2 rounded-lg text-sm font-medium text-white transition-all"
                       style="background-color: #2563EB;"
                       onmouseover="this.style.backgroundColor='#1D4ED8';"
                       onmouseout="this.style.backgroundColor='#2563EB';">
                        🔐 Quản lý Permissions
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>

<style>
/* Custom Scrollbar Styles - Always Visible */
.scrollbar-custom {
    scrollbar-width: thin;
    scrollbar-color: #a855f7 #e5e7eb;
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
    background: linear-gradient(to right, #a855f7, #8b5cf6);
    border-radius: 10px;
    border: 2px solid #e5e7eb;
    min-width: 40px;
}

.scrollbar-custom::-webkit-scrollbar-thumb:vertical {
    background: linear-gradient(to bottom, #a855f7, #8b5cf6);
    border-radius: 10px;
    border: 2px solid #e5e7eb;
}

.scrollbar-custom::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to right, #9333ea, #7c3aed);
    box-shadow: 0 0 6px rgba(168, 85, 247, 0.5);
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
    const tableContainer = document.getElementById('permissionsTableContainer');
    
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
    $activeMenu = 'permissions';
@endphp
