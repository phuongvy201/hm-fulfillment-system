@extends('layouts.admin-dashboard') 

@section('title', 'Edit Top-up Request - ' . config('app.name', 'Laravel'))

@section('header-title', 'Edit Top-up Request')
@section('header-subtitle', 'Update top-up request information')

@section('header-actions')
<a href="{{ route('admin.top-up-requests.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
    ← Back to List
</a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Request Info -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Request Information</h3>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">User</dt>
                <dd class="text-base font-semibold text-gray-900">{{ $topUpRequest->user->name }}</dd>
                <dd class="text-sm text-gray-600">{{ $topUpRequest->user->email }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Payment Method</dt>
                <dd class="text-base font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $topUpRequest->payment_method)) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Transaction Code</dt>
                <dd class="text-base font-mono text-gray-900">{{ $topUpRequest->transaction_code }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Created At</dt>
                <dd class="text-base text-gray-900">{{ $topUpRequest->created_at->format('d/m/Y H:i') }}</dd>
            </div>
        </dl>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">✏️ Edit Request</h3>
        <form method="POST" action="{{ route('admin.top-up-requests.update', $topUpRequest) }}">
            @csrf
            @method('PUT')
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                    <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="pending" {{ $topUpRequest->status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ $topUpRequest->status === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ $topUpRequest->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">⚠️ Changing status from Pending to Approved will add balance to user's wallet.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Amount (Optional)</label>
                    <input 
                        type="number" 
                        name="amount" 
                        step="0.01"
                        value="{{ old('amount', $topUpRequest->amount) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Leave empty to keep original amount"
                    >
                    <p class="text-xs text-gray-500 mt-1">Only change if you need to adjust the amount. Wallet balance will be adjusted accordingly.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Admin Notes</label>
                    <textarea 
                        name="admin_notes" 
                        rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Add or update admin notes..."
                    >{{ old('admin_notes', $topUpRequest->admin_notes) }}</textarea>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="px-6 py-3 rounded-lg text-sm font-semibold text-white transition-colors bg-blue-500 hover:bg-blue-600">
                        Update Request
                    </button>
                    <a href="{{ route('admin.top-up-requests.show', $topUpRequest) }}" class="px-6 py-3 rounded-lg text-sm font-semibold transition-colors border border-gray-300 text-gray-700 hover:bg-gray-100">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@php
    $activeMenu = 'top-up-requests';
@endphp






































