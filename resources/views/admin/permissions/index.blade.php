@extends('layouts.app')

@section('title', 'Permissions Management - ' . config('app.name', 'Laravel'))

@section('header-title', '🔐 Permissions Management')
@section('header-subtitle', 'Quản lý permissions và gán cho roles')

@section('header-actions')
<a href="{{ route('admin.permissions.create') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm" style="background-color: #2563EB;" onmouseover="this.style.backgroundColor='#1D4ED8';" onmouseout="this.style.backgroundColor='#2563EB';">
    + Thêm Permission
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="GET" action="{{ route('admin.permissions.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium mb-2" style="color: #111827;">Tìm kiếm:</label>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Tìm theo tên, slug, mô tả..."
                    class="w-full px-4 py-2 border rounded-lg text-sm"
                    style="border-color: #D1D5DB;"
                >
            </div>
            <div class="min-w-[200px]">
                <label class="block text-sm font-medium mb-2" style="color: #111827;">Nhóm:</label>
                <select 
                    name="group" 
                    class="w-full px-4 py-2 border rounded-lg text-sm"
                    style="border-color: #D1D5DB;"
                >
                    <option value="">Tất cả nhóm</option>
                    @foreach($groups as $group)
                        <option value="{{ $group }}" {{ request('group') == $group ? 'selected' : '' }}>
                            {{ $group }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button 
                    type="submit" 
                    class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all"
                    style="background-color: #10B981;"
                    onmouseover="this.style.backgroundColor='#059669';"
                    onmouseout="this.style.backgroundColor='#10B981';"
                >
                    🔍 Tìm kiếm
                </button>
                <a 
                    href="{{ route('admin.permissions.index') }}" 
                    class="px-4 py-2 rounded-lg text-sm font-medium border transition-all"
                    style="color: #6B7280; border-color: #D1D5DB;"
                    onmouseover="this.style.backgroundColor='#F3F4F6';"
                    onmouseout="this.style.backgroundColor='transparent';"
                >
                    🔄 Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Permissions List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead style="background-color: #F9FAFB;">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color: #6B7280;">Permission</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color: #6B7280;">Slug</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color: #6B7280;">Nhóm</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color: #6B7280;">Roles</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider" style="color: #6B7280;">Mô tả</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider" style="color: #6B7280;">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: #E5E7EB;">
                    @forelse($permissions as $permission)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium" style="color: #111827;">{{ $permission->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <code class="text-xs px-2 py-1 rounded" style="background-color: #F3F4F6; color: #1F2937;">{{ $permission->slug }}</code>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($permission->group)
                                    <span class="px-2 py-1 text-xs rounded" style="background-color: #DBEAFE; color: #1E40AF;">
                                        {{ $permission->group }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm" style="color: #6B7280;">
                                    {{ $permission->roles_count }} role(s)
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-600 max-w-md">
                                    {{ $permission->description ?: '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <a 
                                        href="{{ route('admin.permissions.edit', $permission) }}" 
                                        class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-all"
                                        style="color: #2563EB; border-color: #DBEAFE;"
                                        onmouseover="this.style.backgroundColor='#EFF6FF';"
                                        onmouseout="this.style.backgroundColor='transparent';"
                                    >
                                        ✏️ Sửa
                                    </a>
                                    <form 
                                        method="POST" 
                                        action="{{ route('admin.permissions.destroy', $permission) }}" 
                                        class="inline"
                                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa permission này?');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit" 
                                            class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-all"
                                            style="color: #EF4444; border-color: #FEE2E2;"
                                            onmouseover="this.style.backgroundColor='#FEE2E2';"
                                            onmouseout="this.style.backgroundColor='transparent';"
                                        >
                                            🗑️ Xóa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-gray-400">
                                    <svg class="mx-auto h-12 w-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p class="text-sm">Không tìm thấy permissions nào.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($permissions->hasPages())
            <div class="px-6 py-4 border-t" style="border-color: #E5E7EB;">
                {{ $permissions->links() }}
            </div>
        @endif
    </div>

    <!-- Role Permissions Management -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold mb-4" style="color: #111827;">⚙️ Quản lý Permissions cho Roles</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($roles as $role)
                <div class="p-4 rounded-lg border" style="border-color: #E5E7EB; background-color: #F9FAFB;">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium" style="color: #111827;">{{ $role->name }}</h4>
                        <span class="text-xs px-2 py-1 rounded" style="background-color: #DBEAFE; color: #1E40AF;">
                            {{ $role->slug }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-3">{{ $role->description ?: 'Không có mô tả' }}</p>
                    <a 
                        href="{{ route('admin.roles.permissions', $role) }}" 
                        class="block w-full text-center px-4 py-2 rounded-lg text-sm font-medium text-white transition-all"
                        style="background-color: #2563EB;"
                        onmouseover="this.style.backgroundColor='#1D4ED8';"
                        onmouseout="this.style.backgroundColor='#2563EB';"
                    >
                        🔐 Quản lý Permissions
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@php
    $activeMenu = 'permissions';
@endphp



































