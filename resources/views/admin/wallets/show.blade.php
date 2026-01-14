@extends('layouts.admin-dashboard') 

@section('title', 'Wallet Details - ' . config('app.name', 'Laravel'))

@section('header-title', 'Wallet Details')
@section('header-subtitle', $user->name)

@section('header-actions')
<div class="flex items-center gap-3">
    <a href="{{ route('admin.wallets.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
        ← Back to Wallets
    </a>
    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
        View User
    </a>
</div>
@endsection

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- User Info -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full flex items-center justify-center font-bold text-white text-xl bg-gradient-to-br from-green-500 to-green-600">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ $user->name }}</h3>
                <p class="text-sm text-gray-600">{{ $user->email }}</p>
            </div>
        </div>
    </div>

    <!-- Financial Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Available Balance -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
            <p class="text-sm opacity-90 mb-1">Available Balance</p>
            <p class="text-3xl font-bold">{{ number_format($wallet->balance, 2) }} {{ $wallet->currency }}</p>
        </div>

        <!-- Credit Limit -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
            <p class="text-sm opacity-90 mb-1">Credit Limit</p>
            <p class="text-3xl font-bold">{{ number_format($credit->credit_limit, 2) }} {{ $wallet->currency }}</p>
        </div>

        <!-- Current Debt -->
        <div class="bg-gradient-to-r {{ $credit->current_credit > 0 ? 'from-red-500 to-red-600' : 'from-gray-500 to-gray-600' }} rounded-xl shadow-lg p-6 text-white">
            <p class="text-sm opacity-90 mb-1">Current Debt</p>
            <p class="text-3xl font-bold">{{ number_format($credit->current_credit, 2) }} {{ $wallet->currency }}</p>
        </div>

        <!-- Total Payment Capacity -->
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
            <p class="text-sm opacity-90 mb-1">Total Payment Capacity</p>
            <p class="text-3xl font-bold">{{ number_format($total_payment_capacity, 2) }} {{ $wallet->currency }}</p>
            <p class="text-xs opacity-75 mt-1">Balance + Remaining Credit</p>
        </div>
    </div>

    <!-- Credit Details -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">💳 Credit Information</h3>
        <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Credit Limit</dt>
                <dd class="text-base font-semibold text-gray-900">{{ number_format($credit->credit_limit, 2) }} {{ $wallet->currency }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Current Debt</dt>
                <dd class="text-base font-semibold {{ $credit->current_credit > 0 ? 'text-red-600' : 'text-gray-900' }}">
                    {{ number_format($credit->current_credit, 2) }} {{ $wallet->currency }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Remaining Credit</dt>
                <dd class="text-base font-semibold text-blue-600">
                    {{ number_format($remaining_credit, 2) }} {{ $wallet->currency }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Credit Status</dt>
                <dd>
                    @if($credit->enabled)
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Enabled</span>
                    @else
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Disabled</span>
                    @endif
                </dd>
            </div>
            <div class="md:col-span-2">
                <a href="{{ route('admin.credits.edit', $user) }}" class="inline-block px-4 py-2 rounded-lg text-sm font-semibold text-white transition-colors bg-blue-500 hover:bg-blue-600">
                    Manage Credit
                </a>
            </div>
        </dl>
    </div>

    <!-- Adjust Balance (Admin only) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">⚙️ Adjust Balance (Admin)</h3>
        <form method="POST" action="{{ route('admin.wallets.adjust', $user) }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-2 text-gray-900">Type <span class="text-red-500">*</span></label>
                    <select name="type" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="add">Add Money</option>
                        <option value="deduct">Deduct Money</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2 text-gray-900">Amount <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-2">
                        <input 
                            type="number" 
                            name="amount" 
                            step="0.01"
                            min="0.01"
                            required
                            class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            placeholder="Enter amount"
                        >
                        <span class="text-sm text-gray-600">{{ $wallet->currency }}</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2 text-gray-900">Description <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="description" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="Reason for adjustment (e.g., Bonus, Refund, Correction)..."
                    >
                    <p class="text-xs text-gray-500 mt-1">This will be logged for audit purposes.</p>
                </div>
                <button 
                    type="submit"
                    class="px-6 py-3 rounded-lg text-sm font-semibold text-white transition-colors bg-green-500 hover:bg-green-600"
                >
                    Adjust Balance
                </button>
            </div>
        </form>
    </div>

    <!-- Process Refund (Admin only) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">💰 Process Refund (Admin)</h3>
        <form method="POST" action="{{ route('admin.wallets.refund', $user) }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-2 text-gray-900">Refund Amount <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-2">
                        <input 
                            type="number" 
                            name="amount" 
                            step="0.01"
                            min="0.01"
                            required
                            class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                            placeholder="Enter refund amount"
                        >
                        <span class="text-sm text-gray-600">{{ $wallet->currency }}</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2 text-gray-900">Refund Reason <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="description" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                        placeholder="Reason for refund (e.g., Order cancellation, Product defect, Service issue)..."
                    >
                    <p class="text-xs text-gray-500 mt-1">This will be logged for audit purposes.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2 text-gray-900">Reference Type (Optional)</label>
                        <input 
                            type="text" 
                            name="reference_type" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                            placeholder="e.g., Order, Invoice, etc."
                        >
                        <p class="text-xs text-gray-500 mt-1">Link this refund to an order or invoice</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2 text-gray-900">Reference ID (Optional)</label>
                        <input 
                            type="number" 
                            name="reference_id" 
                            min="1"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                            placeholder="e.g., 123"
                        >
                        <p class="text-xs text-gray-500 mt-1">ID of the related order/invoice</p>
                    </div>
                </div>
                <button 
                    type="submit"
                    class="px-6 py-3 rounded-lg text-sm font-semibold text-white transition-colors bg-pink-500 hover:bg-pink-600"
                >
                    Process Refund
                </button>
            </div>
        </form>
    </div>

    <!-- Transactions History -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">📊 Transaction History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance Before</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance After</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($transactions as $transaction)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $transaction->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                @if($transaction->type === 'top_up') bg-green-100 text-green-800
                                @elseif($transaction->type === 'payment') bg-blue-100 text-blue-800
                                @elseif($transaction->type === 'credit_usage') bg-yellow-100 text-yellow-800
                                @elseif($transaction->type === 'credit_payment') bg-purple-100 text-purple-800
                                @elseif($transaction->type === 'admin_adjustment') bg-gray-100 text-gray-800
                                @elseif($transaction->type === 'refund') bg-pink-100 text-pink-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ $transaction->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount, 2) }} {{ $wallet->currency }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ number_format($transaction->balance_before, 2) }} {{ $wallet->currency }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                            {{ number_format($transaction->balance_after, 2) }} {{ $wallet->currency }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $transaction->description ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            @if($transaction->creator)
                                <span class="text-xs">{{ $transaction->creator->name }}</span>
                            @else
                                <span class="text-xs text-gray-400">System</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                @if($transaction->status === 'completed') bg-green-100 text-green-800
                                @elseif($transaction->status === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ strtoupper($transaction->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            No transactions found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($transactions->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@php
    $activeMenu = 'wallets';
@endphp

