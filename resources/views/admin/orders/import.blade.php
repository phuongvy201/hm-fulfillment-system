@extends('layouts.admin-dashboard')

@section('title', 'Import Orders - ' . config('app.name', 'Laravel'))

@section('header-title')
<div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
    <a href="{{ route($routePrefix . '.orders.index') }}" class="hover:text-[#F7961D] transition-colors">Orders</a>
    <span class="material-symbols-outlined text-xs">chevron_right</span>
    <span class="text-slate-900 font-medium">Import Orders</span>
</div>
<h2 class="text-2xl font-bold text-slate-900">Import Orders</h2>
@endsection

@section('header-subtitle', 'Bulk import orders from Excel file')

@section('header-actions')
<div class="flex items-center gap-3">
    @if(isset($routePrefix) && $routePrefix === 'admin')
    <a href="{{ route($routePrefix . '.orders.import-files') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
        View Import Files
    </a>
    @endif
    <a href="{{ route($routePrefix . '.orders.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
        Back to Orders
    </a>
</div>
@endsection

@section('content')
<div class="space-y-8">
    <!-- Success Message - Only show if no errors -->
    @if(session('success') && !session('error') && (!session('import_errors') || empty(session('import_errors'))))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    {{-- Only show session error if there are no import_errors (import_errors section will show the error message) --}}
    @if(session('error') && !session('import_errors'))
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    </div>
    @endif

    {{-- Only show validation errors if there are no import_errors --}}
    @if($errors->any() && !session('import_errors'))
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
        <div class="space-y-1">
            @foreach($errors->all() as $error)
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <span>{{ $error }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if(session('import_errors') && count(session('import_errors')) > 0)
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
        <div class="flex items-center gap-2 mb-3">
            <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <div class="flex-1">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-semibold">Import Errors ({{ count(session('import_errors')) }}):</h4>
                    @if(session('success_count') || session('error_count'))
                    <span class="text-sm">Success: {{ session('success_count', 0) }} | Errors: {{ session('error_count', 0) }}</span>
                    @endif
                </div>
                @if(session('error'))
                <div class="text-sm font-medium mb-2">{{ session('error') }}</div>
                @endif
            </div>
        </div>
        <div class="max-h-60 overflow-y-auto space-y-1 pl-7">
            @foreach(session('import_errors') as $error)
            <div class="text-sm">{{ $error }}</div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column (2/3 width) -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Upload Excel File Section -->
            <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-[#F7961D]/10 rounded-lg flex items-center justify-center">
                        <span class="material-symbols-outlined text-[#F7961D]">upload_file</span>
                    </div>
                        <h2 class="text-xl font-bold text-slate-900">Upload Excel File</h2>
                </div>
                <form method="POST" action="{{ route($routePrefix . '.orders.import.store') }}" enctype="multipart/form-data" id="importForm">
                    @csrf
                    
                    @if(isset($users) && $users && (auth()->user()->isSuperAdmin() || auth()->user()->hasRole('system-admin') || auth()->user()->hasRole('fulfillment-staff')))
                    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <label for="user_id" class="block text-sm font-semibold text-slate-900 mb-2">
                            <span class="material-symbols-outlined align-middle text-blue-600">person</span>
                            Assign Orders To User (Optional)
                        </label>
                        <select name="user_id" id="user_id" class="w-full rounded-lg border-slate-200 text-sm focus:border-[#F7961D] focus:ring-[#F7961D]">
                            <option value="">-- Auto-assign from Buyer Email in Excel --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500 mt-1">
                            If selected, all orders will be assigned to this user. Otherwise, orders will be assigned based on Buyer Email in Excel.
                        </p>
                    </div>
                    @endif
                    
                    <div class="border-2 border-dashed border-slate-200 rounded-xl p-10 flex flex-col items-center justify-center text-center bg-slate-50/50 group hover:border-[#F7961D] transition-colors">
                        <div class="mb-4 flex gap-4">
                            <label for="file" class="cursor-pointer bg-[#F7961D] hover:bg-[#E6851A] text-white px-6 py-3 rounded-xl font-bold transition-all shadow-lg shadow-[#F7961D]/20 flex items-center gap-2">
                                <span class="material-symbols-outlined">add_circle</span>
                                Select Excel File
                            </label>
                            <input type="file" name="file" id="file" accept=".xlsx,.xls" required class="hidden" onchange="document.getElementById('importForm').submit()">
                            <a href="{{ route($routePrefix . '.orders.import.sample') }}" class="bg-white border border-slate-200 hover:border-[#F7961D] text-slate-700 px-6 py-3 rounded-xl font-bold transition-all flex items-center gap-2">
                                <span class="material-symbols-outlined text-[#F7961D]">download</span>
                                Download Sample Excel
                            </a>
                        </div>
                        <p class="text-sm text-slate-500">
                            Max file size: <span class="font-semibold text-slate-700">10MB</span> • Supported formats: <span class="font-semibold text-slate-700">XLSX, XLS</span>
                        </p>
                    </div>
                </form>
            </section>

            <!-- Excel Format Guide Section -->
            <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-200 flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-500/10 rounded-lg flex items-center justify-center">
                        <span class="material-symbols-outlined text-blue-500">info</span>
                    </div>
                    <h2 class="text-xl font-bold text-slate-900">Excel Format Guide</h2>
                </div>
                <div class="p-6">
                    <!-- Tab Navigation -->
                    <div class="flex gap-1 p-1 bg-slate-100 rounded-xl mb-6">
                        <button class="tab-btn active flex-1 py-2 px-4 rounded-lg text-sm font-semibold transition-all flex items-center justify-center gap-2 bg-white shadow-sm text-slate-900" data-tab="tab-order" onclick="switchTab(event, 'tab-order')">
                            Order & Customer
                        </button>
                        <button class="tab-btn flex-1 py-2 px-4 rounded-lg text-sm font-semibold transition-all flex items-center justify-center gap-2 text-slate-500 hover:text-slate-900" data-tab="tab-product" onclick="switchTab(event, 'tab-product')">
                            Product & POD
                        </button>
                        <button class="tab-btn flex-1 py-2 px-4 rounded-lg text-sm font-semibold transition-all flex items-center justify-center gap-2 text-slate-500 hover:text-slate-900" data-tab="tab-shipping" onclick="switchTab(event, 'tab-shipping')">
                            Shipping & Label
                        </button>
                    </div>

                    <!-- Tab Content: Order & Customer -->
                    <div class="tab-content space-y-4" id="tab-order">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">External ID <span class="text-red-500">*</span></h4>
                                    <p class="text-xs text-slate-500">Unique order code (Shopify, Etsy, etc.)</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">Buyer Email</h4>
                                    <p class="text-xs text-slate-500">Customer contact email</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">Address 1 <span class="text-red-500">*</span></h4>
                                    <p class="text-xs text-slate-500">Main shipping address line</p>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">First/Last Name</h4>
                                    <p class="text-xs text-slate-500">Customer full name</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">City <span class="text-red-500">*</span></h4>
                                    <p class="text-xs text-slate-500">Destination city</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">Country <span class="text-red-500">*</span></h4>
                                    <p class="text-xs text-slate-500">ISO code (US, UK, VN...)</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Content: Product & POD -->
                    <div class="tab-content hidden space-y-4" id="tab-product">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">Part Number <span class="text-red-500">*</span></h4>
                                    <p class="text-xs text-slate-500">Product SKU or Variant code. For staff/admin: can be any SKU (manual entry allowed)</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">Quantity <span class="text-red-500">*</span></h4>
                                    <p class="text-xs text-slate-500">Integer value (minimum 1)</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">Total Amount</h4>
                                    <p class="text-xs text-slate-500">For staff/admin manual entry: total order amount in USD (optional if Part Number exists in database)</p>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">Design URL 1-5 <span class="text-red-500">*</span></h4>
                                    <p class="text-xs text-slate-500">Direct links to PNG or Google Drive files (up to 5 designs per item)</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">Mockup URL 1-5</h4>
                                    <p class="text-xs text-slate-500">Links to mockup previews (optional, up to 5 per item)</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                            <p class="text-xs text-amber-800 font-medium mb-2">
                                <strong>Position Mapping:</strong> Each Position (1-5) corresponds to its respective Mockup and Design URL index.
                            </p>
                            <p class="text-xs text-amber-700">
                                <strong>Example:</strong> Position 1 → Design Url 1 → Mockup Url 1. You can use up to 5 design/mockup combinations per product item.
                            </p>
                        </div>
                        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-xs text-blue-800 font-medium mb-1">
                                <strong>Note for Staff/Admin:</strong>
                            </p>
                            <ul class="text-xs text-blue-700 list-disc list-inside space-y-1">
                                <li>Part Number can be any SKU (doesn't need to exist in database)</li>
                                <li>If Total Amount is provided, it will be used instead of calculating from product prices</li>
                                <li>Currency is fixed as USD for manual entry orders</li>
                                <li>Buyer Email is optional - orders can be created without assigning to a customer</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Tab Content: Shipping & Label -->
                    <div class="tab-content hidden space-y-4" id="tab-shipping">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">Shipping Method</h4>
                                    <p class="text-xs text-slate-500">e.g., standard, express, tiktok_label</p>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">Label Type</h4>
                                    <p class="text-xs text-slate-500">Optional custom label identifier</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Full CSV Header List Section -->
            <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-slate-900/10 rounded-lg flex items-center justify-center">
                            <span class="material-symbols-outlined">data_object</span>
                        </div>
                        <h2 class="text-xl font-bold text-slate-900">Full Excel Header List</h2>
                    </div>
                    <button class="text-xs font-bold text-[#F7961D] hover:text-[#E6851A] flex items-center gap-1 bg-[#F7961D]/5 px-3 py-2 rounded-lg transition-all" onclick="copyToClipboard()">
                        <span class="material-symbols-outlined text-sm">content_copy</span>
                        Copy to Clipboard
                    </button>
                </div>
                <div class="bg-slate-900 p-5 rounded-xl border border-slate-800">
                    <code class="font-mono text-sm text-green-400 break-all leading-relaxed" id="excel-headers">External ID,Brand,Channel,Comment,Buyer Email,First Name,Last Name,Company,Phone 1,Phone 2,Address 1,Address 2,City,County,Postcode,Country,Shipping Method,Label Name,Label Type,Label Url,Part Number,Title,Quantity,Description,Total Amount,Position 1,Position 2,Position 3,Position 4,Position 5,Mockup Url 1,Mockup Url 2,Mockup Url 3,Mockup Url 4,Mockup Url 5,Design Url 1,Design Url 2,Design Url 3,Design Url 4,Design Url 5</code>
                </div>
            </section>
        </div>

        <!-- Right Column (1/3 width) -->
        <div class="space-y-6">
            <!-- Required Fields -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-md font-bold mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-red-500 text-lg">priority_high</span>
                    Required Fields
                </h3>
                <ul class="space-y-3">
                    <li class="flex items-center gap-3 text-sm text-slate-600">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                        External ID
                    </li>
                    <li class="flex items-center gap-3 text-sm text-slate-600">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                        Address 1
                    </li>
                    <li class="flex items-center gap-3 text-sm text-slate-600">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                        City & Country
                    </li>
                    <li class="flex items-center gap-3 text-sm text-slate-600">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                        Part Number
                    </li>
                    <li class="flex items-center gap-3 text-sm text-slate-600">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                        Quantity
                    </li>
                    <li class="flex items-center gap-3 text-sm text-slate-600">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                        Design URL 1
                    </li>
                </ul>
            </div>

            <!-- Pro Tips -->
            <div class="bg-indigo-600 rounded-2xl p-6 text-white shadow-lg shadow-indigo-600/20">
                <div class="flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined">lightbulb</span>
                    <h3 class="font-bold">Pro Tips</h3>
                </div>
                <div class="space-y-4 text-indigo-100 text-sm">
                    <div class="flex gap-3">
                        <span class="material-symbols-outlined text-indigo-300 text-lg">check_circle</span>
                        <p><strong>Unique IDs:</strong> Duplicate External IDs will update existing orders instead of creating new ones.</p>
                    </div>
                    <div class="flex gap-3">
                        <span class="material-symbols-outlined text-indigo-300 text-lg">layers</span>
                        <p><strong>One Row = One Item:</strong> Multiple rows with the same External ID will be grouped into a single order.</p>
                    </div>
                    <div class="flex gap-3">
                        <span class="material-symbols-outlined text-indigo-300 text-lg">link</span>
                        <p><strong>Cloud Links:</strong> We support direct PNG links and public Google Drive share links.</p>
                    </div>
                </div>
            </div>

            <!-- Warning -->
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6">
                <h3 class="text-amber-800 font-bold mb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">warning</span>
                    Warning
                </h3>
                <p class="text-sm text-amber-700">
                    Please ensure all design files are in <span class="font-bold underline">PNG format</span> for the best print quality.
                </p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function switchTab(event, tabId) {
        // Hide all contents
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        // Show selected content
        document.getElementById(tabId).classList.remove('hidden');
        // Reset button styles
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('bg-white', 'shadow-sm', 'text-slate-900');
            btn.classList.add('text-slate-500');
        });
        // Highlight active button
        const activeBtn = event.currentTarget;
        activeBtn.classList.remove('text-slate-500');
        activeBtn.classList.add('bg-white', 'shadow-sm', 'text-slate-900');
    }

    function copyToClipboard() {
        const headers = document.getElementById('excel-headers').innerText;
        navigator.clipboard.writeText(headers).then(() => {
            const btn = event.currentTarget;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="material-symbols-outlined text-sm">check</span> Copied!';
            btn.classList.add('text-green-500');
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.remove('text-green-500');
            }, 2000);
        });
    }
</script>
@endpush

@php
    $activeMenu = 'orders';
@endphp

