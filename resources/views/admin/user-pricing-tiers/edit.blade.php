@extends('layouts.app')

@section('title', 'Edit User Pricing Tier - ' . config('app.name', 'Laravel'))

@section('header-title', 'Edit User Pricing Tier')
@section('header-subtitle', $user->name)

@section('header-actions')
<a href="{{ route('admin.user-pricing-tiers.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
    ← Back to List
</a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 pt-6 pb-4">
            <!-- User Info -->
            <div class="mb-6 p-4 rounded-lg bg-blue-50 border border-blue-200">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center font-bold text-white text-xl bg-blue-500">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $user->name }}</h3>
                        <p class="text-sm text-gray-600">{{ $user->email }}</p>
                        @if($user->role)
                            <span class="inline-block mt-1 px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $user->role->name }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
                    <ul class="text-sm text-red-800">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.user-pricing-tiers.update', $user) }}">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- Current Tier Info -->
                    @if($currentTier)
                    <div class="p-4 rounded-lg bg-green-50 border border-green-200">
                        <p class="text-sm text-green-800">
                            <strong>Pricing Tier hiện tại:</strong> {{ $currentTier->name }} ({{ $currentTier->slug }})
                            @if($currentTier->min_orders !== null)
                                - ≥ {{ number_format($currentTier->min_orders) }} đơn/tháng
                            @endif
                        </p>
                        @if($user->pricingTier && $user->pricingTier->assigned_at)
                            <p class="text-xs text-green-600 mt-1">
                                Được gán vào: {{ $user->pricingTier->assigned_at->format('d/m/Y H:i') }}
                            </p>
                        @endif
                    </div>
                    @else
                    <div class="p-4 rounded-lg bg-yellow-50 border border-yellow-200">
                        <p class="text-sm text-yellow-800">
                            <strong>Lưu ý:</strong> User này chưa có pricing tier được gán.
                        </p>
                    </div>
                    @endif

                    <!-- Tier Selection -->
                    <div>
                        <label class="block text-sm font-semibold mb-2 text-gray-900">Chọn Pricing Tier:</label>
                        <select 
                            name="pricing_tier_id" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        >
                            <option value="">-- Chọn tier --</option>
                            @foreach($tiers as $tier)
                                <option 
                                    value="{{ $tier->id }}" 
                                    {{ old('pricing_tier_id', $currentTier?->id) == $tier->id ? 'selected' : '' }}
                                >
                                    {{ $tier->name }} ({{ $tier->slug }})
                                    @if($tier->min_orders !== null)
                                        - ≥ {{ number_format($tier->min_orders) }} đơn/tháng
                                    @endif
                                    @if($tier->auto_assign)
                                        - Auto assign
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-600 mt-2">
                            Chọn pricing tier để gán cho user này. Tier này sẽ ảnh hưởng đến giá mà user nhìn thấy.
                        </p>
                    </div>

                    <!-- Tier Info -->
                    @if($currentTier)
                    <div class="p-4 rounded-lg bg-gray-50 border border-gray-200">
                        <h4 class="text-sm font-semibold mb-2 text-gray-900">Thông tin về tier hiện tại:</h4>
                        <ul class="text-xs text-gray-600 space-y-1">
                            <li><strong>Priority:</strong> {{ $currentTier->priority }}</li>
                            @if($currentTier->description)
                                <li><strong>Mô tả:</strong> {{ $currentTier->description }}</li>
                            @endif
                            @if($currentTier->min_orders !== null)
                                <li><strong>Điều kiện:</strong> ≥ {{ number_format($currentTier->min_orders) }} đơn/tháng</li>
                            @endif
                            <li><strong>Auto assign:</strong> {{ $currentTier->auto_assign ? 'Có' : 'Không' }}</li>
                            <li><strong>Status:</strong> {{ $currentTier->status }}</li>
                        </ul>
                    </div>
                    @endif
                </div>

                <div class="flex items-center gap-4 pt-6 border-t border-gray-200 mt-6">
                    <button 
                        type="submit"
                        class="px-6 py-3 rounded-lg font-semibold text-white transition-colors bg-green-500 hover:bg-green-600"
                    >
                        Cập nhật Pricing Tier
                    </button>
                    <a 
                        href="{{ route('admin.user-pricing-tiers.index') }}"
                        class="px-6 py-3 rounded-lg font-semibold transition-colors border border-gray-300 text-gray-700 hover:bg-gray-100"
                    >
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@php
    $activeMenu = 'user-pricing-tiers';
@endphp

