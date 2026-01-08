@extends('layouts.app')

@section('title', 'Market Details - ' . config('app.name', 'Laravel'))

@section('header-title', $market->name)
@section('header-subtitle', 'Market details and related information')

@section('header-actions')
<div class="flex items-center gap-2">
    <a href="{{ route('admin.markets.edit', $market) }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #2563EB; border-color: #DBEAFE; background-color: #EFF6FF;" onmouseover="this.style.backgroundColor='#DBEAFE';" onmouseout="this.style.backgroundColor='#EFF6FF';">
        Edit Market
    </a>
    <a href="{{ route('admin.markets.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
        ← Back to Markets
    </a>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Info -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Market Details -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Market Information</h3>
            <dl class="space-y-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500 mb-1">Code</dt>
                    <dd class="text-base font-semibold text-gray-900">{{ $market->code }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 mb-1">Name</dt>
                    <dd class="text-base font-semibold text-gray-900">{{ $market->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 mb-1">Currency</dt>
                    <dd class="text-base font-semibold text-gray-900">{{ $market->currency_symbol }} {{ $market->currency }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 mb-1">Timezone</dt>
                    <dd class="text-base font-semibold text-gray-900">{{ $market->timezone }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 mb-1">Status</dt>
                    <dd>
                        @if($market->status === 'active')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full" style="background-color: #D1FAE5; color: #065F46;">
                                Active
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full" style="background-color: #FEE2E2; color: #991B1B;">
                                Inactive
                            </span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Workshops -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Workshops</h3>
                <a href="{{ route('admin.workshops.index', ['market_id' => $market->id]) }}" class="text-sm font-medium" style="color: #2563EB;">
                    View All →
                </a>
            </div>
            @if($market->workshops->count() > 0)
                <div class="space-y-3">
                    @foreach($market->workshops->take(5) as $workshop)
                        <div class="flex items-center justify-between p-3 rounded-lg border" style="border-color: #E5E7EB;">
                            <div>
                                <p class="font-medium text-gray-900">{{ $workshop->name }}</p>
                                <p class="text-sm text-gray-500">{{ $workshop->code }}</p>
                            </div>
                            <a href="{{ route('admin.workshops.show', $workshop) }}" class="text-sm font-medium" style="color: #2563EB;">
                                View →
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500">No workshops found for this market.</p>
            @endif
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Statistics -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h3>
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-500">Workshops</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $market->workshops->count() }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Products</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $market->workshops->sum(function($workshop) { return $workshop->products->count(); }) }}</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('admin.workshops.create', ['market_id' => $market->id]) }}" class="block w-full px-4 py-2 rounded-lg text-sm font-medium text-center transition-all border" style="color: #2563EB; border-color: #DBEAFE; background-color: #EFF6FF;" onmouseover="this.style.backgroundColor='#DBEAFE';" onmouseout="this.style.backgroundColor='#EFF6FF';">
                    + Add Workshop
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@php
    $activeMenu = 'markets';
@endphp

