@extends('layouts.admin-dashboard')

@section('title', 'Edit Pricing Tier - ' . config('app.name', 'Laravel'))

@section('header-title', 'Edit Pricing Tier')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow-sm p-8">
        <form method="POST" action="{{ route('admin.pricing-tiers.update', $pricingTier) }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <label for="slug" class="block text-sm font-semibold mb-2">Slug</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug', $pricingTier->slug) }}" required class="w-full px-4 py-3 border rounded-lg">
                </div>

                <div>
                    <label for="name" class="block text-sm font-semibold mb-2">Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $pricingTier->name) }}" required class="w-full px-4 py-3 border rounded-lg">
                </div>

                <div>
                    <label for="priority" class="block text-sm font-semibold mb-2">Priority</label>
                    <input type="number" id="priority" name="priority" value="{{ old('priority', $pricingTier->priority) }}" min="0" class="w-full px-4 py-3 border rounded-lg">
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold mb-2">Description</label>
                    <textarea id="description" name="description" rows="4" class="w-full px-4 py-3 border rounded-lg">{{ old('description', $pricingTier->description) }}</textarea>
                </div>

                <!-- Điều kiện tự động phân hạng -->
                <div class="border-t pt-6 mt-6">
                    <h3 class="text-lg font-semibold mb-4">Điều kiện tự động phân hạng</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    id="auto_assign" 
                                    name="auto_assign" 
                                    value="1"
                                    {{ old('auto_assign', $pricingTier->auto_assign ?? true) ? 'checked' : '' }}
                                    onchange="toggleAutoAssignFields()"
                                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                                <span class="text-sm font-medium">Tự động gán tier này dựa trên số đơn hàng</span>
                            </label>
                            <p class="text-xs text-gray-500 mt-1 ml-6">Bỏ chọn nếu tier này chỉ được gán thủ công (ví dụ: special tier)</p>
                        </div>

                        <div id="autoAssignFields">
                            <div>
                                <label for="min_orders" class="block text-sm font-semibold mb-2">Số đơn hàng tối thiểu</label>
                                <input 
                                    type="number" 
                                    id="min_orders" 
                                    name="min_orders" 
                                    value="{{ old('min_orders', $pricingTier->min_orders) }}" 
                                    min="0" 
                                    placeholder="Ví dụ: 1500"
                                    class="w-full px-4 py-3 border rounded-lg"
                                >
                                <p class="text-xs text-gray-500 mt-1">Số đơn hàng tối thiểu trong tháng để đạt tier này (để trống nếu không có điều kiện)</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <strong>Ví dụ:</strong> Wood = để trống (mặc định), Silver = 1500, Gold = 4500, Diamond = 9000
                                </p>
                            </div>

                            <div>
                                <label for="reset_period" class="block text-sm font-semibold mb-2">Chu kỳ reset</label>
                                <select id="reset_period" name="reset_period" required class="w-full px-4 py-3 border rounded-lg">
                                    <option value="monthly" {{ old('reset_period', $pricingTier->reset_period ?? 'monthly') === 'monthly' ? 'selected' : '' }}>Hàng tháng</option>
                                    <option value="quarterly" {{ old('reset_period', $pricingTier->reset_period) === 'quarterly' ? 'selected' : '' }}>Hàng quý</option>
                                    <option value="yearly" {{ old('reset_period', $pricingTier->reset_period) === 'yearly' ? 'selected' : '' }}>Hàng năm</option>
                                    <option value="never" {{ old('reset_period', $pricingTier->reset_period) === 'never' ? 'selected' : '' }}>Không reset</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Thời gian reset số đơn hàng để tính lại tier</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="status" class="block text-sm font-semibold mb-2">Status</label>
                    <select id="status" name="status" required class="w-full px-4 py-3 border rounded-lg">
                        <option value="active" {{ old('status', $pricingTier->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $pricingTier->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="flex items-center gap-4 pt-4">
                    <button type="submit" class="px-6 py-3 rounded-lg font-semibold text-white" style="background-color: #2563EB;">
                        Update Tier
                    </button>
                    <a href="{{ route('admin.pricing-tiers.index') }}" class="px-6 py-3 rounded-lg font-semibold border" style="color: #374151; border-color: #D1D5DB;">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

<script>
    function toggleAutoAssignFields() {
        const autoAssign = document.getElementById('auto_assign');
        const fields = document.getElementById('autoAssignFields');
        
        if (autoAssign.checked) {
            fields.style.display = 'block';
            document.getElementById('min_orders').disabled = false;
            document.getElementById('reset_period').disabled = false;
        } else {
            fields.style.display = 'none';
            document.getElementById('min_orders').disabled = true;
            document.getElementById('reset_period').disabled = true;
            document.getElementById('min_orders').value = '';
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleAutoAssignFields();
    });
</script>

@php
    $activeMenu = 'pricing-tiers';
@endphp





