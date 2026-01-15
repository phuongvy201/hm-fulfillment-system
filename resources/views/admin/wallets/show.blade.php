@extends('layouts.admin-dashboard') 

@section('title', 'Wallet Details - ' . config('app.name', 'Laravel'))

@section('header-title', 'Wallet Details')
@section('header-subtitle', $user->name)

@section('header-actions')
<div class="flex items-center gap-3">
    <a href="{{ route('admin.wallets.index') }}" class="flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors font-medium">
        <span class="material-symbols-outlined text-lg">arrow_back</span>
        Back to Wallets
    </a>
    <a href="{{ route('admin.users.index') }}" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition-all font-medium flex items-center gap-2 shadow-sm">
        <span class="material-symbols-outlined text-lg">person_search</span>
        View User
    </a>
</div>
@endsection

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    <!-- User Info -->
    <section class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex items-center gap-6">
        <div class="w-20 h-20 rounded-2xl bg-orange-500/10 flex items-center justify-center text-orange-600 font-bold text-3xl">
            {{ strtoupper(substr($user->name, 0, 2)) }}
        </div>
        <div class="flex-1">
            <h2 class="text-xl font-bold flex items-center gap-2">
                {{ $user->name }}
                <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded-full uppercase tracking-wider">Active</span>
            </h2>
            <p class="text-gray-500 mt-1">{{ $user->email }}</p>
            <div class="mt-2 flex gap-4 text-sm">
                <span class="text-gray-400">User ID: <span class="text-gray-700 font-mono">#{{ $user->id }}</span></span>
                <span class="text-gray-400">Created: <span class="text-gray-700">{{ $user->created_at->format('M d, Y') }}</span></span>
            </div>
        </div>
    </section>

    <!-- Financial Overview -->
    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white border-l-4 border-orange-500 p-6 rounded-xl shadow-sm border-t border-r border-b border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-500 font-medium text-sm">Available Balance</span>
                <span class="material-symbols-outlined text-orange-500/40">account_balance_wallet</span>
            </div>
            <div class="text-2xl font-bold tracking-tight">{{ number_format($wallet->balance, 2) }} {{ $wallet->currency }}</div>
            <div class="mt-1 text-xs text-green-500 flex items-center gap-1">
                <span class="material-symbols-outlined text-xs">trending_up</span> Ready for use
            </div>
        </div>
        <div class="bg-white border-l-4 border-blue-500 p-6 rounded-xl shadow-sm border-t border-r border-b border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-500 font-medium text-sm">Credit Limit</span>
                <span class="material-symbols-outlined text-blue-500/40">credit_card</span>
            </div>
            <div class="text-2xl font-bold tracking-tight">{{ number_format($credit->credit_limit, 2) }} {{ $wallet->currency }}</div>
            <div class="mt-1 text-xs text-gray-400">Allocated limit</div>
        </div>
        <div class="bg-white border-l-4 {{ $credit->current_credit > 0 ? 'border-red-500' : 'border-gray-400' }} p-6 rounded-xl shadow-sm border-t border-r border-b border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-500 font-medium text-sm">Current Debt</span>
                <span class="material-symbols-outlined {{ $credit->current_credit > 0 ? 'text-red-500/40' : 'text-gray-400/40' }}">receipt_long</span>
            </div>
            <div class="text-2xl font-bold tracking-tight">{{ number_format($credit->current_credit, 2) }} {{ $wallet->currency }}</div>
            <div class="mt-1 text-xs text-gray-400">Pending payment</div>
        </div>
        <div class="bg-white border-l-4 border-purple-500 p-6 rounded-xl shadow-sm border-t border-r border-b border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-500 font-medium text-sm">Total Capacity</span>
                <span class="material-symbols-outlined text-purple-500/40">payments</span>
            </div>
            <div class="text-2xl font-bold tracking-tight">{{ number_format($total_payment_capacity, 2) }} {{ $wallet->currency }}</div>
            <div class="mt-1 text-xs text-gray-400">Balance + Remaining Credit</div>
        </div>
    </section>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        <div class="space-y-8">
            <!-- Credit Information -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-orange-600">credit_score</span>
                        Credit Information
                    </h3>
                    <span class="px-2 py-1 bg-gray-100 text-gray-500 text-[10px] font-bold uppercase rounded">
                        {{ $credit->enabled ? 'Enabled' : 'Disabled' }}
                    </span>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-3 gap-6 mb-8">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Credit Limit</p>
                            <p class="font-semibold">{{ number_format($credit->credit_limit, 2) }} {{ $wallet->currency }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Current Debt</p>
                            <p class="font-semibold {{ $credit->current_credit > 0 ? 'text-red-600' : '' }}">{{ number_format($credit->current_credit, 2) }} {{ $wallet->currency }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Remaining</p>
                            <p class="font-semibold text-orange-600">{{ number_format($remaining_credit, 2) }} {{ $wallet->currency }}</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.credits.edit', $user) }}" class="w-full sm:w-auto px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-900 font-semibold rounded-lg transition-colors flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-lg">edit</span>
                        Manage Credit Settings
                    </a>
                </div>
            </div>

            <!-- Adjust Balance (Admin only) -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-orange-600">settings_suggest</span>
                        Adjust Balance (Admin)
                    </h3>
                </div>
                <form method="POST" action="{{ route('admin.wallets.adjust', $user) }}" class="p-6 space-y-5">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold mb-1.5 text-gray-700">Adjustment Type <span class="text-orange-500">*</span></label>
                            <select name="type" required class="w-full bg-white border border-gray-200 rounded-lg px-4 py-2 focus:ring-orange-500 focus:border-orange-500">
                                <option value="add">Add Money</option>
                                <option value="deduct">Subtract Money</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1.5 text-gray-700">Amount ({{ $wallet->currency }}) <span class="text-orange-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                    @if($wallet->currency === 'USD')$@elseif($wallet->currency === 'EUR')€@elseif($wallet->currency === 'VND')₫@else{{ $wallet->currency }} @endif
                                </span>
                                <input 
                                    type="number" 
                                    name="amount" 
                                    step="0.01"
                                    min="0.01"
                                    required
                                    class="w-full bg-white border border-gray-200 rounded-lg pl-7 pr-4 py-2 focus:ring-orange-500 focus:border-orange-500" 
                                    placeholder="0.00"
                                />
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-gray-700">Description <span class="text-orange-500">*</span></label>
                        <input 
                            type="text" 
                            name="description" 
                            required
                            class="w-full bg-white border border-gray-200 rounded-lg px-4 py-2 focus:ring-orange-500 focus:border-orange-500" 
                            placeholder="Reason for adjustment (e.g., Bonus, Correction)..."
                        />
                        <p class="mt-1.5 text-xs text-gray-500 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">info</span>
                            This action will be logged for audit purposes.
                        </p>
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="bg-orange-500 text-white px-8 py-2.5 rounded-lg hover:bg-orange-600 transition-all font-semibold shadow-sm">
                            Apply Adjustment
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Process Refund (Admin only) -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm h-fit">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-pink-500">assignment_return</span>
                    Process Refund (Admin)
                </h3>
            </div>
            <form method="POST" action="{{ route('admin.wallets.refund', $user) }}" class="p-6 space-y-5">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold mb-1.5 text-gray-700">Refund Amount ({{ $wallet->currency }}) <span class="text-orange-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                @if($wallet->currency === 'USD')$@elseif($wallet->currency === 'EUR')€@elseif($wallet->currency === 'VND')₫@else{{ $wallet->currency }} @endif
                            </span>
                            <input 
                                type="number" 
                                name="amount" 
                                step="0.01"
                                min="0.01"
                                required
                                class="w-full bg-white border border-gray-200 rounded-lg pl-7 pr-4 py-2 focus:ring-orange-500 focus:border-orange-500" 
                                placeholder="Enter refund amount"
                            />
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold mb-1.5 text-gray-700">Refund Reason <span class="text-orange-500">*</span></label>
                        <textarea 
                            name="description" 
                            required
                            rows="3"
                            class="w-full bg-white border border-gray-200 rounded-lg px-4 py-2 focus:ring-orange-500 focus:border-orange-500" 
                            placeholder="Reason for refund (e.g., Order cancellation, Product defect)..."
                        ></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-gray-700">Reference Type <span class="text-gray-400 font-normal">(Optional)</span></label>
                        <input 
                            type="text" 
                            name="reference_type" 
                            class="w-full bg-white border border-gray-200 rounded-lg px-4 py-2 focus:ring-orange-500 focus:border-orange-500" 
                            placeholder="e.g., Order, Invoice"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-gray-700">Reference ID <span class="text-gray-400 font-normal">(Optional)</span></label>
                        <input 
                            type="text" 
                            name="reference_id" 
                            class="w-full bg-white border border-gray-200 rounded-lg px-4 py-2 focus:ring-orange-500 focus:border-orange-500" 
                            placeholder="e.g., ORD-12345"
                        />
                    </div>
                </div>
                <div class="pt-2">
                    <button type="submit" class="bg-pink-500 text-white px-8 py-2.5 rounded-lg hover:bg-pink-600 transition-all font-semibold shadow-sm">
                        Process Refund
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Transactions History -->
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-orange-600">history</span>
                Transaction History
            </h3>
            <div class="flex gap-2">
                <button class="p-2 border border-gray-200 rounded hover:bg-gray-50">
                    <span class="material-symbols-outlined text-xl text-gray-500">filter_list</span>
                </button>
                <button class="p-2 border border-gray-200 rounded hover:bg-gray-50">
                    <span class="material-symbols-outlined text-xl text-gray-500">file_download</span>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 font-semibold uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4">Amount</th>
                        <th class="px-6 py-4">Balance (Prev/Post)</th>
                        <th class="px-6 py-4">Description</th>
                        <th class="px-6 py-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transactions as $transaction)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">
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
                            {{ number_format($transaction->balance_before, 2) }} / {{ number_format($transaction->balance_after, 2) }} {{ $wallet->currency }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $transaction->description ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
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
                        <td colspan="6" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center">
                                <span class="material-symbols-outlined text-5xl text-gray-200 mb-4">receipt</span>
                                <p class="text-gray-500 font-medium">No transactions found</p>
                                <p class="text-xs text-gray-400 mt-1">Activities related to this wallet will appear here.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex items-center justify-between">
            <p class="text-xs text-gray-500">Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} of {{ $transactions->total() }} transactions</p>
            <div class="flex gap-1">
                {{ $transactions->links() }}
            </div>
        </div>
        @else
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex items-center justify-between">
            <p class="text-xs text-gray-500">Showing {{ $transactions->count() }} of {{ $transactions->count() }} transactions</p>
        </div>
        @endif
    </section>
</div>
@endsection

@php
    $activeMenu = 'wallets';
@endphp
