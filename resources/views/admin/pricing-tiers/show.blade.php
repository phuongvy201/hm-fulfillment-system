@extends('layouts.admin-dashboard')

@section('title', 'Pricing Tier Details - ' . config('app.name', 'Laravel'))

@section('header-title', $pricingTier->name)
@section('header-subtitle', 'Chi tiết về pricing tier')

@section('header-actions')
<a href="{{ route('admin.pricing-tiers.edit', $pricingTier) }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #2563EB; border-color: #DBEAFE; background-color: #EFF6FF;" onmouseover="this.style.backgroundColor='#DBEAFE';" onmouseout="this.style.backgroundColor='#EFF6FF';">
    ✏️ Chỉnh sửa
</a>
<a href="{{ route('admin.pricing-tiers.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
    ← Back to List
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Basic Information -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Thông tin cơ bản</h3>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Tên</dt>
                <dd class="text-base font-semibold text-gray-900">{{ $pricingTier->name }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Slug</dt>
                <dd class="text-base font-semibold text-gray-900">{{ $pricingTier->slug }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Priority</dt>
                <dd class="text-base font-semibold text-gray-900">{{ $pricingTier->priority }}</dd>
                <p class="text-xs text-gray-500 mt-1">Priority càng cao thì tier càng được ưu tiên khi auto-assign</p>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Status</dt>
                <dd>
                    @if($pricingTier->status === 'active')
                        <span class="px-3 py-1 text-xs font-semibold rounded-full" style="background-color: #D1FAE5; color: #065F46;">Active</span>
                    @else
                        <span class="px-3 py-1 text-xs font-semibold rounded-full" style="background-color: #FEE2E2; color: #991B1B;">Inactive</span>
                    @endif
                </dd>
            </div>
            @if($pricingTier->description)
            <div class="md:col-span-2">
                <dt class="text-sm font-medium text-gray-500 mb-1">Mô tả</dt>
                <dd class="text-base text-gray-900">{{ $pricingTier->description }}</dd>
            </div>
            @endif
        </dl>
    </div>

    <!-- Auto-Assign Settings -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">⚙️ Cài đặt Auto-Assign</h3>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Auto Assign</dt>
                <dd>
                    @if($pricingTier->auto_assign)
                        <span class="px-3 py-1 text-xs font-semibold rounded-full" style="background-color: #D1FAE5; color: #065F46;">✓ Có</span>
                        <p class="text-xs text-gray-500 mt-1">Tier này sẽ được tự động gán cho users đáp ứng điều kiện</p>
                    @else
                        <span class="px-3 py-1 text-xs font-semibold rounded-full" style="background-color: #FEE2E2; color: #991B1B;">✗ Không</span>
                        <p class="text-xs text-gray-500 mt-1">Tier này chỉ được gán thủ công</p>
                    @endif
                </dd>
            </div>
            @if($pricingTier->auto_assign && $pricingTier->min_orders !== null)
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Điều kiện (Min Orders)</dt>
                <dd class="text-base font-semibold text-gray-900">
                    ≥ {{ number_format($pricingTier->min_orders) }} đơn/tháng
                </dd>
                <p class="text-xs text-gray-500 mt-1">Users cần đạt tối thiểu số đơn này để được gán tier</p>
            </div>
            @endif
            @if($pricingTier->auto_assign)
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Reset Period</dt>
                <dd class="text-base font-semibold text-gray-900">
                    @if($pricingTier->reset_period === 'monthly')
                        Hàng tháng
                    @elseif($pricingTier->reset_period === 'quarterly')
                        Hàng quý
                    @elseif($pricingTier->reset_period === 'yearly')
                        Hàng năm
                    @else
                        Không reset
                    @endif
                </dd>
                <p class="text-xs text-gray-500 mt-1">Chu kỳ reset để đếm lại số đơn cho auto-assign</p>
            </div>
            @endif
            @if(!$pricingTier->auto_assign)
            <div class="md:col-span-2">
                <div class="p-4 rounded-lg bg-yellow-50 border border-yellow-200">
                    <p class="text-sm text-yellow-800">
                        <strong>💡 Lưu ý:</strong> Tier này không có auto-assign, chỉ có thể gán thủ công cho users.
                    </p>
                </div>
            </div>
            @endif
        </dl>
    </div>

    <!-- Statistics -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Thống kê</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="p-4 rounded-lg border" style="border-color: #DBEAFE; background-color: #EFF6FF;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Số lượng Users</p>
                        <p class="text-3xl font-bold mt-2" style="color: #1E40AF;">
                            {{ $pricingTier->userPricingTiers()->count() }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Users đang sử dụng tier này</p>
                    </div>
                    <div class="w-16 h-16 rounded-full flex items-center justify-center" style="background-color: #DBEAFE;">
                        <svg class="w-8 h-8" style="color: #1E40AF;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="p-4 rounded-lg border" style="border-color: #D1FAE5; background-color: #ECFDF5;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Số lượng Products</p>
                        <p class="text-3xl font-bold mt-2" style="color: #059669;">
                            {{ $pricingTier->productTierPrices()->distinct('product_id')->count() }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Products có giá cho tier này</p>
                    </div>
                    <div class="w-16 h-16 rounded-full flex items-center justify-center" style="background-color: #D1FAE5;">
                        <svg class="w-8 h-8" style="color: #059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">🔗 Hành động</h3>
        <div class="flex flex-wrap gap-3">
            <a 
                href="{{ route('admin.user-pricing-tiers.index', ['tier_id' => $pricingTier->id]) }}"
                class="px-6 py-3 rounded-lg text-sm font-semibold text-white transition-colors bg-blue-500 hover:bg-blue-600"
            >
                👥 Xem Users có tier này
            </a>
            <a 
                href="{{ route('admin.pricing-tiers.edit', $pricingTier) }}"
                class="px-6 py-3 rounded-lg text-sm font-semibold transition-colors border border-gray-300 text-gray-700 hover:bg-gray-100"
            >
                ✏️ Chỉnh sửa Tier
            </a>
        </div>
    </div>
</div>
@endsection

@php
    $activeMenu = 'pricing-tiers';
@endphp





