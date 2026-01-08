@extends('layouts.app')

@section('title', $user->name . ' - ' . config('app.name', 'Laravel'))

@section('header-title', $user->name)
@section('header-subtitle', 'User Details')

@section('header-actions')
<a href="{{ route('admin.users.edit', $user) }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #2563EB; border-color: #DBEAFE; background-color: #EFF6FF;" onmouseover="this.style.backgroundColor='#DBEAFE';" onmouseout="this.style.backgroundColor='#EFF6FF';">
    Edit User
</a>
<a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
    ← Back to Users
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- User Info Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-start gap-4">
            <div class="w-16 h-16 rounded-full flex items-center justify-center font-bold text-white text-xl shadow-md" style="background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h2>
                    @if($user->role)
                        <span class="px-3 py-1 text-xs font-semibold rounded-full" style="background-color: #DBEAFE; color: #2563EB;">
                            {{ $user->role->name }}
                        </span>
                    @endif
                </div>
                <div class="space-y-2 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span>{{ $user->email }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>Joined: {{ $user->created_at->format('M d, Y') }}</span>
                    </div>
                    @if($user->team)
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0369A1;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span class="font-medium" style="color: #0369A1;">{{ $user->team->name }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Info -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Wallet Info -->
        @if($user->wallet)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">💰 Wallet</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Balance:</span>
                    <span class="text-sm font-semibold text-gray-900">{{ number_format($user->wallet->balance, 2) }} {{ $user->wallet->currency }}</span>
                </div>
            </div>
            <a href="{{ route('admin.wallets.show', $user) }}" class="mt-4 inline-block px-4 py-2 rounded-lg text-sm font-semibold text-white transition-colors bg-green-500 hover:bg-green-600">
                View Wallet Details
            </a>
        </div>
        @endif

        <!-- Credit Info -->
        @if($user->credit)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">💳 Credit</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Credit Limit:</span>
                    <span class="text-sm font-semibold text-gray-900">{{ number_format($user->credit->credit_limit, 2) }} USD</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Current Debt:</span>
                    <span class="text-sm font-semibold text-gray-900">{{ number_format($user->credit->current_credit, 2) }} USD</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Status:</span>
                    <span class="text-sm font-semibold {{ $user->credit->enabled ? 'text-green-600' : 'text-gray-600' }}">
                        {{ $user->credit->enabled ? 'Enabled' : 'Disabled' }}
                    </span>
                </div>
            </div>
            <a href="{{ route('admin.credits.edit', $user) }}" class="mt-4 inline-block px-4 py-2 rounded-lg text-sm font-semibold text-white transition-colors bg-blue-500 hover:bg-blue-600">
                Manage Credit
            </a>
        </div>
        @endif
    </div>

    <!-- Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.users.edit', $user) }}" class="px-6 py-2 rounded-lg text-sm font-semibold text-white transition-colors bg-blue-500 hover:bg-blue-600">
                Edit User
            </a>
            @if($user->id !== auth()->id())
            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-6 py-2 rounded-lg text-sm font-semibold text-white transition-colors bg-red-500 hover:bg-red-600">
                    Delete User
                </button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection

@php
    $activeMenu = 'users';
@endphp

