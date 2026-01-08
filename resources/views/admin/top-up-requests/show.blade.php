@extends('layouts.app')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Top-up Request Details - ' . config('app.name', 'Laravel'))

@section('header-title', 'Top-up Request Details')
@section('header-subtitle', 'View top-up request information')

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
                <dt class="text-sm font-medium text-gray-500 mb-1">Amount</dt>
                <dd class="text-base font-semibold text-gray-900">{{ number_format($topUpRequest->amount, 2) }} {{ $topUpRequest->currency }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Payment Method</dt>
                <dd class="text-base font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $topUpRequest->payment_method)) }}</dd>
            </div>
            @if($topUpRequest->transaction_code)
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Transaction Code</dt>
                <dd class="text-base font-mono text-gray-900">{{ $topUpRequest->transaction_code }}</dd>
            </div>
            @endif
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Status</dt>
                <dd>
                    @if($topUpRequest->status === 'pending')
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">PENDING</span>
                    @elseif($topUpRequest->status === 'approved')
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">APPROVED</span>
                    @else
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">REJECTED</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Created At</dt>
                <dd class="text-base text-gray-900">{{ $topUpRequest->created_at->format('d/m/Y H:i') }}</dd>
            </div>
            @if($topUpRequest->approved_at)
            <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Processed At</dt>
                <dd class="text-base text-gray-900">{{ $topUpRequest->approved_at->format('d/m/Y H:i') }}</dd>
                @if($topUpRequest->approver)
                <dd class="text-sm text-gray-600">By: {{ $topUpRequest->approver->name }}</dd>
                @endif
            </div>
            @endif
            @if($topUpRequest->notes)
            <div class="md:col-span-2">
                <dt class="text-sm font-medium text-gray-500 mb-1">User Notes</dt>
                <dd class="text-base text-gray-900">{{ $topUpRequest->notes }}</dd>
            </div>
            @endif
            @if($topUpRequest->admin_notes)
            <div class="md:col-span-2">
                <dt class="text-sm font-medium text-gray-500 mb-1">Admin Notes</dt>
                <dd class="text-base {{ $topUpRequest->status === 'rejected' ? 'text-red-600' : 'text-gray-900' }}">{{ $topUpRequest->admin_notes }}</dd>
            </div>
            @endif
        </dl>
    </div>

    <!-- Proof File -->
    @if($topUpRequest->proof_file)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">📎 Payment Proof</h3>
        <div class="flex items-center gap-4">
            @if(str_ends_with($topUpRequest->proof_file, '.pdf'))
                <a href="{{ Storage::url($topUpRequest->proof_file) }}" target="_blank" class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-colors bg-blue-500 hover:bg-blue-600">
                    View PDF
                </a>
            @else
                <img src="{{ Storage::url($topUpRequest->proof_file) }}" alt="Proof" class="max-w-md rounded-lg border border-gray-200">
            @endif
        </div>
    </div>
    @endif

    <!-- Actions (Only for pending requests) -->
    @if($topUpRequest->status === 'pending')
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">⚡ Actions</h3>
        
        <!-- Approve Form -->
        <form method="POST" action="{{ route('admin.top-up-requests.approve', $topUpRequest) }}" class="mb-4">
            @csrf
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                <textarea name="admin_notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Notes when approving..."></textarea>
            </div>
            <button type="submit" class="px-6 py-3 rounded-lg text-sm font-semibold text-white transition-colors bg-green-500 hover:bg-green-600">
                ✓ Approve Request
            </button>
        </form>

        <!-- Reject Form -->
        <form method="POST" action="{{ route('admin.top-up-requests.reject', $topUpRequest) }}">
            @csrf
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason <span class="text-red-500">*</span></label>
                <textarea name="admin_notes" rows="3" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="Enter rejection reason..."></textarea>
            </div>
            <button type="submit" class="px-6 py-3 rounded-lg text-sm font-semibold text-white transition-colors bg-red-500 hover:bg-red-600">
                ✗ Reject Request
            </button>
        </form>
    </div>
    @endif
</div>
@endsection

@php
    $activeMenu = 'top-up-requests';
@endphp

