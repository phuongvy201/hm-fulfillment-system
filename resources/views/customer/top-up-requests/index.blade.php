@extends('layouts.admin-dashboard')

@section('title', 'My Wallet & Transactions - ' . config('app.name', 'Laravel'))

@section('header-title', 'My Wallet & Transactions')
@section('header-subtitle', 'View your wallet balance and transaction history')

@section('header-actions')
<a href="{{ route('customer.top-up-requests.create') }}" class="px-4 py-2.5 bg-primary hover:bg-orange-600 text-white rounded-xl font-bold text-sm shadow-md shadow-primary/20 transition-all flex items-center gap-2 active:scale-95">
    <span class="material-symbols-outlined text-lg">add_circle</span>
    Top-up
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Available Balance Card -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden group">
            <div class="absolute right-0 top-0 p-8 opacity-5 group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-7xl text-primary">account_balance_wallet</span>
            </div>
            <p class="text-sm font-medium text-slate-500 mb-1">Available Balance</p>
            <div class="flex items-end justify-between gap-4">
                <div>
                    <h3 class="text-3xl font-bold text-slate-900 dark:text-white">${{ number_format($currentBalance, 2) }}</h3>
                    @if(isset($wallet) && $wallet && $wallet->currency === 'USD')
                        @php
                            $exchangeRate = 27000; // Default exchange rate, can be dynamic
                            $vndAmount = $currentBalance * $exchangeRate;
                        @endphp
                        <p class="text-xs text-slate-400 mt-1">≈ {{ number_format($vndAmount, 0) }} ₫</p>
                    @endif
                </div>
                <a href="{{ route('customer.top-up-requests.create') }}" class="bg-primary hover:bg-orange-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-md shadow-primary/20 transition-all flex items-center gap-2 active:scale-95">
                    <span class="material-symbols-outlined text-lg">add_circle</span>
                    Top-up
                </a>
            </div>
        </div>

        <!-- Total Spent Card -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-orange-50 dark:bg-orange-900/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-orange-600">shopping_bag</span>
                </div>
                <p class="text-sm font-medium text-slate-500">Total Spent</p>
            </div>
            <h3 class="text-3xl font-bold text-slate-900 dark:text-white">${{ number_format($totalSpent, 2) }}</h3>
            @if(isset($spentChange) && $spentChange != 0)
                <p class="text-xs {{ $spentChange > 0 ? 'text-emerald-500' : 'text-red-500' }} mt-2 flex items-center gap-1 font-medium">
                    <span class="material-symbols-outlined text-sm">{{ $spentChange > 0 ? 'trending_up' : 'trending_down' }}</span>
                    {{ $spentChange > 0 ? '+' : '' }}{{ number_format($spentChange, 1) }}% from last month
                </p>
            @endif
        </div>

        <!-- Pending Transactions Card -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-600">schedule</span>
                </div>
                <p class="text-sm font-medium text-slate-500">Pending Transactions</p>
            </div>
            <h3 class="text-3xl font-bold text-slate-900 dark:text-white">${{ number_format($pendingAmount, 2) }}</h3>
            <p class="text-xs text-slate-400 mt-2 font-medium">{{ $pendingCount }} active {{ $pendingCount == 1 ? 'request' : 'requests' }}</p>
        </div>
    </div>

    <!-- Credit Card (displayed separately if enabled) -->
    @if(isset($credit) && $credit && $credit->enabled)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-purple-600">credit_card</span>
                </div>
                <p class="text-sm font-medium text-slate-500">Current Credit</p>
            </div>
            <h3 class="text-3xl font-bold text-slate-900 dark:text-white">${{ number_format($currentCredit, 2) }}</h3>
            <div class="mt-2 space-y-1">
                <p class="text-xs text-slate-400">
                    Limit: <span class="font-medium text-slate-600 dark:text-slate-300">${{ number_format($creditLimit, 2) }}</span>
                </p>
                <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                    Available: ${{ number_format($availableCredit, 2) }}
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-4 shadow-sm">
        <form method="GET" action="{{ route('customer.wallet.index') }}" class="flex flex-wrap items-center gap-4">
            <!-- Search -->
            <div class="flex-1 min-w-[280px] relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400">search</span>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search ?? '' }}"
                    class="w-full pl-11 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm transition-all" 
                    placeholder="Search by Transaction ID..."
                />
            </div>

            <!-- Type Filter -->
            <div class="relative">
                <select 
                    name="type" 
                    class="appearance-none bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 pr-10 text-sm font-medium focus:ring-primary/20 focus:border-primary outline-none cursor-pointer min-w-[160px]"
                    onchange="this.form.submit()"
                >
                    <option value="">All Types</option>
                    <option value="deposit" {{ ($type ?? '') === 'deposit' ? 'selected' : '' }}>Deposit</option>
                    <option value="payment" {{ ($type ?? '') === 'payment' ? 'selected' : '' }}>Order Payment</option>
                    <option value="refund" {{ ($type ?? '') === 'refund' ? 'selected' : '' }}>Refund</option>
                    @if(isset($credit) && $credit && $credit->enabled)
                        <option value="credit" {{ ($type ?? '') === 'credit' ? 'selected' : '' }}>Credit Transactions</option>
                    @endif
                </select>
                <span class="absolute right-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 pointer-events-none text-lg">expand_more</span>
            </div>

            <!-- Date Range Filter -->
            <div class="relative">
                <select 
                    name="date_range" 
                    class="appearance-none bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 pr-10 text-sm font-medium focus:ring-primary/20 focus:border-primary outline-none cursor-pointer min-w-[180px]"
                    onchange="this.form.submit()"
                >
                    <option value="last-30" {{ ($dateRange ?? 'last-30') === 'last-30' ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="last-90" {{ ($dateRange ?? 'last-30') === 'last-90' ? 'selected' : '' }}>Last 90 Days</option>
                    <option value="custom" {{ ($dateRange ?? 'last-30') === 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>
                <span class="absolute right-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 pointer-events-none text-lg">calendar_today</span>
            </div>

            <a 
                href="{{ route('customer.wallet.index') }}" 
                class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl font-semibold text-sm hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors"
            >
                Reset
            </a>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse table-fixed-header">
                <thead >
                    <tr class="border-b border-slate-200 dark:border-slate-700">
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest">Transaction ID</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest">Date & Time</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest">Type</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest text-right">Amount</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest text-right">Balance After</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($transactions as $transaction)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-sm">{{ $transaction->display_id }}</span>
                                    @if($transaction->short_reference_id)
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $transaction->short_reference_id }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium">{{ $transaction->created_at->format('M d, Y') }}</div>
                                <div class="text-xs text-slate-400">{{ $transaction->created_at->format('h:i A') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    @php
                                        $typeConfig = [
                                            'top_up' => ['icon' => 'account_balance', 'bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 'text' => 'text-emerald-600'],
                                            'payment' => ['icon' => 'receipt_long', 'bg' => 'bg-red-50 dark:bg-red-900/20', 'text' => 'text-red-600'],
                                            'refund' => ['icon' => 'assignment_return', 'bg' => 'bg-blue-50 dark:bg-blue-900/20', 'text' => 'text-blue-600'],
                                            'credit_used' => ['icon' => 'credit_card', 'bg' => 'bg-red-50 dark:bg-red-900/20', 'text' => 'text-red-600'],
                                            'credit_payment' => ['icon' => 'payments', 'bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 'text' => 'text-emerald-600'],
                                            'admin_adjustment' => ['icon' => 'settings', 'bg' => 'bg-slate-50 dark:bg-slate-900/20', 'text' => 'text-slate-600'],
                                        ];
                                        $config = $typeConfig[$transaction->type] ?? ['icon' => 'account_balance', 'bg' => 'bg-slate-50 dark:bg-slate-900/20', 'text' => 'text-slate-600'];
                                    @endphp
                                    <div class="w-8 h-8 rounded-lg {{ $config['bg'] }} flex items-center justify-center {{ $config['text'] }}">
                                        <span class="material-symbols-outlined text-[18px]">{{ $config['icon'] }}</span>
                                    </div>
                                    <span class="text-sm font-semibold">{{ $transaction->type_display }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm font-bold {{ $transaction->amount >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $transaction->amount >= 0 ? '+' : '' }}${{ number_format(abs($transaction->amount), 2) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                @if($transaction->status === 'pending')
                                    <span class="italic text-slate-400">Calculating...</span>
                                @else
                                    ${{ number_format($transaction->balance_after, 2) }}
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusConfig = [
                                        'completed' => ['bg' => 'bg-emerald-50 dark:bg-emerald-900/30', 'text' => 'text-emerald-600 dark:text-emerald-400'],
                                        'pending' => ['bg' => 'bg-amber-50 dark:bg-amber-900/30', 'text' => 'text-amber-600 dark:text-amber-400'],
                                        'failed' => ['bg' => 'bg-red-50 dark:bg-red-900/30', 'text' => 'text-red-600 dark:text-red-400'],
                                        'cancelled' => ['bg' => 'bg-slate-50 dark:bg-slate-900/30', 'text' => 'text-slate-600 dark:text-slate-400'],
                                    ];
                                    $statusStyle = $statusConfig[$transaction->status] ?? ['bg' => 'bg-slate-50 dark:bg-slate-900/30', 'text' => 'text-slate-600 dark:text-slate-400'];
                                @endphp
                                <span class="px-3 py-1 {{ $statusStyle['bg'] }} {{ $statusStyle['text'] }} text-[11px] font-bold rounded-full uppercase tracking-wider">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="material-symbols-outlined text-5xl text-slate-400 mb-2">receipt_long</span>
                                    <p class="text-sm font-semibold text-slate-600 dark:text-slate-400">No transactions found</p>
                                    <p class="text-xs text-slate-500 mt-1">Your transaction history will appear here</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($transactions->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <p class="text-xs text-slate-500 font-medium">
                Showing {{ $transactions->firstItem() }}-{{ $transactions->lastItem() }} of {{ $transactions->total() }} transactions
            </p>
            <div class="flex gap-2">
                @if($transactions->onFirstPage())
                    <button class="p-2 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-50 cursor-not-allowed" disabled>
                        <span class="material-symbols-outlined text-sm">chevron_left</span>
                    </button>
                @else
                    <a href="{{ $transactions->previousPageUrl() }}" class="p-2 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800">
                        <span class="material-symbols-outlined text-sm">chevron_left</span>
                    </a>
                @endif
                
                @foreach($transactions->getUrlRange(1, $transactions->lastPage()) as $page => $url)
                    @if($page == $transactions->currentPage())
                        <button class="px-3 py-1 bg-primary text-white rounded-lg text-sm font-bold">{{ $page }}</button>
                    @else
                        <a href="{{ $url }}" class="px-3 py-1 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg text-sm font-medium">{{ $page }}</a>
                    @endif
                @endforeach
                
                @if($transactions->hasMorePages())
                    <a href="{{ $transactions->nextPageUrl() }}" class="p-2 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800">
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                    </a>
                @else
                    <button class="p-2 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-50 cursor-not-allowed" disabled>
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                    </button>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .table-fixed-header thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #F9FAFB;
    }
    .dark .table-fixed-header thead th {
        background: #1e293b;
    }
</style>
@endpush
@endsection

@php
    $activeMenu = 'wallet';
@endphp
