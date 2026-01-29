@extends('layouts.admin-dashboard')

@section('title', 'Create Design Task - ' . config('app.name', 'Laravel'))

@section('header-title', 'Create Design Task')
@section('header-subtitle', 'Submit a new design request')

@section('content')
<div class="max-w-4xl mx-auto p-6 space-y-6">
    @if ($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <ul class="text-sm list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route($routePrefix . '.design-tasks.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Task Information -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-primary/5">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">description</span>
                    <h3 class="font-bold text-slate-900 dark:text-white">Task Information</h3>
                </div>
            </div>
            <div class="p-6 space-y-6">
                <!-- Title -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="title" 
                        value="{{ old('title') }}" 
                        placeholder="e.g., Black, L - Custom Design"
                        required
                        class="w-full px-4 py-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary transition-all"
                    >
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Enter a descriptive title for your design task</p>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        Description
                    </label>
                    <textarea 
                        name="description" 
                        rows="4"
                        placeholder="Describe your design requirements, specifications, and any special instructions..."
                        class="w-full px-4 py-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary transition-all resize-none"
                    >{{ old('description') }}</textarea>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Provide detailed information about your design needs</p>
                </div>

                <!-- Sides Count & Price -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            Number of Sides <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            name="sides_count" 
                            id="sides_count"
                            value="{{ old('sides_count', 1) }}" 
                            min="1"
                            max="10"
                            required
                            onchange="updateFileInputs(); calculatePrice();"
                            oninput="updateFileInputs(); calculatePrice();"
                            class="w-full px-4 py-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary transition-all"
                        >
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">How many sides need to be designed?</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            Price (USD) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400 font-semibold">$</span>
                            <input 
                                type="number" 
                                name="price" 
                                id="price"
                                value="{{ old('price') }}" 
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                required
                                readonly
                                class="w-full pl-8 pr-4 py-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary transition-all"
                            >
                        </div>
                        <div id="priceBreakdown" class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            <p id="priceVND" class="font-semibold"></p>
                            <p id="priceDetails" class="mt-1"></p>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Price is automatically calculated based on number of sides</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mockup Reference -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-primary/5">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">image</span>
                    <h3 class="font-bold text-slate-900 dark:text-white">Mockup Reference</h3>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        Upload Mockup Files <span class="text-slate-500 text-xs">(One file per side)</span>
                    </label>
                    <div id="mockupUploads" class="space-y-4">
                        <!-- File inputs will be dynamically generated here -->
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Upload reference mockups to help the designer understand your requirements. You can upload one file for each side.</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-between gap-4 pt-4">
            <a href="{{ route($routePrefix . '.design-tasks.index') }}" class="px-6 py-3 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-8 py-3 rounded-lg bg-primary text-white font-bold hover:bg-orange-600 shadow-lg shadow-primary/20 transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">add</span>
                Create Design Task
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const routePrefix = '{{ $routePrefix }}';
    
    function updateFileInputs() {
        const sidesCount = parseInt(document.getElementById('sides_count').value) || 1;
        const container = document.getElementById('mockupUploads');
        container.innerHTML = '';
        
        for (let i = 0; i < sidesCount; i++) {
            const sideNumber = i + 1;
            const fileInputId = `mockup_file_${i}`;
            const previewId = `preview_${i}`;
            const fileNameId = `file_name_${i}`;
            
            const fileInputDiv = document.createElement('div');
            fileInputDiv.className = 'border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-lg p-6 hover:border-primary transition-colors';
            fileInputDiv.innerHTML = `
                <div class="mb-2">
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">Side ${sideNumber} Mockup</label>
                </div>
                <input 
                    type="file" 
                    name="mockup_files[]" 
                    id="${fileInputId}"
                    accept="image/*,.pdf"
                    class="hidden"
                    onchange="handleFileSelect(this, ${i})"
                >
                <label for="${fileInputId}" class="cursor-pointer">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary text-2xl">cloud_upload</span>
                        </div>
                        <div class="text-center">
                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                Click to upload or drag and drop
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                PNG, JPG, PDF up to 10MB
                            </p>
                        </div>
                    </div>
                </label>
                <div id="${previewId}" class="mt-4 hidden">
                    <div class="relative inline-block">
                        <img id="previewImage_${i}" src="" alt="Preview" class="max-w-full max-h-48 rounded-lg border border-slate-200 dark:border-slate-700">
                        <button type="button" onclick="removeFile(${i})" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1.5 hover:bg-red-600 transition-colors">
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                    </div>
                    <p id="${fileNameId}" class="text-xs text-slate-500 dark:text-slate-400 mt-2"></p>
                </div>
            `;
            container.appendChild(fileInputDiv);
        }
    }
    
    function calculatePrice() {
        const sidesCount = parseInt(document.getElementById('sides_count').value) || 1;
        
        if (sidesCount < 1) {
            return;
        }

        fetch(`/${routePrefix}/design-tasks/calculate-price/${sidesCount}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('price').value = data.price_usd.toFixed(2);
                
                // Display VND price
                const priceVND = document.getElementById('priceVND');
                priceVND.textContent = `≈ ${data.price_vnd.toLocaleString('en-US')} VND`;
                
                // Display breakdown
                const priceDetails = document.getElementById('priceDetails');
                if (sidesCount === 1) {
                    priceDetails.textContent = `First side: 30,000 VND`;
                } else {
                    priceDetails.textContent = `First side: 30,000 VND + ${sidesCount - 1} side(s) × 20,000 VND = ${data.price_vnd.toLocaleString('en-US')} VND`;
                }
            }
        })
        .catch(error => {
            console.error('Error calculating price:', error);
        });
    }

    function handleFileSelect(input, index) {
        const file = input.files[0];
        if (file) {
            const previewDiv = document.getElementById(`preview_${index}`);
            const fileName = document.getElementById(`file_name_${index}`);
            const previewImage = document.getElementById(`previewImage_${index}`);
            
            fileName.textContent = file.name;
            
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewDiv.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                // For PDF or other files, show file name only
                previewImage.src = 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'200\'%3E%3Crect fill=\'%23f1f5f9\' width=\'200\' height=\'200\'/%3E%3Ctext fill=\'%2394a3b8\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' font-size=\'14\'%3E' + encodeURIComponent(file.name) + '%3C/text%3E%3C/svg%3E';
                previewDiv.classList.remove('hidden');
            }
        }
    }

    function removeFile(index) {
        const input = document.getElementById(`mockup_file_${index}`);
        const previewDiv = document.getElementById(`preview_${index}`);
        
        input.value = '';
        previewDiv.classList.add('hidden');
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateFileInputs();
        calculatePrice();
    });
</script>
@endpush
@endsection

@php
    $activeMenu = 'design-tasks';
@endphp
