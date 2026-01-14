@extends('layouts.admin-dashboard')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Top Up - ' . config('app.name', 'Laravel'))

@section('content')
<div class="max-w-6xl mx-auto py-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold" style="color: #111827;">Top Up</h1>
        <a href="{{ route('customer.top-up-requests.index') }}" class="w-10 h-10 rounded-full flex items-center justify-center transition-all" style="background-color: #F3F4F6; color: #6B7280;" onmouseover="this.style.backgroundColor='#E5E7EB';" onmouseout="this.style.backgroundColor='#F3F4F6';">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </a>
    </div>

    <!-- Payment Methods Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Tabs Navigation -->
        <div class="border-b" style="border-color: #E5E7EB;">
            <div class="flex overflow-x-auto">
                @foreach($paymentMethods as $method)
                    <button 
                        type="button"
                        onclick="switchTab('{{ $method->slug }}')"
                        id="tab-{{ $method->slug }}"
                        class="px-6 py-4 text-sm font-medium whitespace-nowrap border-b-2 transition-all tab-button"
                        style="border-color: transparent; color: #6B7280;"
                        data-method-slug="{{ $method->slug }}"
                    >
                        {{ $method->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            @foreach($paymentMethods as $index => $method)
                <div id="content-{{ $method->slug }}" class="tab-content {{ $index === 0 ? '' : 'hidden' }}" data-method-id="{{ $method->id }}">
                    <!-- Exchange Rate (for Bank Vietnam) -->
                    @if($method->type === 'bank_transfer' && $method->exchange_rate)
                        <div class="mb-6 p-4 rounded-lg" style="background-color: #EFF6FF; border: 1px solid #DBEAFE;">
                            <p class="text-sm font-medium" style="color: #1E40AF;">
                                Exchange rate today: $1.00 ↔ {{ number_format($method->exchange_rate, 0) }}₫
                            </p>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Column: Bank Info / QR Code -->
                        <div class="space-y-6">
                            @if($method->type === 'bank_transfer')
                                <!-- Bank Transfer Details -->
                                <div class="p-4 rounded-lg border" style="border-color: #E5E7EB; background-color: #F9FAFB;">
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium" style="color: #6B7280;">Bank:</span>
                                            <span class="text-sm font-semibold" style="color: #111827;">{{ $method->bank_name }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium" style="color: #6B7280;">Account Number:</span>
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-semibold font-mono" style="color: #111827;">{{ $method->account_number }}</span>
                                                <button 
                                                    type="button"
                                                    onclick="copyToClipboard('{{ $method->account_number }}', 'account-number-{{ $method->id }}')"
                                                    class="w-6 h-6 rounded flex items-center justify-center transition-all"
                                                    style="background-color: #DBEAFE; color: #2563EB;"
                                                    onmouseover="this.style.backgroundColor='#BFDBFE';"
                                                    onmouseout="this.style.backgroundColor='#DBEAFE';"
                                                    id="account-number-{{ $method->id }}"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium" style="color: #6B7280;">Account Holder:</span>
                                            <span class="text-sm font-semibold" style="color: #111827;">{{ $method->account_holder }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- QR Code -->
                                @if($method->qr_code)
                                    <div class="flex flex-col items-center">
                                        <div class="w-64 h-64 p-4 rounded-lg border" style="border-color: #E5E7EB; background-color: #FFFFFF;">
                                            @php
                                                // Handle QR code path: remove 'storage/' prefix if present
                                                $qrCodePath = $method->qr_code;
                                                if (str_starts_with($qrCodePath, 'storage/')) {
                                                    $qrCodePath = str_replace('storage/', '', $qrCodePath);
                                                }
                                                // Storage::url() expects path relative to storage/app/public
                                                // If path is 'images/qr-code.jpg', it will generate correct URL
                                                try {
                                                    $qrCodeUrl = Storage::url($qrCodePath);
                                                } catch (\Exception $e) {
                                                    // Fallback to asset() if Storage::url() fails
                                                    $qrCodeUrl = asset('storage/' . $qrCodePath);
                                                }
                                            @endphp
                                            <img 
                                                src="{{ $qrCodeUrl }}" 
                                                alt="QR Code" 
                                                class="w-full h-full object-contain"
                                                onerror="console.error('QR Code failed to load:', this.src); this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                            >
                                            <div class="w-full h-full flex items-center justify-center" style="display: none;">
                                                <p class="text-sm text-gray-400">Unable to load QR Code</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-4 mt-2">
                                            <span class="text-xs font-semibold" style="color: #EF4444;">VIETQR</span>
                                            <span class="text-xs font-semibold" style="color: #2563EB;">napas 247</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="w-64 h-64 mx-auto flex items-center justify-center rounded-lg border" style="border-color: #E5E7EB; background-color: #F9FAFB;">
                                        <p class="text-sm text-gray-400">QR Code will be updated</p>
                                    </div>
                                @endif
                            @else
                                <!-- Payment Gateway Account Info -->
                                @if($method->account_number)
                                    <div class="p-4 rounded-lg border" style="border-color: #E5E7EB; background-color: #F9FAFB;">
                                        <div class="space-y-4">
                                            @if($method->type === 'worldfirst')
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm font-medium" style="color: #6B7280;">Account Number:</span>
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-sm font-semibold font-mono" style="color: #111827;">{{ $method->account_number }}</span>
                                                        <button 
                                                            type="button"
                                                            onclick="copyToClipboard('{{ $method->account_number }}', 'account-number-{{ $method->id }}')"
                                                            class="w-6 h-6 rounded flex items-center justify-center transition-all"
                                                            style="background-color: #DBEAFE; color: #2563EB;"
                                                            onmouseover="this.style.backgroundColor='#BFDBFE';"
                                                            onmouseout="this.style.backgroundColor='#DBEAFE';"
                                                            id="account-number-{{ $method->id }}"
                                                        >
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm font-medium" style="color: #6B7280;">Email:</span>
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-sm font-semibold" style="color: #111827;">{{ $method->account_number }}</span>
                                                        <button 
                                                            type="button"
                                                            onclick="copyToClipboard('{{ $method->account_number }}', 'account-number-{{ $method->id }}')"
                                                            class="w-6 h-6 rounded flex items-center justify-center transition-all"
                                                            style="background-color: #DBEAFE; color: #2563EB;"
                                                            onmouseover="this.style.backgroundColor='#BFDBFE';"
                                                            onmouseout="this.style.backgroundColor='#DBEAFE';"
                                                            id="account-number-{{ $method->id }}"
                                                        >
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                            @if($method->account_holder)
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm font-medium" style="color: #6B7280;">Account Holder:</span>
                                                    <span class="text-sm font-semibold" style="color: #111827;">{{ $method->account_holder }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Payment Gateway Instructions -->
                                <div class="p-4 rounded-lg border" style="border-color: #E5E7EB; background-color: #F9FAFB;">
                                    <h3 class="text-sm font-semibold mb-2" style="color: #111827;">Payment Instructions:</h3>
                                    <p class="text-sm text-gray-600">{{ $method->instructions }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Right Column: Form -->
                        <div>
                            <form method="POST" action="{{ route('customer.top-up-requests.store') }}" enctype="multipart/form-data" id="topup-form-{{ $method->slug }}">
                                @csrf
                                <input type="hidden" name="payment_method" value="{{ $method->type }}">
                                <input type="hidden" name="payment_method_id" value="{{ $method->id }}">
                                <input type="hidden" name="currency" value="{{ $method->currency }}">
                                <input type="hidden" name="transaction_code" id="transaction-code-{{ $method->slug }}" value="{{ $transactionCode }}">

                                <div class="space-y-6">
                                    <!-- Amount Input -->
                                    <div>
                                        <label class="block text-sm font-semibold mb-2" style="color: #111827;">
                                            Amount ({{ $method->currency }})
                                        </label>
                                        <input 
                                            type="number" 
                                            name="amount" 
                                            step="0.01" 
                                            min="{{ $method->min_amount }}"
                                            @if($method->max_amount) max="{{ $method->max_amount }}" @endif
                                            required
                                            class="w-full px-4 py-3 border rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                            style="border-color: #D1D5DB;"
                                            placeholder="0.00"
                                        >
                                        <p class="text-xs mt-1" style="color: #6B7280;">
                                            * Minimum top up amount is {{ $method->currency }} {{ number_format($method->min_amount, 2) }}
                                            @if($method->max_amount)
                                                . Maximum is {{ $method->currency }} {{ number_format($method->max_amount, 2) }}
                                            @endif
                                        </p>
                                    </div>

                                    <!-- Transfer Description / Transaction Code -->
                                    <div>
                                        <label class="block text-sm font-semibold mb-2" style="color: #111827;">
                                            Transfer Description
                                        </label>
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 px-4 py-3 rounded-lg border font-mono text-sm" style="border-color: #D1D5DB; background-color: #F9FAFB; color: #111827;" id="txn-code-display-{{ $method->slug }}">
                                                {{ $transactionCode }}
                                            </div>
                                            <button 
                                                type="button"
                                                onclick="copyTransactionCode('{{ $method->slug }}')"
                                                class="px-4 py-3 rounded-lg border transition-all flex items-center gap-2"
                                                style="border-color: #D1D5DB; color: #374151;"
                                                onmouseover="this.style.backgroundColor='#F3F4F6';"
                                                onmouseout="this.style.backgroundColor='transparent';"
                                                id="txn-code-btn-{{ $method->slug }}"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                </svg>
                                                Copy
                                            </button>
                                        </div>
                                        <div class="mt-2 flex items-start gap-2">
                                            <svg class="w-5 h-5 mt-0.5" style="color: #2563EB;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <p class="text-xs" style="color: #6B7280;">
                                                Please copy this transaction code and include it in your transfer to help us verify and track the payment.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Proof File -->
                                    <div>
                                        <label class="block text-sm font-semibold mb-2" style="color: #111827;">
                                            Proof of Payment <span class="text-red-500">*</span>
                                        </label>
                                        <input 
                                            type="file" 
                                            name="proof_file" 
                                            accept="image/*,.pdf"
                                            required
                                            class="w-full px-4 py-3 border rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                            style="border-color: #D1D5DB;"
                                        >
                                        <p class="text-xs mt-1" style="color: #6B7280;">Accept: JPG, PNG, PDF (max 5MB)</p>
                                    </div>

                                    <!-- Notes -->
                                    <div>
                                        <label class="block text-sm font-semibold mb-2" style="color: #111827;">
                                            Notes (Optional)
                                        </label>
                                        <textarea 
                                            name="notes" 
                                            rows="3"
                                            class="w-full px-4 py-3 border rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                            style="border-color: #D1D5DB;"
                                            placeholder="Additional information about the transaction..."
                                        ></textarea>
                                    </div>

                                    <!-- Submit Button -->
                                    <button 
                                        type="submit"
                                        class="w-full px-6 py-3 rounded-lg text-sm font-semibold text-white transition-all"
                                        style="background-color: #10B981;"
                                        onmouseover="this.style.backgroundColor='#059669';"
                                        onmouseout="this.style.backgroundColor='#10B981';"
                                    >
                                        Submit Top-up Request
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<script>
    function switchTab(slug) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });

        // Remove active state from all tabs
        document.querySelectorAll('.tab-button').forEach(button => {
            button.style.borderColor = 'transparent';
            button.style.color = '#6B7280';
        });

        // Show selected tab content
        const content = document.getElementById('content-' + slug);
        if (content) {
            content.classList.remove('hidden');
            
            // Generate new transaction code for this tab
            const newCode = generateTransactionCode();
            const codeInput = document.getElementById('transaction-code-' + slug);
            const codeDisplay = document.getElementById('txn-code-display-' + slug);
            if (codeInput) codeInput.value = newCode;
            if (codeDisplay) codeDisplay.textContent = newCode;
        }

        // Activate selected tab
        const tab = document.getElementById('tab-' + slug);
        if (tab) {
            tab.style.borderColor = '#10B981';
            tab.style.color = '#10B981';
        }
    }

    function generateTransactionCode() {
        return 'TXN-' + Date.now() + '-' + Math.floor(Math.random() * 900 + 100);
    }

    function copyTransactionCode(slug) {
        const input = document.getElementById('transaction-code-' + slug);
        const display = document.getElementById('txn-code-display-' + slug);
        const button = document.getElementById('txn-code-btn-' + slug);
        
        if (input && display) {
            const code = input.value || display.textContent.trim();
            navigator.clipboard.writeText(code).then(() => {
                if (button) {
                    const originalHTML = button.innerHTML;
                    button.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                    button.style.color = '#10B981';
                    setTimeout(() => {
                        button.innerHTML = originalHTML;
                        button.style.color = '';
                    }, 2000);
                }
            });
        }
    }

    function copyToClipboard(text, buttonId) {
        navigator.clipboard.writeText(text).then(() => {
            const button = document.getElementById(buttonId);
            if (button) {
                const originalHTML = button.innerHTML;
                button.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                button.style.color = '#10B981';
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.style.color = '';
                }, 2000);
            }
        });
    }

    // Initialize first tab as active
    document.addEventListener('DOMContentLoaded', function() {
        const firstTab = document.querySelector('.tab-button');
        if (firstTab) {
            switchTab(firstTab.getAttribute('data-method-slug'));
        }
    });
</script>
@endsection

@php
    $activeMenu = 'wallet';
@endphp
