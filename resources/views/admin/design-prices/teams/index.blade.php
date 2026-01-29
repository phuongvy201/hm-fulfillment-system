@extends('layouts.admin-dashboard')

@section('title', 'Team Design Prices - ' . config('app.name', 'Laravel'))

@section('header-title', 'Team Design Prices')
@section('header-subtitle', 'Manage custom design pricing for teams')

@section('header-actions')
<div class="flex items-center gap-3">
    <a href="{{ route('admin.design-prices.users.index') }}" class="px-5 py-2.5 rounded-lg font-semibold flex items-center gap-2 transition-colors shadow-sm border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
        <span class="material-symbols-outlined">person</span>
        User Prices
    </a>
    <a href="{{ route('admin.design-prices.teams.create') }}" class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2.5 rounded-lg font-semibold flex items-center gap-2 transition-colors shadow-sm">
        <span class="material-symbols-outlined">add</span>
        Add Team Price
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    @if(session('success'))
    <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200">
        <div class="flex items-center gap-2 text-green-800">
            <span class="material-symbols-outlined">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Team Design Prices</h2>
            <p class="text-sm text-gray-500 mt-1">Custom pricing for teams</p>
        </div>
        <div class="p-6 overflow-x-auto">
            @if($prices->count() > 0)
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">Team</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">First Side (VND)</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">Additional Side (VND)</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">Valid Period</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($prices as $price)
                    <tr>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $price->team->name }}</div>
                            @if($price->team->description)
                            <div class="text-sm text-gray-500">{{ Str::limit($price->team->description, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="text-sm font-semibold text-gray-900">{{ number_format($price->first_side_price_vnd, 0) }}</span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="text-sm font-semibold text-gray-900">{{ number_format($price->additional_side_price_vnd, 0) }}</span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $price->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($price->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($price->valid_from || $price->valid_to)
                                <div>{{ $price->valid_from ? $price->valid_from->format('Y-m-d') : 'N/A' }}</div>
                                <div class="text-xs">to {{ $price->valid_to ? $price->valid_to->format('Y-m-d') : 'N/A' }}</div>
                            @else
                                <span class="text-gray-400">No limit</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.design-prices.teams.edit', $price) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                <form action="{{ route('admin.design-prices.teams.destroy', $price) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this price?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-4 py-4 border-t border-gray-200">
                {{ $prices->links() }}
            </div>
            @else
            <div class="text-center py-12">
                <p class="text-gray-500">No team design prices found.</p>
                <a href="{{ route('admin.design-prices.teams.create') }}" class="mt-4 inline-block text-blue-600 hover:text-blue-900">Create your first team design price</a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@php
    $activeMenu = 'design-prices';
@endphp

