@extends('layouts.admin-dashboard')

@section('title', 'Workshop Details - ' . config('app.name', 'Laravel'))

@section('header-title', $workshop->name)
@section('header-subtitle', 'Workshop details')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Workshop Information</h3>
            <div class="flex gap-2">
                <a href="{{ route('admin.workshops.edit', $workshop) }}" class="px-4 py-2 rounded-lg text-sm font-medium border" style="color: #2563EB; border-color: #DBEAFE;">
                    Edit
                </a>
            </div>
        </div>
        <dl class="grid grid-cols-2 gap-4">
            <div>
                <dt class="text-sm text-gray-500">Code</dt>
                <dd class="text-base font-semibold">{{ $workshop->code }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Market</dt>
                <dd class="text-base font-semibold">{{ $workshop->market->name }} ({{ $workshop->market->code }})</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Status</dt>
                <dd>
                    @if($workshop->status === 'active')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full" style="background-color: #D1FAE5; color: #065F46;">Active</span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full" style="background-color: #FEE2E2; color: #991B1B;">Inactive</span>
                    @endif
                </dd>
            </div>
        </dl>
    </div>
</div>
@endsection

@php
    $activeMenu = 'workshops';
@endphp





