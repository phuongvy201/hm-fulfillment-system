@extends('layouts.app')

@section('title', 'Pricing Tiers Management - ' . config('app.name', 'Laravel'))

@section('header-title', 'Pricing Tiers Management')
@section('header-subtitle', 'Manage pricing tiers (wood, silver, gold, diamond, etc.)')

@section('header-actions')
<a href="{{ route('admin.pricing-tiers.create') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm" style="background-color: #2563EB;">
    + Add Pricing Tier
</a>
@endsection

@section('content')
<div class="space-y-6">
    @forelse($tiers as $tier)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4 flex-1">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center font-bold text-white" style="background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);">
                    {{ $tier->priority }}
                </div>
                <div>
                    <h3 class="text-lg font-semibold">{{ $tier->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $tier->slug }}</p>
                    <div class="flex flex-wrap gap-4 mt-2 text-sm text-gray-500">
                        <span>{{ $tier->user_pricing_tiers_count }} users</span>
                        @if($tier->auto_assign && $tier->min_orders !== null)
                            <span class="px-2 py-1 rounded" style="background-color: #DBEAFE; color: #1E40AF;">
                                ≥ {{ number_format($tier->min_orders) }} đơn/tháng
                            </span>
                        @elseif(!$tier->auto_assign)
                            <span class="px-2 py-1 rounded" style="background-color: #F3E8FF; color: #7C3AED;">
                                Gán thủ công
                            </span>
                        @else
                            <span class="px-2 py-1 rounded" style="background-color: #FEF3C7; color: #92400E;">
                                Mặc định
                            </span>
                        @endif
                        @if($tier->reset_period)
                            <span class="text-xs">
                                Reset: {{ $tier->reset_period === 'monthly' ? 'Hàng tháng' : ($tier->reset_period === 'quarterly' ? 'Hàng quý' : ($tier->reset_period === 'yearly' ? 'Hàng năm' : 'Không reset')) }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.pricing-tiers.show', $tier) }}" class="px-4 py-2 rounded-lg text-sm font-medium border" style="color: #2563EB; border-color: #DBEAFE;">
                    View
                </a>
                <a href="{{ route('admin.pricing-tiers.edit', $tier) }}" class="px-4 py-2 rounded-lg text-sm font-medium border" style="color: #2563EB; border-color: #DBEAFE;">
                    Edit
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
        <h3 class="text-lg font-semibold mb-2">No pricing tiers found</h3>
        <a href="{{ route('admin.pricing-tiers.create') }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background-color: #2563EB;">
            Add First Tier
        </a>
    </div>
    @endforelse
</div>

@if($tiers->hasPages())
<div class="mt-6">
    {{ $tiers->links() }}
</div>
@endif
@endsection

@php
    $activeMenu = 'pricing-tiers';
@endphp





