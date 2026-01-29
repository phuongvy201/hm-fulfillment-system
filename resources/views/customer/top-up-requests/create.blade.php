@extends('layouts.admin-dashboard')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Top-Up Balance - ' . config('app.name', 'Laravel'))

@section('header-title', 'Top-Up Balance')
@section('header-subtitle', 'Select your preferred method to fund your account.')

@section('content')
<div class="max-w-6xl mx-auto">
        <!-- Tabs Navigation -->
    <div class="flex flex-wrap gap-2 mb-8 bg-slate-100 dark:bg-slate-800/50 p-1 rounded-xl w-fit">
        @foreach($paymentMethods as $index => $method)
                    <button 
                        type="button"
                        onclick="switchTab('{{ $method->slug }}')"
                        id="tab-{{ $method->slug }}"
                class="px-5 py-2.5 rounded-lg text-sm font-medium transition-colors tab-button {{ $index === 0 ? 'bg-white dark:bg-slate-700 shadow-sm text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}"
                        data-method-slug="{{ $method->slug }}"
                    >
                        {{ $method->name }}
                    </button>
                @endforeach
        </div>

            @foreach($paymentMethods as $index => $method)
                <div id="content-{{ $method->slug }}" class="tab-content {{ $index === 0 ? '' : 'hidden' }}" data-method-id="{{ $method->id }}">
                    <!-- Exchange Rate (for Bank Vietnam) -->
                    @if($method->type === 'bank_transfer' && $method->exchange_rate)
                <div class="mb-8 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/50 rounded-xl p-4 flex items-center gap-3">
                    <div class="bg-blue-500 text-white rounded-full p-1 flex">
                        <span class="material-symbols-outlined text-[18px]">currency_exchange</span>
                    </div>
                    <p class="text-sm font-medium text-blue-700 dark:text-blue-400">
                        Exchange rate today: <span class="font-bold">$1.00 = {{ number_format($method->exchange_rate, 0) }}₫</span>
                            </p>
                        </div>
                    @endif

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                <!-- Left Column: Bank Info / QR Code (2 columns) -->
                <div class="lg:col-span-2 space-y-6">
                            @if($method->type === 'bank_transfer')
                        <!-- Bank Information Card -->
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-sm">
                            <div class="bg-slate-50 dark:bg-slate-800/50 p-4 border-b border-slate-200 dark:border-slate-700">
                                <h3 class="font-bold flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary">account_balance</span>
                                    Bank Information
                                </h3>
                                        </div>
                            <div class="p-6 space-y-6">
                                <!-- QR Code -->
                                @if($method->qr_code)
                                    <div class="flex flex-col items-center">
                                        <div class="bg-white p-4 rounded-2xl shadow-inner border border-slate-100">
                                            @php
                                                $qrCodePath = $method->qr_code;
                                                if (str_starts_with($qrCodePath, 'storage/')) {
                                                    $qrCodePath = str_replace('storage/', '', $qrCodePath);
                                                }
                                                try {
                                                    $qrCodeUrl = Storage::url($qrCodePath);
                                                } catch (\Exception $e) {
                                                    $qrCodeUrl = asset('storage/' . $qrCodePath);
                                                }
                                            @endphp
                                            <img 
                                                src="{{ $qrCodeUrl }}" 
                                                alt="QR Code" 
                                                class="w-48 h-48 object-contain"
                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                            >
                                            <div class="w-full h-full flex items-center justify-center" style="display: none;">
                                                <p class="text-sm text-gray-400">Unable to load QR Code</p>
                                            </div>
                                        </div>
                                        <div class="mt-4 text-center">
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Scan with Banking App</p>
                                            <div class="flex items-center gap-2 mt-1 justify-center">
                                                <span class="text-xs font-bold text-blue-600">VIETQR</span>
                                                <span class="text-xs font-bold text-red-600">napas 247</span>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="w-48 h-48 mx-auto flex items-center justify-center rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                                        <p class="text-sm text-gray-400">QR Code will be updated</p>
                                    </div>
                                @endif

                                <!-- Bank Details -->
                                <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-slate-500 font-medium">Bank Name</span>
                                        <span class="text-sm font-bold">{{ $method->bank_name }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-slate-500 font-medium">Account Number</span>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-bold font-mono">{{ $method->account_number }}</span>
                                            <button 
                                                type="button"
                                                onclick="copyToClipboard('{{ $method->account_number }}', 'account-number-{{ $method->id }}')"
                                                class="p-1 hover:bg-slate-100 dark:hover:bg-slate-800 rounded transition-colors text-primary"
                                                id="account-number-{{ $method->id }}"
                                            >
                                                <span class="material-symbols-outlined text-[18px]">content_copy</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-slate-500 font-medium">Account Holder</span>
                                        <span class="text-sm font-bold uppercase">{{ $method->account_holder }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="bg-orange-50 dark:bg-orange-900/10 border border-orange-100 dark:border-orange-900/30 p-4 rounded-xl">
                            <div class="flex gap-3">
                                <span class="material-symbols-outlined text-orange-500 text-sm">info</span>
                                <p class="text-xs text-orange-700 dark:text-orange-400 leading-relaxed">
                                    Funds are typically credited to your account within 15-30 minutes after verification. If you face any issues, please contact support.
                                </p>
                            </div>
                        </div>
                            @else
                                <!-- Payment Gateway Account Info -->
                                @if($method->account_number)
                            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm">
                                <h3 class="font-bold flex items-center gap-2 mb-4">
                                    <span class="material-symbols-outlined text-primary">account_balance</span>
                                    Account Information
                                </h3>
                                        <div class="space-y-4">
                                            @if($method->type === 'worldfirst')
                                                <div class="flex items-center justify-between">
                                            <span class="text-xs text-slate-500 font-medium">Account Number:</span>
                                                    <div class="flex items-center gap-2">
                                                <span class="text-sm font-bold font-mono">{{ $method->account_number }}</span>
                                                        <button 
                                                            type="button"
                                                            onclick="copyToClipboard('{{ $method->account_number }}', 'account-number-{{ $method->id }}')"
                                                    class="p-1 hover:bg-slate-100 dark:hover:bg-slate-800 rounded transition-colors text-primary"
                                                            id="account-number-{{ $method->id }}"
                                                        >
                                                    <span class="material-symbols-outlined text-[18px]">content_copy</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="flex items-center justify-between">
                                            <span class="text-xs text-slate-500 font-medium">Email:</span>
                                                    <div class="flex items-center gap-2">
                                                <span class="text-sm font-bold">{{ $method->account_number }}</span>
                                                        <button 
                                                            type="button"
                                                            onclick="copyToClipboard('{{ $method->account_number }}', 'account-number-{{ $method->id }}')"
                                                    class="p-1 hover:bg-slate-100 dark:hover:bg-slate-800 rounded transition-colors text-primary"
                                                            id="account-number-{{ $method->id }}"
                                                        >
                                                    <span class="material-symbols-outlined text-[18px]">content_copy</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                            @if($method->account_holder)
                                                <div class="flex items-center justify-between">
                                            <span class="text-xs text-slate-500 font-medium">Account Holder:</span>
                                            <span class="text-sm font-bold">{{ $method->account_holder }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Payment Gateway Instructions -->
                        @if($method->instructions)
                            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm">
                                <h3 class="font-bold flex items-center gap-2 mb-4">
                                    <span class="material-symbols-outlined text-primary">description</span>
                                    Payment Instructions
                                </h3>
                                <p class="text-sm text-slate-600 dark:text-slate-400">{{ $method->instructions }}</p>
                                </div>
                        @endif
                            @endif
                        </div>

                <!-- Right Column: Form (3 columns) -->
                <div class="lg:col-span-3">
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-8 shadow-sm">
                        <h3 class="text-xl font-bold mb-6 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">description</span>
                            Top-up Details
                        </h3>
                        <form method="POST" action="{{ route('customer.top-up-requests.store') }}" enctype="multipart/form-data" id="topup-form-{{ $method->slug }}" class="space-y-6">
                                @csrf
                                <input type="hidden" name="payment_method" value="{{ $method->type }}">
                                <input type="hidden" name="payment_method_id" value="{{ $method->id }}">
                                <input type="hidden" name="currency" value="{{ $method->currency }}">
                                <input type="hidden" name="transaction_code" id="transaction-code-{{ $method->slug }}" value="{{ $transactionCode }}">

                                    <!-- Amount Input -->
                                    <div>
                                <label class="block text-sm font-semibold mb-2">
                                    Amount ({{ $method->currency }}) <span class="text-red-500">*</span>
                                        </label>
                                <div class="relative group">
                                    @if($method->currency === 'USD')
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-medium">$</span>
                                    @endif
                                        <input 
                                            type="number" 
                                            name="amount" 
                                            step="0.01" 
                                            min="{{ $method->min_amount }}"
                                            @if($method->max_amount) max="{{ $method->max_amount }}" @endif
                                            required
                                        class="w-full {{ $method->currency === 'USD' ? 'pl-8' : 'pl-4' }} pr-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-lg font-medium outline-none"
                                            placeholder="0.00"
                                        >
                                </div>
                                <p class="mt-2 text-xs text-slate-500 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">error_outline</span>
                                    Minimum top-up amount is {{ $method->currency }} {{ number_format($method->min_amount, 2) }}
                                            @if($method->max_amount)
                                                . Maximum is {{ $method->currency }} {{ number_format($method->max_amount, 2) }}
                                            @endif
                                        </p>
                                    </div>

                                    <!-- Transfer Description / Transaction Code -->
                                    <div>
                                <label class="block text-sm font-semibold mb-2">
                                            Transfer Description
                                        </label>
                                <div class="flex gap-2">
                                    <input 
                                        type="text" 
                                        readonly
                                        class="flex-1 px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono text-sm text-slate-600 dark:text-slate-300 outline-none" 
                                        id="txn-code-display-{{ $method->slug }}"
                                        value="{{ $transactionCode }}"
                                    >
                                            <button 
                                                type="button"
                                                onclick="copyTransactionCode('{{ $method->slug }}')"
                                        class="px-5 py-3 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-xl font-semibold text-sm flex items-center gap-2 transition-all"
                                                id="txn-code-btn-{{ $method->slug }}"
                                            >
                                        <span class="material-symbols-outlined text-[20px]">content_copy</span>
                                                Copy
                                            </button>
                                        </div>
                                <p class="mt-2 text-[11px] text-slate-500 flex items-start gap-1">
                                    <span class="material-symbols-outlined text-[14px] mt-0.5">info</span>
                                    Please copy this exact transaction code and include it in your transfer note to help us verify and track the payment instantly.
                                            </p>
                                    </div>

                                    <!-- Proof File -->
                                    <div>
                                <label class="block text-sm font-semibold mb-2">
                                            Proof of Payment <span class="text-red-500">*</span>
                                        </label>
                                <div class="border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-2xl p-8 flex flex-col items-center justify-center bg-slate-50/50 dark:bg-slate-800/20 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors cursor-pointer group" onclick="document.getElementById('proof-file-{{ $method->slug }}').click()">
                                    <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary mb-3 group-hover:scale-110 transition-transform">
                                        <span class="material-symbols-outlined text-[32px]">cloud_upload</span>
                                    </div>
                                    <p class="text-sm font-medium">Click to upload or drag and drop</p>
                                    <p class="text-xs text-slate-500 mt-1">Accept: JPG, PNG, PDF (max 5MB)</p>
                                        <input 
                                            type="file" 
                                            name="proof_file" 
                                        id="proof-file-{{ $method->slug }}"
                                            accept="image/*,.pdf"
                                            required
                                        class="hidden"
                                        onchange="handleFileSelect(this, '{{ $method->slug }}')"
                                        >
                                </div>
                                <div id="file-name-{{ $method->slug }}" class="mt-2 text-sm text-slate-600 dark:text-slate-400 hidden"></div>
                                    </div>

                                    <!-- Notes -->
                                    <div>
                                <label class="block text-sm font-semibold mb-2">
                                            Notes (Optional)
                                        </label>
                                        <textarea 
                                            name="notes" 
                                            rows="3"
                                    class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none resize-none"
                                            placeholder="Additional information about the transaction..."
                                        ></textarea>
                                    </div>

                                    <!-- Submit Button -->
                                    <button 
                                        type="submit"
                                class="w-full bg-primary hover:bg-orange-600 text-white font-bold py-4 rounded-xl shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2 active:scale-[0.98]"
                                    >
                                <span>Submit Top-up Request</span>
                                <span class="material-symbols-outlined">arrow_forward</span>
                                    </button>
                            </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@push('scripts')
<script>
    function switchTab(slug) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });

        // Remove active state from all tabs
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('bg-white', 'dark:bg-slate-700', 'shadow-sm', 'text-primary', 'font-semibold');
            button.classList.add('text-slate-600', 'dark:text-slate-400', 'hover:text-slate-900', 'dark:hover:text-white');
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
            if (codeDisplay) codeDisplay.value = newCode;
        }

        // Activate selected tab
        const tab = document.getElementById('tab-' + slug);
        if (tab) {
            tab.classList.remove('text-slate-600', 'dark:text-slate-400', 'hover:text-slate-900', 'dark:hover:text-white');
            tab.classList.add('bg-white', 'dark:bg-slate-700', 'shadow-sm', 'text-primary', 'font-semibold');
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
            const code = input.value || display.value.trim();
            navigator.clipboard.writeText(code).then(() => {
                if (button) {
                    const originalHTML = button.innerHTML;
                    button.innerHTML = '<span class="material-symbols-outlined text-[20px]">check</span>';
                    button.classList.add('text-green-500');
                    setTimeout(() => {
                        button.innerHTML = originalHTML;
                        button.classList.remove('text-green-500');
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
                button.innerHTML = '<span class="material-symbols-outlined text-[18px]">check</span>';
                button.classList.add('text-green-500');
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.classList.remove('text-green-500');
                }, 2000);
            }
        });
    }

    function handleFileSelect(input, slug) {
        const fileName = input.files[0]?.name;
        const fileNameDiv = document.getElementById('file-name-' + slug);
        if (fileName) {
            fileNameDiv.textContent = 'Selected: ' + fileName;
            fileNameDiv.classList.remove('hidden');
        } else {
            fileNameDiv.classList.add('hidden');
        }
    }

    // Initialize first tab as active
    document.addEventListener('DOMContentLoaded', function() {
        const firstTab = document.querySelector('.tab-button');
        if (firstTab) {
            switchTab(firstTab.getAttribute('data-method-slug'));
        }
    });
</script>
@endpush
@endsection

@php
    $activeMenu = 'wallet';
@endphp
