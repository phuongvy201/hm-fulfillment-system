@extends('layouts.app')

@section('title', 'Pricing Rules Management - ' . config('app.name', 'Laravel'))

@section('header-title', 'Pricing Rules Management')
@section('header-subtitle', 'Manage pricing rules (e.g., t-shirt UK in 1 mặt +3.5, size S trừ 0.5$)')

@section('header-actions')
<a href="{{ route('admin.pricing-rules.create') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm" style="background-color: #2563EB;">
    + Add Pricing Rule
</a>
@endsection

@section('content')
<div class="space-y-6">
    @forelse($rules as $rule)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold">{{ $rule->rule_type }}</h3>
                <p class="text-sm text-gray-500">
                    {{ $rule->market->name }} 
                    @if($rule->product)
                        - {{ $rule->product->name }}
                    @endif
                </p>
                <p class="text-sm text-gray-600 mt-2">
                    @if($rule->condition_key && $rule->condition_value)
                        If {{ $rule->condition_key }} = {{ $rule->condition_value }}, then
                    @endif
                    {{ strtoupper($rule->operation) }} {{ $rule->amount }} {{ $rule->currency ?? '' }}
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.pricing-rules.show', $rule) }}" class="px-4 py-2 rounded-lg text-sm font-medium border" style="color: #2563EB; border-color: #DBEAFE;">
                    View
                </a>
                <a href="{{ route('admin.pricing-rules.edit', $rule) }}" class="px-4 py-2 rounded-lg text-sm font-medium border" style="color: #2563EB; border-color: #DBEAFE;">
                    Edit
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
        <h3 class="text-lg font-semibold mb-2">No pricing rules found</h3>
        <a href="{{ route('admin.pricing-rules.create') }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background-color: #2563EB;">
            Add First Rule
        </a>
    </div>
    @endforelse
</div>

@if($rules->hasPages())
<div class="mt-6">
    {{ $rules->links() }}
</div>
@endif
@endsection

@php
    $activeMenu = 'pricing-rules';
@endphp





