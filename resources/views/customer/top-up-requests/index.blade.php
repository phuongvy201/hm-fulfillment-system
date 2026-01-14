@extends('layouts.admin-dashboard')

@section('title', 'My Top-up Requests - ' . config('app.name', 'Laravel'))

@section('header-title', 'My Top-up Requests')
@section('header-subtitle', 'My top-up request history')

@section('header-actions')
<a href="{{ route('customer.top-up-requests.create') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all shadow-sm" style="background-color: #10B981;" onmouseover="this.style.backgroundColor='#059669';" onmouseout="this.style.backgroundColor='#10B981';">
    + Create Top-up Request
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Wallet Balance Info -->
    @php
        $wallet = auth()->user()->wallet;
        $currentBalance = $wallet ? $wallet->balance : 0;
    @endphp
    <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm opacity-90 mb-1">Current Wallet Balance</p>
                <p class="text-3xl font-bold">{{ number_format($currentBalance, 2) }} {{ $wallet->currency ?? 'USD' }}</p>
            </div>
            <svg class="w-16 h-16 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
    </div>

    <!-- Requests List -->
    <div class="space-y-4">
        @forelse($requests as $request)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-200 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-2 flex-wrap">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full whitespace-nowrap
                                @if($request->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($request->status === 'approved') bg-green-100 text-green-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ strtoupper($request->status) }}
                            </span>
                            <span class="text-lg font-semibold text-gray-900">{{ number_format($request->amount, 2) }} {{ $request->currency }}</span>
                        </div>
                        <div class="flex items-center gap-4 text-sm text-gray-600 flex-wrap">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>{{ ucfirst(str_replace('_', ' ', $request->payment_method)) }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span>{{ $request->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                        @if($request->admin_notes)
                        <div class="mt-2 text-sm {{ $request->status === 'rejected' ? 'text-red-600' : 'text-gray-600' }}">
                            <strong>Admin notes:</strong> {{ $request->admin_notes }}
                        </div>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('customer.top-up-requests.show', $request) }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors border border-gray-300 text-gray-700 hover:bg-gray-100">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12">
            <div class="text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-semibold text-gray-900">No Requests Yet</h3>
                <p class="mt-2 text-sm text-gray-500">You haven't created any top-up requests yet.</p>
                <a href="{{ route('customer.top-up-requests.create') }}" class="mt-4 inline-block px-6 py-3 rounded-lg text-sm font-semibold text-white transition-colors bg-green-500 hover:bg-green-600">
                    Create First Request
                </a>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($requests->hasPages())
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-6 py-4">
        {{ $requests->links() }}
    </div>
    @endif
</div>
@endsection

@php
    $activeMenu = 'wallet';
@endphp

