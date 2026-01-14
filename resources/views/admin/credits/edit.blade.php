@extends('layouts.admin-dashboard')

@section('title', 'Edit Credit - ' . config('app.name', 'Laravel'))

@section('header-title', 'Quản lý Credit')
@section('header-subtitle', $user->name)

@section('header-actions')
<a href="{{ route('admin.credits.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
    ← Back to List
</a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- User Info -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full flex items-center justify-center font-bold text-white text-xl bg-gradient-to-br from-purple-500 to-purple-600">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ $user->name }}</h3>
                <p class="text-sm text-gray-600">{{ $user->email }}</p>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-white rounded-xl shadow-sm border border-red-200 p-6">
            <ul class="text-sm text-red-800">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Credit Settings -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">⚙️ Cài đặt Credit</h3>
        <form method="POST" action="{{ route('admin.credits.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="enabled" 
                            value="1"
                            {{ old('enabled', $credit->enabled) ? 'checked' : '' }}
                            class="w-5 h-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                        >
                        <span class="text-sm font-semibold text-gray-900">Bật credit cho user này</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1 ml-7">Khi bật, user có thể chi trước trả sau trong phạm vi hạn mức</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2 text-gray-900">Hạn mức công nợ (Credit Limit) <span class="text-red-500">*</span></label>
                    <input 
                        type="number" 
                        name="credit_limit" 
                        step="0.01" 
                        min="0"
                        required
                        value="{{ old('credit_limit', $credit->credit_limit) }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                    >
                    <p class="text-xs text-gray-500 mt-1">Số tiền tối đa user có thể nợ</p>
                </div>

                <!-- Current Credit Info -->
                @if($credit->current_credit > 0)
                <div class="p-4 rounded-lg bg-yellow-50 border border-yellow-200">
                    <p class="text-sm text-yellow-800">
                        <strong>⚠️ Lưu ý:</strong> User hiện đang có công nợ: <strong>{{ number_format($credit->current_credit, 2) }}</strong>
                    </p>
                </div>
                @endif

                <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
                    <button 
                        type="submit"
                        class="px-6 py-3 rounded-lg text-sm font-semibold text-white transition-colors bg-purple-500 hover:bg-purple-600"
                    >
                        Cập nhật Credit
                    </button>
                    <a 
                        href="{{ route('admin.credits.index') }}"
                        class="px-6 py-3 rounded-lg text-sm font-semibold transition-colors border border-gray-300 text-gray-700 hover:bg-gray-100"
                    >
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Adjust Current Debt Manually (Admin) -->
    @if($credit->enabled)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">⚙️ Adjust Current Debt (Admin)</h3>
        <form method="POST" action="{{ route('admin.credits.adjust-debt', $user) }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-2 text-gray-900">Type <span class="text-red-500">*</span></label>
                    <select name="type" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="increase">Increase Debt</option>
                        <option value="decrease">Decrease Debt</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2 text-gray-900">Amount <span class="text-red-500">*</span></label>
                    <input 
                        type="number" 
                        name="amount" 
                        step="0.01" 
                        min="0.01"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                        placeholder="0.00"
                    >
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2 text-gray-900">Description <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="description" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                        placeholder="Reason for adjustment (e.g., Manual correction, Refund)..."
                    >
                    <p class="text-xs text-gray-500 mt-1">This will be logged for audit purposes.</p>
                </div>
                <button 
                    type="submit"
                    class="px-6 py-3 rounded-lg text-sm font-semibold text-white transition-colors bg-orange-500 hover:bg-orange-600"
                >
                    Adjust Debt
                </button>
            </div>
        </form>
    </div>
    @endif

    <!-- Pay Credit from Wallet -->
    @if($credit->enabled && $credit->current_credit > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">💳 Pay Debt from Wallet</h3>
        @php
            $wallet = $user->wallet;
            $walletBalance = $wallet ? $wallet->balance : 0;
        @endphp
        
        <div class="mb-4 p-4 rounded-lg bg-blue-50 border border-blue-200">
            <p class="text-sm text-blue-900">
                <strong>Số dư ví:</strong> {{ number_format($walletBalance, 2) }} {{ $wallet->currency ?? 'USD' }}<br>
                <strong>Công nợ hiện tại:</strong> {{ number_format($credit->current_credit, 2) }}
            </p>
        </div>

        <form method="POST" action="{{ route('admin.credits.pay-from-wallet', $user) }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-2 text-gray-900">Số tiền thanh toán <span class="text-red-500">*</span></label>
                    <input 
                        type="number" 
                        name="amount" 
                        step="0.01" 
                        min="0.01"
                        max="{{ min($walletBalance, $credit->current_credit) }}"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                        placeholder="0.00"
                    >
                    <p class="text-xs text-gray-500 mt-1">Tối đa: {{ number_format(min($walletBalance, $credit->current_credit), 2) }}</p>
                </div>
                <button 
                    type="submit"
                    class="px-6 py-3 rounded-lg text-sm font-semibold text-white transition-colors bg-green-500 hover:bg-green-600"
                >
                    Thanh toán từ ví
                </button>
            </div>
        </form>
    </div>
    @endif
</div>
@endsection

@php
    $activeMenu = 'credits';
@endphp

