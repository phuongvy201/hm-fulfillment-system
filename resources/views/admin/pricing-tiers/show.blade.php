@extends('layouts.app')

@section('title', 'Pricing Tier Details - ' . config('app.name', 'Laravel'))

@section('header-title', $pricingTier->name)

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
    <dl class="grid grid-cols-2 gap-4">
        <div>
            <dt class="text-sm text-gray-500">Slug</dt>
            <dd class="text-base font-semibold">{{ $pricingTier->slug }}</dd>
        </div>
        <div>
            <dt class="text-sm text-gray-500">Priority</dt>
            <dd class="text-base font-semibold">{{ $pricingTier->priority }}</dd>
        </div>
        <div>
            <dt class="text-sm text-gray-500">Status</dt>
            <dd>
                @if($pricingTier->status === 'active')
                    <span class="px-2 py-1 text-xs font-semibold rounded-full" style="background-color: #D1FAE5; color: #065F46;">Active</span>
                @else
                    <span class="px-2 py-1 text-xs font-semibold rounded-full" style="background-color: #FEE2E2; color: #991B1B;">Inactive</span>
                @endif
            </dd>
        </div>
    </dl>
</div>
@endsection

@php
    $activeMenu = 'pricing-tiers';
@endphp





