@extends('layouts.app')

@section('title', 'Top-up Requests Management - ' . config('app.name', 'Laravel'))

@section('header-title', 'Top-up Requests Management')
@section('header-subtitle', 'Manage top-up requests')


@section('content')
<div class="space-y-6">
    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="GET" action="{{ route('admin.top-up-requests.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">User</label>
                    <select name="user_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Users</option>
                        @foreach($users as $userItem)
                            <option value="{{ $userItem->id }}" {{ request('user_id') == $userItem->id ? 'selected' : '' }}>
                                {{ $userItem->name }} ({{ $userItem->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                    <select name="currency" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All</option>
                        @foreach($currencies as $currency)
                            <option value="{{ $currency }}" {{ request('currency') == $currency ? 'selected' : '' }}>
                                {{ $currency }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                    <select name="sort_by" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Date</option>
                        <option value="amount" {{ request('sort_by') == 'amount' ? 'selected' : '' }}>Amount</option>
                        <option value="status" {{ request('sort_by') == 'status' ? 'selected' : '' }}>Status</option>
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                    <select name="sort_order" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Descending</option>
                        <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Ascending</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-6 py-2 rounded-lg text-sm font-semibold text-white transition-colors bg-blue-500 hover:bg-blue-600">
                        Filter
                    </button>
                    @if(request()->anyFilled(['status', 'user_id', 'currency', 'date_from', 'date_to']))
                    <a href="{{ route('admin.top-up-requests.index') }}" class="px-6 py-2 rounded-lg text-sm font-semibold transition-colors border border-gray-300 text-gray-700 hover:bg-gray-100">
                        Clear
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Requests List -->
    <div class="space-y-4">
        @forelse($requests as $request)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-200 overflow-hidden">
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left: User & Basic Info -->
                    <div class="lg:col-span-2 space-y-4">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-white text-lg shadow-md bg-gradient-to-br from-green-500 to-green-600 shrink-0">
                                {{ strtoupper(substr($request->user->name, 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3 mb-2 flex-wrap">
                                    <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $request->user->name }}</h3>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full whitespace-nowrap
                                        @if($request->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($request->status === 'approved') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ strtoupper($request->status) }}
                                    </span>
                                </div>
                                <div class="space-y-2 text-sm">
                                    <div class="flex items-center gap-2 text-gray-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                                        </svg>
                                        <span><strong>User ID:</strong> #{{ $request->user->id }}</span>
                                        <span class="text-gray-400">|</span>
                                        <span>{{ $request->user->email }}</span>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        <div class="flex items-center gap-2 text-gray-700">
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span><strong>Amount:</strong> {{ number_format($request->amount, 2) }} {{ $request->currency }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-gray-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span><strong>Payment:</strong> {{ ucfirst(str_replace('_', ' ', $request->payment_method)) }}</span>
                                        </div>
                                        @if($request->transaction_code)
                                        <div class="flex items-center gap-2 text-gray-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                            </svg>
                                            <span><strong>Transaction Code:</strong> <code class="px-2 py-0.5 bg-gray-100 rounded text-xs">{{ $request->transaction_code }}</code></span>
                                        </div>
                                        @endif
                                        <div class="flex items-center gap-2 text-gray-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <span><strong>Created:</strong> {{ $request->created_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                    </div>
                                    @if($request->paymentMethod)
                                    <div class="mt-2 p-3 bg-gray-50 rounded-lg text-xs text-gray-600">
                                        <strong>Payment Details:</strong>
                                        @if($request->paymentMethod->account_number)
                                            <span>Account: {{ $request->paymentMethod->account_number }}</span>
                                        @endif
                                        @if($request->paymentMethod->account_holder)
                                            <span class="ml-2">Holder: {{ $request->paymentMethod->account_holder }}</span>
                                        @endif
                                    </div>
                                    @endif
                                    @if($request->notes)
                                    <div class="mt-2 p-3 bg-blue-50 rounded-lg text-sm text-gray-700">
                                        <strong class="text-blue-900">User Notes:</strong>
                                        <p class="mt-1">{{ $request->notes }}</p>
                                    </div>
                                    @endif
                                    @if($request->admin_notes)
                                    <div class="mt-2 p-3 {{ $request->status === 'rejected' ? 'bg-red-50' : 'bg-gray-50' }} rounded-lg text-sm {{ $request->status === 'rejected' ? 'text-red-700' : 'text-gray-700' }}">
                                        <strong>Admin Notes:</strong>
                                        <p class="mt-1">{{ $request->admin_notes }}</p>
                                    </div>
                                    @endif
                                    @if($request->approved_at && $request->approver)
                                    <div class="text-xs text-gray-500">
                                        <strong>Processed by:</strong> {{ $request->approver->name }} on {{ $request->approved_at->format('d/m/Y H:i') }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Actions -->
                    <div class="lg:col-span-1 flex flex-col gap-3">
                        <div class="flex flex-col gap-2">
                            <a href="{{ route('admin.top-up-requests.show', $request) }}" class="w-full px-4 py-2 rounded-lg text-sm font-semibold text-white transition-colors bg-blue-500 hover:bg-blue-600 text-center">
                                View Details
                            </a>
                            @canPermission('top-up.edit')
                            <a href="{{ route('admin.top-up-requests.edit', $request) }}" class="w-full px-4 py-2 rounded-lg text-sm font-semibold transition-colors border border-gray-300 text-gray-700 hover:bg-gray-100 text-center">
                                Edit
                            </a>
                            @endcanPermission
                        </div>

                        @if($request->status === 'pending')
                        @canPermission('top-up.approve')
                        <div class="border-t pt-3 space-y-2">
                            <!-- Approve Form -->
                            <form method="POST" action="{{ route('admin.top-up-requests.approve', $request) }}" class="space-y-2" onsubmit="return confirm('Are you sure you want to approve this top-up request?');">
                                @csrf
                                <input type="hidden" name="admin_notes" value="">
                                <button type="submit" class="w-full px-4 py-2 rounded-lg text-sm font-semibold text-white transition-colors bg-green-500 hover:bg-green-600">
                                    ✓ Approve
                                </button>
                            </form>

                            <!-- Reject Form with Modal -->
                            <button type="button" onclick="openRejectModal({{ $request->id }})" class="w-full px-4 py-2 rounded-lg text-sm font-semibold text-white transition-colors bg-red-500 hover:bg-red-600">
                                ✗ Reject
                            </button>
                        </div>
                        @endcanPermission
                        @endif
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
                <h3 class="mt-4 text-lg font-semibold text-gray-900">No Requests Found</h3>
                <p class="mt-2 text-sm text-gray-500">No top-up requests found matching the filters.</p>
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

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
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
}

function closeRejectModal() {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.reset();
    modal.classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});
</script>
@endpush
@endsection

@php
    $activeMenu = 'top-up-requests';
@endphp

