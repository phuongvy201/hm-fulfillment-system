@extends('layouts.app')

@section('title', 'Workshops Management - ' . config('app.name', 'Laravel'))

@section('header-title', 'Workshops Management')
@section('header-subtitle', 'Manage fulfillment workshops for each market')

@section('header-actions')
<a href="{{ route('admin.workshops.create') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm" style="background-color: #2563EB;" onmouseover="this.style.backgroundColor='#1D4ED8';" onmouseout="this.style.backgroundColor='#2563EB';">
    + Add Workshop
</a>
@endsection

@section('content')
<div class="space-y-6">
    @forelse($workshops as $workshop)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-200 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4 flex-1">
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center font-bold text-white text-lg shadow-md" style="background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $workshop->name }}</h3>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full" style="background-color: #DBEAFE; color: #1E40AF;">
                                {{ $workshop->code }}
                            </span>
                            @if($workshop->status === 'active')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full" style="background-color: #D1FAE5; color: #065F46;">
                                    Active
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full" style="background-color: #FEE2E2; color: #991B1B;">
                                    Inactive
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-4 text-sm text-gray-500">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>{{ $workshop->market->name }} ({{ $workshop->market->code }})</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                <span>{{ $workshop->products_count }} {{ $workshop->products_count === 1 ? 'product' : 'products' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 ml-4">
                    <a href="{{ route('admin.workshops.show', $workshop) }}" class="px-4 py-2 rounded-lg text-sm font-medium transition-all border" style="color: #2563EB; border-color: #DBEAFE; background-color: #EFF6FF;">
                        View
                    </a>
                    <a href="{{ route('admin.workshops.edit', $workshop) }}" class="px-4 py-2 rounded-lg text-sm font-medium transition-all border" style="color: #2563EB; border-color: #DBEAFE; background-color: #EFF6FF;">
                        Edit
                    </a>
                    <form action="{{ route('admin.workshops.destroy', $workshop) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium transition-all border" style="color: #DC2626; border-color: #FEE2E2; background-color: #FEF2F2;">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No workshops found</h3>
        <p class="text-sm text-gray-600 mb-6">Get started by creating a new workshop.</p>
        <a href="{{ route('admin.workshops.create') }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm" style="background-color: #2563EB;">
            Add First Workshop
        </a>
    </div>
    @endforelse
</div>

@if($workshops->hasPages())
<div class="mt-6 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-4 py-3">
        {{ $workshops->links() }}
    </div>
</div>
@endif
@endsection

@php
    $activeMenu = 'workshops';
@endphp





