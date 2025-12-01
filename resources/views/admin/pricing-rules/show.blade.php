@extends('layouts.app')

@section('title', 'Pricing Rule Details - ' . config('app.name', 'Laravel'))

@section('header-title', 'Pricing Rule Details')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <dl class="grid grid-cols-2 gap-4">
        <div>
            <dt class="text-sm text-gray-500">Market</dt>
            <dd class="text-base font-semibold">{{ $pricingRule->market->name }} ({{ $pricingRule->market->code }})</dd>
        </div>
        <div>
            <dt class="text-sm text-gray-500">Product</dt>
            <dd class="text-base font-semibold">{{ $pricingRule->product ? $pricingRule->product->name : 'All Products' }}</dd>
        </div>
        <div>
            <dt class="text-sm text-gray-500">Rule Type</dt>
            <dd class="text-base font-semibold">{{ $pricingRule->rule_type }}</dd>
        </div>
        <div>
            <dt class="text-sm text-gray-500">Operation</dt>
            <dd class="text-base font-semibold">{{ strtoupper($pricingRule->operation) }} {{ $pricingRule->amount }} {{ $pricingRule->currency ?? '' }}</dd>
        </div>
        <div>
            <dt class="text-sm text-gray-500">Priority</dt>
            <dd class="text-base font-semibold">{{ $pricingRule->priority }}</dd>
        </div>
        <div>
            <dt class="text-sm text-gray-500">Status</dt>
            <dd>
                @if($pricingRule->status === 'active')
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
    $activeMenu = 'pricing-rules';
@endphp





