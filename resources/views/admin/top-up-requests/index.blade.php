@extends('layouts.admin-dashboard') 

@section('title', 'Top-up Requests Management - ' . config('app.name', 'Laravel'))

@section('header-title', 'Top-up Requests')
@section('header-subtitle', 'Manage top-up requests')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200">
        <div class="flex items-center gap-2 text-green-800">
            <span class="material-symbols-outlined">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
        <div class="flex items-center gap-2 text-red-800">
            <span class="material-symbols-outlined">error</span>
            <span>{{ $errors->first() }}</span>
        </div>
    </div>
    @endif

    <!-- Filters Section -->
    <section class="bg-white rounded-xl border border-gray-200 shadow-sm mb-8 p-6">
        <form method="GET" action="{{ route('admin.top-up-requests.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="lg:col-span-1">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Search</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="TXN, User ID..." 
                            class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-orange-500 focus:border-orange-500 transition-all"
                        />
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Status</label>
                    <select name="status" class="w-full py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Currency</label>
                    <select name="currency" class="w-full py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                        <option value="">All Currencies</option>
                        @foreach($currencies as $currency)
                            <option value="{{ $currency }}" {{ request('currency') == $currency ? 'selected' : '' }}>
                                {{ $currency }} @if($currency === 'USD')($)@elseif($currency === 'EUR')(€)@elseif($currency === 'VND')(₫)@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Date Range</label>
                    <input 
                        type="date" 
                        name="date_from" 
                        value="{{ request('date_from') }}"
                        class="w-full py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-orange-500 focus:border-orange-500 text-sm"
                    />
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-4 rounded-lg shadow-sm shadow-orange-500/20 transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-sm">filter_alt</span>
                        Apply Filters
                    </button>
                </div>
            </div>
            @if(request()->anyFilled(['status', 'user_id', 'currency', 'date_from', 'date_to', 'search']))
            <div class="mt-4">
                <a href="{{ route('admin.top-up-requests.index') }}" class="text-sm text-orange-600 hover:text-orange-700 font-medium">
                    Clear Filters
                </a>
            </div>
            @endif
        </form>
    </section>

    <!-- Requests List -->
    <div class="flex flex-col gap-4">
        @forelse($requests as $request)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden {{ $request->status === 'approved' ? 'opacity-90' : '' }}">
            <div class="p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="flex items-start gap-4 flex-1">
                    <div class="shrink-0">
                        @php
                            $initials = strtoupper(substr($request->user->name, 0, 2));
                            $colors = [
                                'emerald' => ['bg-emerald-100', 'text-emerald-600', 'border-emerald-200'],
                                'blue' => ['bg-blue-100', 'text-blue-600', 'border-blue-200'],
                                'purple' => ['bg-purple-100', 'text-purple-600', 'border-purple-200'],
                                'amber' => ['bg-amber-100', 'text-amber-600', 'border-amber-200'],
                            ];
                            $colorKey = array_rand($colors);
                            $colorClasses = $colors[$colorKey];
                        @endphp
                        <div class="w-14 h-14 rounded-xl {{ $colorClasses[0] }} {{ $colorClasses[1] }} flex items-center justify-center font-bold text-lg border {{ $colorClasses[2] }}">
                            {{ $initials }}
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center gap-3">
                            <h3 class="font-bold text-gray-900 text-lg">{{ $request->user->name }}</h3>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold flex items-center gap-1
                                @if($request->status === 'pending') bg-amber-100 text-amber-700
                                @elseif($request->status === 'approved') bg-emerald-100 text-emerald-700
                                @else bg-red-100 text-red-700
                                @endif">
                                @if($request->status === 'pending')
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                @elseif($request->status === 'approved')
                                    <span class="material-symbols-outlined text-[10px]">done</span>
                                @endif
                                {{ strtoupper($request->status) }}
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center text-sm text-gray-500 gap-y-1">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-base">fingerprint</span>
                                ID: #{{ $request->user->id }}
                            </span>
                            <span class="mx-2 hidden sm:block">•</span>
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-base">mail_outline</span>
                                {{ $request->user->email }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 lg:flex lg:items-center gap-x-8 gap-y-4 flex-[1.5]">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Amount</span>
                        <span class="text-xl font-bold text-gray-900">
                            @if($request->currency === 'USD')$@elseif($request->currency === 'EUR')€@elseif($request->currency === 'VND')₫@else{{ $request->currency }} @endif
                            {{ number_format($request->amount, 2) }}
                            <span class="text-sm font-medium text-gray-500">{{ $request->currency }}</span>
                        </span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Payment Method</span>
                        <div class="flex items-center gap-1 text-gray-700 font-medium">
                            <span class="material-symbols-outlined text-orange-500 text-lg">
                                @if($request->payment_method === 'bank_transfer')account_balance
                                @elseif($request->payment_method === 'credit_card')credit_card
                                @elseif($request->payment_method === 'lianpay' || $request->payment_method === 'pingpong' || $request->payment_method === 'worldfirst' || $request->payment_method === 'payoneer')payments
                                @elseaccount_balance
                                @endif
                            </span>
                            {{ ucfirst(str_replace('_', ' ', $request->payment_method)) }}
                        </div>
                    </div>
                    @if($request->transaction_code)
                    <div class="flex flex-col">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Transaction Code</span>
                        <div class="flex items-center gap-1.5 group">
                            <code class="bg-gray-100 px-2 py-0.5 rounded text-xs font-mono text-gray-600">{{ $request->transaction_code }}</code>
                            <button 
                                type="button"
                                onclick="copyToClipboard('{{ $request->transaction_code }}')"
                                class="text-gray-400 hover:text-orange-500 transition-colors"
                                title="Copy to clipboard"
                            >
                                <span class="material-symbols-outlined text-base">content_copy</span>
                            </button>
                        </div>
                    </div>
                    @endif
                    <div class="flex flex-col">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Timestamp</span>
                        <span class="text-sm text-gray-600 flex items-center gap-1">
                            <span class="material-symbols-outlined text-base">calendar_today</span>
                            {{ $request->created_at->format('d/m/Y H:i') }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-2 lg:border-l lg:pl-6 border-gray-200">
                    @if($request->status === 'pending')
                        @canPermission('top-up.approve')
                        <form method="POST" action="{{ route('admin.top-up-requests.approve', $request) }}" class="flex-1 lg:flex-none" onsubmit="return confirm('Are you sure you want to approve this top-up request?');">
                            @csrf
                            <input type="hidden" name="admin_notes" value="">
                            <button type="submit" class="w-full flex items-center justify-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-lg text-sm font-bold transition-all shadow-sm shadow-emerald-200">
                                <span class="material-symbols-outlined text-sm">check_circle</span>
                                Approve
                            </button>
                        </form>
                        <button 
                            type="button" 
                            onclick="openRejectModal({{ $request->id }})" 
                            class="flex-1 lg:flex-none flex items-center justify-center gap-1 border-2 border-red-100 text-red-600 px-4 py-2.5 rounded-lg text-sm font-bold hover:bg-red-50 transition-all"
                        >
                            <span class="material-symbols-outlined text-sm">cancel</span>
                            Reject
                        </button>
                        @endcanPermission
                    @else
                        <a href="{{ route('admin.top-up-requests.show', $request) }}" class="flex-1 lg:flex-none border border-gray-200 text-gray-600 px-4 py-2.5 rounded-lg text-sm font-bold hover:bg-gray-50 transition-all text-center">
                            View Receipt
                        </a>
                    @endif
                    <div class="relative group">
                        <button class="p-2.5 text-gray-400 hover:bg-gray-100 rounded-lg transition-colors">
                            <span class="material-symbols-outlined">more_vert</span>
                        </button>
                        <div class="hidden group-hover:block absolute right-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-10 min-w-[120px]">
                            <a href="{{ route('admin.top-up-requests.show', $request) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">View Details</a>
                            @canPermission('top-up.edit')
                            <a href="{{ route('admin.top-up-requests.edit', $request) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Edit</a>
                            @endcanPermission
                        </div>
                    </div>
                </div>
            </div>
            @if($request->paymentMethod && ($request->paymentMethod->account_number || $request->paymentMethod->account_holder))
            <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
                <div class="flex items-center gap-4 text-xs font-medium text-gray-500">
                    <span class="uppercase tracking-widest text-[10px] font-bold">Payment Details:</span>
                    @if($request->paymentMethod->account_number)
                        <span>Account: {{ $request->paymentMethod->account_number }}</span>
                    @endif
                    @if($request->paymentMethod->account_holder)
                        <span class="text-gray-300">|</span>
                        <span>Holder: {{ $request->paymentMethod->account_holder }}</span>
                    @endif
                </div>
            </div>
            @endif
            @if($request->notes || $request->admin_notes)
            <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
                @if($request->notes)
                <div class="mb-2">
                    <span class="text-xs font-semibold text-gray-600">User Notes:</span>
                    <p class="text-xs text-gray-500 mt-1">{{ $request->notes }}</p>
                </div>
                @endif
                @if($request->admin_notes)
                <div>
                    <span class="text-xs font-semibold {{ $request->status === 'rejected' ? 'text-red-600' : 'text-gray-600' }}">Admin Notes:</span>
                    <p class="text-xs {{ $request->status === 'rejected' ? 'text-red-500' : 'text-gray-500' }} mt-1">{{ $request->admin_notes }}</p>
                </div>
                @endif
            </div>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-12">
            <div class="text-center">
                <span class="material-symbols-outlined text-6xl text-gray-400">account_balance_wallet</span>
                <h3 class="mt-4 text-lg font-semibold text-gray-900">No Requests Found</h3>
                <p class="mt-2 text-sm text-gray-500">No top-up requests found matching the filters.</p>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Load More / Pagination -->
    @if($requests->hasPages())
    <div class="flex justify-center mt-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-6 py-4">
            {{ $requests->links() }}
        </div>
    </div>
    @endif

    <!-- Stats Footer -->
    @php
        $pendingVolume = \App\Models\TopUpRequest::where('status', 'pending')->sum('amount');
        $totalApproved24h = \App\Models\TopUpRequest::where('status', 'approved')
            ->where('approved_at', '>=', now()->subDay())
            ->sum('amount');
        $activeRequestors = \App\Models\TopUpRequest::where('status', 'pending')
            ->distinct('user_id')
            ->count('user_id');
    @endphp
    <footer class="mt-10">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="bg-white p-5 rounded-xl border border-gray-200 flex items-center gap-4">
                <div class="p-3 bg-amber-50 text-amber-600 rounded-lg">
                    <span class="material-symbols-outlined">hourglass_empty</span>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">Pending Volume</p>
                    <p class="text-xl font-bold text-gray-900">$ {{ number_format($pendingVolume, 2) }}</p>
                </div>
            </div>
            <div class="bg-white p-5 rounded-xl border border-gray-200 flex items-center gap-4">
                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-lg">
                    <span class="material-symbols-outlined">verified</span>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">Total Approved (24h)</p>
                    <p class="text-xl font-bold text-gray-900">$ {{ number_format($totalApproved24h, 2) }}</p>
                </div>
            </div>
            <div class="bg-white p-5 rounded-xl border border-gray-200 flex items-center gap-4">
                <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
                    <span class="material-symbols-outlined">group</span>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">Active Requestors</p>
                    <p class="text-xl font-bold text-gray-900">{{ $activeRequestors }} Users</p>
                </div>
            </div>
        </div>
    </footer>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Reject Top-up Request</h3>
        <form id="rejectForm" method="POST" action="">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-2 text-gray-900">Rejection Reason <span class="text-red-500">*</span></label>
                    <textarea 
                        name="admin_notes" 
                        required
                        rows="4"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                        placeholder="Please provide a reason for rejecting this request..."
                    ></textarea>
                    <p class="text-xs text-gray-500 mt-1">This reason will be visible to the customer.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button 
                        type="submit"
                        class="flex-1 px-4 py-2 rounded-lg text-sm font-semibold text-white transition-colors bg-red-500 hover:bg-red-600"
                    >
                        Confirm Reject
                    </button>
                    <button 
                        type="button"
                        onclick="closeRejectModal()"
                        class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors border border-gray-300 text-gray-700 hover:bg-gray-100"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openRejectModal(requestId) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.action = `/admin/top-up-requests/${requestId}/reject`;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeRejectModal() {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.reset();
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Close modal when clicking outside
document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show a temporary success message
        const button = event.target.closest('button');
        const originalHTML = button.innerHTML;
        button.innerHTML = '<span class="material-symbols-outlined text-base text-green-500">check</span>';
        setTimeout(() => {
            button.innerHTML = originalHTML;
        }, 2000);
    });
}
</script>
@endpush
@endsection

@php
    $activeMenu = 'top-up-requests';
@endphp
