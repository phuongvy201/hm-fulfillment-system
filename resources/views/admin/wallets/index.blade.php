@extends('layouts.app')

@section('title', 'Wallet Management - ' . config('app.name', 'Laravel'))

@section('header-title', 'Wallet Management')
@section('header-subtitle', 'View and manage customer wallets')

@section('content')
<div class="space-y-6">
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="GET" action="{{ route('admin.wallets.index') }}" class="flex items-end gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Search Customer</label>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Search by name or email..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
            </div>
            <div>
                <button type="submit" class="px-6 py-2 rounded-lg text-sm font-semibold text-white transition-colors bg-blue-500 hover:bg-blue-600">
                    Search
                </button>
            </div>
            @if(request('search'))
            <div>
                <a href="{{ route('admin.wallets.index') }}" class="px-6 py-2 rounded-lg text-sm font-semibold transition-colors border border-gray-300 text-gray-700 hover:bg-gray-100">
                    Clear
                </a>
            </div>
            @endif
        </form>
    </div>

    <!-- Wallets List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Customer Wallets</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Available Balance</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Credit Limit</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Current Debt</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Remaining Credit</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Payment Capacity</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white text-sm bg-gradient-to-br from-green-500 to-green-600">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-semibold text-green-600">
                                {{ number_format($user->available_balance, 2) }} USD
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm text-gray-900">
                                {{ number_format($user->credit_limit, 2) }} USD
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-semibold {{ $user->current_debt > 0 ? 'text-red-600' : 'text-gray-600' }}">
                                {{ number_format($user->current_debt, 2) }} USD
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-semibold text-blue-600">
                                {{ number_format($user->remaining_credit, 2) }} USD
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-bold text-purple-600">
                                {{ number_format($user->total_payment_capacity, 2) }} USD
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('admin.wallets.show', $user) }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-colors bg-blue-500 hover:bg-blue-600">
                                <svg class="w-4 h-4 inline mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            No customers found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@php
    $activeMenu = 'wallets';
@endphp



































