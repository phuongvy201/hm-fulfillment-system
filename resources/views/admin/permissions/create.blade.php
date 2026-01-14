@extends('layouts.admin-dashboard') 

@section('title', 'Create Permission - ' . config('app.name', 'Laravel'))

@section('header-title', '➕ Thêm Permission Mới')
@section('header-subtitle', 'Tạo permission mới cho hệ thống')

@section('header-actions')
<a href="{{ route('admin.permissions.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
    ← Quay lại
</a>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <form method="POST" action="{{ route('admin.permissions.store') }}">
            @csrf
            <div class="p-6 space-y-6">
                @if ($errors->any())
                    <div class="p-4 rounded-lg" style="background-color: #FEE2E2; border: 1px solid #EF4444;">
                        <ul class="text-sm" style="color: #991B1B;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Name -->
                <div>
                    <label class="block text-sm font-semibold mb-2" style="color: #111827;">
                        Tên Permission <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        value="{{ old('name') }}"
                        required
                        placeholder="Ví dụ: View Wallet"
                        class="w-full px-4 py-2 border rounded-lg text-sm"
                        style="border-color: #D1D5DB;"
                    >
                    <p class="text-xs text-gray-500 mt-1">Tên hiển thị của permission</p>
                </div>

                <!-- Slug -->
                <div>
                    <label class="block text-sm font-semibold mb-2" style="color: #111827;">
                        Slug <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="slug" 
                        value="{{ old('slug') }}"
                        required
                        placeholder="Ví dụ: wallet.view"
                        class="w-full px-4 py-2 border rounded-lg text-sm font-mono"
                        style="border-color: #D1D5DB;"
                    >
                    <p class="text-xs text-gray-500 mt-1">Slug duy nhất, dùng để check permission (ví dụ: wallet.view, credit.edit)</p>
                </div>

                <!-- Group -->
                <div>
                    <label class="block text-sm font-semibold mb-2" style="color: #111827;">
                        Nhóm
                    </label>
                    <input 
                        type="text" 
                        name="group" 
                        value="{{ old('group') }}"
                        list="groups"
                        placeholder="Ví dụ: wallet, credit, top-up"
                        class="w-full px-4 py-2 border rounded-lg text-sm"
                        style="border-color: #D1D5DB;"
                    >
                    <datalist id="groups">
                        @foreach($groups as $group)
                            <option value="{{ $group }}">
                        @endforeach
                    </datalist>
                    <p class="text-xs text-gray-500 mt-1">Nhóm permission để dễ quản lý (tùy chọn)</p>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-semibold mb-2" style="color: #111827;">
                        Mô tả
                    </label>
                    <textarea 
                        name="description" 
                        rows="3"
                        placeholder="Mô tả ngắn gọn về permission này..."
                        class="w-full px-4 py-2 border rounded-lg text-sm"
                        style="border-color: #D1D5DB;"
                    >{{ old('description') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Mô tả về mục đích sử dụng của permission</p>
                </div>
            </div>
            
            <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t shrink-0" style="border-color: #E5E7EB;">
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
                    💾 Tạo Permission
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@php
    $activeMenu = 'permissions';
@endphp






































