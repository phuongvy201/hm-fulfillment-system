@extends('layouts.app')

@section('title', 'Manage Role Permissions - ' . config('app.name', 'Laravel'))

@section('header-title', '🔐 Quản lý Permissions cho Role')
@section('header-subtitle', 'Gán permissions cho role: ' . $role->name)

@section('header-actions')
<a href="{{ route('admin.permissions.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
    ← Quay lại
</a>
@endsection

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <form method="POST" action="{{ route('admin.roles.permissions.assign', $role) }}" id="permissionForm">
            @csrf
            <div class="p-6">
                <!-- Role Info -->
                <div class="mb-6 p-4 rounded-lg" style="background-color: #EFF6FF; border: 1px solid #DBEAFE;">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-xl flex items-center justify-center font-bold text-white" style="background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);">
                            {{ strtoupper(substr($role->name, 0, 2)) }}
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold" style="color: #111827;">{{ $role->name }}</h3>
                            <p class="text-sm text-gray-600">{{ $role->slug }}</p>
                            @if($role->description)
                                <p class="text-sm text-gray-500 mt-1">{{ $role->description }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mb-6 flex flex-wrap gap-2">
                    <button 
                        type="button" 
                        onclick="selectAll()" 
                        class="px-4 py-2 rounded-lg text-sm font-medium border transition-all"
                        style="color: #10B981; border-color: #D1FAE5; background-color: #ECFDF5;"
                        onmouseover="this.style.backgroundColor='#D1FAE5';"
                        onmouseout="this.style.backgroundColor='#ECFDF5';"
                    >
                        ✓ Chọn tất cả
                    </button>
                    <button 
                        type="button" 
                        onclick="deselectAll()" 
                        class="px-4 py-2 rounded-lg text-sm font-medium border transition-all"
                        style="color: #6B7280; border-color: #D1D5DB; background-color: #FFFFFF;"
                        onmouseover="this.style.backgroundColor='#F3F4F6';"
                        onmouseout="this.style.backgroundColor='#FFFFFF';"
                    >
                        ✗ Bỏ chọn tất cả
                    </button>
                    <button 
                        type="button" 
                        onclick="selectByGroup()" 
                        class="px-4 py-2 rounded-lg text-sm font-medium border transition-all"
                        style="color: #2563EB; border-color: #DBEAFE; background-color: #EFF6FF;"
                        onmouseover="this.style.backgroundColor='#DBEAFE';"
                        onmouseout="this.style.backgroundColor='#EFF6FF';"
                    >
                        📁 Chọn theo nhóm
                    </button>
                </div>

                <!-- Permissions by Group -->
                <div class="space-y-6">
                    @foreach($allPermissions as $group => $permissions)
                        <div class="border rounded-lg overflow-hidden" style="border-color: #E5E7EB;">
                            <div class="px-4 py-3" style="background-color: #F9FAFB; border-bottom: 1px solid #E5E7EB;">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-semibold" style="color: #111827;">
                                        📁 {{ $group ?: 'Không có nhóm' }}
                                    </h4>
                                    <button 
                                        type="button" 
                                        onclick="toggleGroup('{{ $group ?: 'ungrouped' }}')"
                                        class="px-3 py-1 text-xs font-medium rounded border transition-all"
                                        style="color: #2563EB; border-color: #DBEAFE;"
                                        onmouseover="this.style.backgroundColor='#EFF6FF';"
                                        onmouseout="this.style.backgroundColor='transparent';"
                                    >
                                        Mở rộng / Thu gọn
                                    </button>
                                </div>
                            </div>
                            <div class="p-4 group-content" data-group="{{ $group ?: 'ungrouped' }}">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($permissions as $permission)
                                        <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-all hover:shadow-sm" 
                                               style="border-color: #E5E7EB; {{ $role->permissions->contains($permission->id) ? 'background-color: #ECFDF5; border-color: #10B981;' : '' }}">
                                            <input 
                                                type="checkbox" 
                                                name="permissions[]" 
                                                value="{{ $permission->id }}"
                                                {{ $role->permissions->contains($permission->id) ? 'checked' : '' }}
                                                class="mt-1 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                            >
                                            <div class="flex-1">
                                                <div class="font-medium text-sm" style="color: #111827;">
                                                    {{ $permission->name }}
                                                </div>
                                                <code class="text-xs text-gray-500 mt-1 block">{{ $permission->slug }}</code>
                                                @if($permission->description)
                                                    <p class="text-xs text-gray-500 mt-1">{{ $permission->description }}</p>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="bg-gray-50 px-6 py-4 flex items-center justify-between border-t shrink-0" style="border-color: #E5E7EB;">
                <div class="text-sm text-gray-600">
                    Đã chọn: <span id="selectedCount" class="font-semibold">0</span> permissions
                </div>
                <div class="flex gap-3">
                    <a 
                        href="{{ route('admin.permissions.index') }}" 
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all border"
                        style="color: #374151; border-color: #D1D5DB;"
                        onmouseover="this.style.backgroundColor='#F3F4F6';"
                        onmouseout="this.style.backgroundColor='transparent';"
                    >
                        Hủy
                    </a>
                    <button 
                        type="submit" 
                        class="px-6 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm"
                        style="background-color: #10B981;"
                        onmouseover="this.style.backgroundColor='#059669';"
                        onmouseout="this.style.backgroundColor='#10B981';"
                    >
                        💾 Lưu Permissions
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function updateSelectedCount() {
        const checked = document.querySelectorAll('input[name="permissions[]"]:checked').length;
        document.getElementById('selectedCount').textContent = checked;
    }

    function selectAll() {
        document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
            cb.checked = true;
        });
        updateSelectedCount();
    }

    function deselectAll() {
        document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
            cb.checked = false;
        });
        updateSelectedCount();
    }

    function toggleGroup(group) {
        const groupContent = document.querySelector(`.group-content[data-group="${group}"]`);
        if (groupContent) {
            groupContent.style.display = groupContent.style.display === 'none' ? 'block' : 'none';
        }
    }

    function selectByGroup() {
        const groups = document.querySelectorAll('.group-content');
        groups.forEach(group => {
            const checkboxes = group.querySelectorAll('input[name="permissions[]"]');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            checkboxes.forEach(cb => {
                cb.checked = !allChecked;
            });
        });
        updateSelectedCount();
    }

    // Update count on checkbox change
    document.addEventListener('DOMContentLoaded', function() {
        updateSelectedCount();
        document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
            cb.addEventListener('change', updateSelectedCount);
        });
    });
</script>
@endsection

@php
    $activeMenu = 'permissions';
@endphp



































