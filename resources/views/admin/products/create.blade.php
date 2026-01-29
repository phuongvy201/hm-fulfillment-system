@extends('layouts.admin-dashboard')

@section('title', 'Create Product - ' . config('app.name', 'Laravel'))

@section('header-title', 'Create New Product')
@section('header-subtitle', 'Add a new product to the system')

@section('header-actions')
<a href="{{ route('admin.products.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
    ← Back to Products
</a>
@endsection

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8 pb-32">
    @if ($errors->any())
        <div class="mb-6 bg-white rounded-xl shadow-sm p-6 border" style="border-color: #E2E8F0;">
            <div class="p-4 rounded-lg" style="background-color: #FEE2E2; border: 1px solid #EF4444;">
                <ul class="text-sm" style="color: #991B1B;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" id="product-form">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Main Form -->
            <div class="lg:col-span-2 space-y-8">
                <!-- General Information -->
                <section class="bg-white rounded-xl shadow-sm p-6 border" style="border-color: #E2E8F0;">
                    <h2 class="text-lg font-semibold mb-6" style="color: #0F172A;">Information</h2>
                    <div class="space-y-5">
                        <div>
                            <label for="name" class="block text-sm font-medium mb-2" style="color: #334155;">Product Name <span class="text-red-500">*</span></label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                value="{{ old('name') }}"
                                required 
                                autofocus
                                placeholder="VD: T-shirt, Hoodie, Mug"
                                class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                style="border-color: #CBD5E1; color: #0F172A; background-color: #FFFFFF;"
                                onfocus="this.style.borderColor='#F7961D'; this.style.boxShadow='0 0 0 3px rgba(247, 150, 29, 0.1)';"
                                onblur="this.style.borderColor='#CBD5E1'; this.style.boxShadow='none';"
                            >
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="sku" class="block text-sm font-medium mb-2" style="color: #334155;">SKU (Optional)</label>
                                <input 
                                    type="text" 
                                    id="sku" 
                                    name="sku" 
                                    value="{{ old('sku') }}"
                                    placeholder="Leave empty to auto-generate"
                                    class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                    style="border-color: #CBD5E1; color: #0F172A; background-color: #FFFFFF;"
                                    onfocus="this.style.borderColor='#F7961D'; this.style.boxShadow='0 0 0 3px rgba(247, 150, 29, 0.1)';"
                                    onblur="this.style.borderColor='#CBD5E1'; this.style.boxShadow='none';"
                                >
                            </div>
                            <div>
                                <label for="workshop_id" class="block text-sm font-medium mb-2" style="color: #334155;">Workshop <span class="text-red-500">*</span></label>
                                <select 
                                    id="workshop_id" 
                                    name="workshop_id"
                                    required
                                    class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                    style="border-color: #CBD5E1; color: #0F172A; background-color: #FFFFFF;"
                                    onfocus="this.style.borderColor='#F7961D'; this.style.boxShadow='0 0 0 3px rgba(247, 150, 29, 0.1)';"
                                    onblur="this.style.borderColor='#CBD5E1'; this.style.boxShadow='none';"
                                >
                                            <option value="">Select Workshop</option>
                                    @foreach($workshops as $workshop)
                                        <option value="{{ $workshop->id }}" {{ old('workshop_id') == $workshop->id ? 'selected' : '' }}>
                                            {{ $workshop->name }} ({{ $workshop->code }}) - {{ $workshop->market->name ?? 'N/A' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium mb-2" style="color: #334155;">Description</label>
                            <textarea 
                                id="description" 
                                name="description" 
                                rows="5"
                                placeholder="Product description..."
                                class="w-full px-4 py-2.5 rounded-lg border transition-all resize-none focus:outline-none focus:ring-2"
                                style="border-color: #CBD5E1; color: #0F172A; background-color: #FFFFFF;"
                                onfocus="this.style.borderColor='#F7961D'; this.style.boxShadow='0 0 0 3px rgba(247, 150, 29, 0.1)';"
                                onblur="this.style.borderColor='#CBD5E1'; this.style.boxShadow='none';"
                            >{{ old('description') }}</textarea>
                        </div>
                    </div>
                </section>

                <!-- SKU Templates -->
                <section class="bg-white rounded-xl shadow-sm p-6 border" style="border-color: #E2E8F0;">
                    <h2 class="text-lg font-semibold mb-6" style="color: #0F172A;">SKU Configuration</h2>
                    <div class="space-y-6">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label for="sku_template" class="text-sm font-medium" style="color: #334155;">Template SKU Internal <span class="text-red-500">*</span></label>
                            </div>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="sku_template" 
                                    name="sku_template" 
                                    value="{{ old('sku_template') }}"
                                    placeholder="VD: T004-{COLOR_CODE}-{SIZE}-US"
                                    class="w-full font-mono text-sm rounded-lg border p-3 transition-all focus:outline-none focus:ring-2"
                                    style="border-color: #CBD5E1; color: #0F172A; background-color: #F8FAFC;"
                                    onfocus="this.style.borderColor='#F7961D'; this.style.boxShadow='0 0 0 3px rgba(247, 150, 29, 0.1)';"
                                    onblur="this.style.borderColor='#CBD5E1'; this.style.boxShadow='none';"
                            >
                            </div>
                            <p class="mt-2 text-xs flex flex-wrap gap-1" style="color: #64748B;">
                                Available variables: 
                                <span class="variable-tag px-2 py-0.5 rounded text-xs font-mono" style="background-color: #F1F5F9; color: #475569;">{COLOR_CODE}</span>
                                <span class="variable-tag px-2 py-0.5 rounded text-xs font-mono" style="background-color: #F1F5F9; color: #475569;">{COLOR}</span>
                                <span class="variable-tag px-2 py-0.5 rounded text-xs font-mono" style="background-color: #F1F5F9; color: #475569;">{SIZE}</span>
                                <span class="variable-tag px-2 py-0.5 rounded text-xs font-mono" style="background-color: #F1F5F9; color: #475569;">{MARKET_CODE}</span>
                                <span class="variable-tag px-2 py-0.5 rounded text-xs font-mono" style="background-color: #F1F5F9; color: #475569;">{WORKSHOP_CODE}</span>
                            </p>
                        </div>
                        <div>
                            <label for="workshop_sku_template" class="block text-sm font-medium mb-2" style="color: #334155;">Workshop SKU Template</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="workshop_sku_template" 
                                    name="workshop_sku_template" 
                                    value="{{ old('workshop_sku_template') }}"
                                    placeholder="e.g., COMFORT {SIZE}/ {COLOR_CODE}"
                                    class="w-full font-mono text-sm rounded-lg border p-3 transition-all focus:outline-none focus:ring-2"
                                    style="border-color: #CBD5E1; color: #0F172A; background-color: #F8FAFC;"
                                    onfocus="this.style.borderColor='#F7961D'; this.style.boxShadow='0 0 0 3px rgba(247, 150, 29, 0.1)';"
                                    onblur="this.style.borderColor='#CBD5E1'; this.style.boxShadow='none';"
                                >
                            </div>
                            <p class="mt-2 text-xs flex flex-wrap gap-1" style="color: #64748B;">
                                Available variables: 
                                <span class="variable-tag px-2 py-0.5 rounded text-xs font-mono" style="background-color: #F1F5F9; color: #475569;">{WORKSHOP_SKU_CODE}</span>
                                <span class="variable-tag px-2 py-0.5 rounded text-xs font-mono" style="background-color: #F1F5F9; color: #475569;">{COLOR_CODE}</span>
                                <span class="variable-tag px-2 py-0.5 rounded text-xs font-mono" style="background-color: #F1F5F9; color: #475569;">{SIZE}</span>
                            </p>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Right Column: Sidebar -->
            <div class="space-y-8">
                <!-- Product Status -->
                <section class="bg-white rounded-xl shadow-sm p-6 border" style="border-color: #E2E8F0;">
                    <h2 class="text-lg font-semibold mb-6" style="color: #0F172A;">Product Status</h2>
                    <select 
                        id="status" 
                        name="status"
                        required
                        class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                        style="border-color: #CBD5E1; color: #0F172A; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#F7961D'; this.style.boxShadow='0 0 0 3px rgba(247, 150, 29, 0.1)';"
                        onblur="this.style.borderColor='#CBD5E1'; this.style.boxShadow='none';"
                    >
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </section>

                <!-- Media Management -->
                <section class="bg-white rounded-xl shadow-sm p-6 border" style="border-color: #E2E8F0;">
                    <h2 class="text-lg font-semibold mb-6" style="color: #0F172A;">Image Management</h2>
                    
                    <!-- Image Grid -->
                    <div id="image-preview" class="grid grid-cols-2 gap-4 mb-6"></div>
                    
                    <!-- Upload Zone -->
                    <div 
                        id="drop-zone" 
                        class="border-2 border-dashed rounded-xl p-8 flex flex-col items-center justify-center text-center transition-all cursor-pointer"
                        style="border-color: #CBD5E1;"
                        onmouseover="this.style.borderColor='#F7961D'; this.style.backgroundColor='rgba(247, 150, 29, 0.05)';"
                        onmouseout="this.style.borderColor='#CBD5E1'; this.style.backgroundColor='transparent';"
                    >
                        <input 
                            type="file" 
                            id="images" 
                            name="images[]" 
                            multiple
                            accept="image/*"
                            class="hidden"
                        >
                        <div class="w-12 h-12 rounded-full flex items-center justify-center mb-3" style="background-color: #FED7AA;">
                            <svg class="w-6 h-6" style="color: #F7961D;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                        </div>
                        <h4 class="text-sm font-semibold mb-1" style="color: #0F172A;">Upload New Image</h4>
                        <p class="text-xs mb-2" style="color: #64748B;">Drag & drop or click to select</p>
                        <p class="text-[10px]" style="color: #94A3B8;">JPG, PNG, WEBP (Tối đa 5MB)</p>
                    </div>
                </section>
            </div>
        </div>
    </form>
</div>

<!-- Fixed Footer -->
<footer class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg px-4 py-4 z-40" style="border-color: #E2E8F0;">
    <div class="max-w-6xl mx-auto flex items-center justify-between">
        <div class="hidden md:block text-sm" style="color: #64748B;">
            Create New Product
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <a 
                href="{{ route('admin.products.index') }}" 
                class="flex-1 md:flex-none px-6 py-2.5 text-sm font-semibold rounded-lg transition-colors"
                style="color: #475569; background-color: #F1F5F9;"
                onmouseover="this.style.backgroundColor='#E2E8F0';"
                onmouseout="this.style.backgroundColor='#F1F5F9';"
            >
                Cancel
            </a>
            <button 
                type="submit"
                form="product-form"
                class="flex-1 md:flex-none px-8 py-2.5 text-sm font-semibold text-white rounded-lg shadow-md transition-all transform active:scale-95"
                style="background-color: #F7961D; box-shadow: 0 4px 6px -1px rgba(247, 150, 29, 0.2);"
                onmouseover="this.style.backgroundColor='#E6891A';"
                onmouseout="this.style.backgroundColor='#F7961D';"
            >
                Create Product
            </button>
        </div>
    </div>
</footer>
@endsection

@push('styles')
<style>
    .image-card:hover .image-actions {
        opacity: 1 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    let uploadedImages = [];
    let primaryImageIndex = 0;

    // Drag and Drop functionality
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('images');
    const imagePreview = document.getElementById('image-preview');

    // Click to select files
    dropZone.addEventListener('click', () => fileInput.click());

    // Drag and drop events
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = '#F7961D';
        dropZone.style.backgroundColor = '#FFFBF5';
    });

    dropZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = '#D1D5DB';
        dropZone.style.backgroundColor = '#FAFAFA';
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = '#D1D5DB';
        dropZone.style.backgroundColor = '#FAFAFA';
        
        const files = Array.from(e.dataTransfer.files).filter(file => file.type.startsWith('image/'));
        handleFiles(files);
    });

    // File input change
    fileInput.addEventListener('change', (e) => {
        const files = Array.from(e.target.files);
        handleFiles(files);
    });

    // Handle uploaded files
    function handleFiles(files) {
        files.forEach((file, index) => {
            if (file.size > 5 * 1024 * 1024) {
                alert(`File ${file.name} exceeds 5MB. Please select a different file.`);
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const imageData = {
                    id: Date.now() + index,
                    src: e.target.result,
                    file: file,
                    isPrimary: uploadedImages.length === 0
                };
                
                uploadedImages.push(imageData);
                if (imageData.isPrimary) {
                    primaryImageIndex = uploadedImages.length - 1;
                }
                
                renderImageGrid();
                updateFileInput();
            };
            reader.readAsDataURL(file);
        });
    }

    // Render image grid
    function renderImageGrid() {
        imagePreview.innerHTML = '';
        
        uploadedImages.forEach((image, index) => {
            const div = document.createElement('div');
            div.className = 'image-card relative group rounded-lg overflow-hidden';
            div.style.border = image.isPrimary ? '2px solid #F7961D' : '1px solid #E2E8F0';
            div.style.boxShadow = image.isPrimary ? '0 0 0 2px rgba(247, 150, 29, 0.1)' : 'none';
            
            div.innerHTML = `
                <img src="${image.src}" alt="Preview" class="w-full h-32 object-cover" style="${!image.isPrimary ? 'filter: grayscale(0.2);' : ''}">
                ${image.isPrimary ? `
                    <div class="absolute top-2 left-2 px-2 py-0.5 text-[10px] font-bold text-white rounded uppercase tracking-wider" style="background-color: #F7961D;">
                        Primary
                    </div>
                ` : ''}
                <div class="image-actions absolute inset-0 flex items-center justify-center gap-2 opacity-0 transition-opacity" style="background-color: rgba(0, 0, 0, 0.4);">
                    ${!image.isPrimary ? `
                        <button 
                            type="button"
                            onclick="setPrimaryImage(${index})"
                            class="w-8 h-8 rounded-full flex items-center justify-center transition-colors"
                            style="background-color: #FFFFFF; color: #F7961D;"
                            onmouseover="this.style.backgroundColor='#FED7AA';"
                            onmouseout="this.style.backgroundColor='#FFFFFF';"
                            title="Set as primary image"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                            </svg>
                        </button>
                    ` : ''}
                    <button 
                        type="button"
                        onclick="removeImage(${index})"
                        class="w-8 h-8 rounded-full flex items-center justify-center transition-colors"
                        style="background-color: #FFFFFF; color: #EF4444;"
                        onmouseover="this.style.backgroundColor='#FEE2E2';"
                        onmouseout="this.style.backgroundColor='#FFFFFF';"
                        title="Delete"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            `;
            
            imagePreview.appendChild(div);
        });
    }

    // Remove image
    function removeImage(index) {
                        if (confirm('Are you sure you want to delete this image?')) {
            const wasPrimary = uploadedImages[index].isPrimary;
            uploadedImages.splice(index, 1);
            
            if (wasPrimary && uploadedImages.length > 0) {
                uploadedImages[0].isPrimary = true;
                primaryImageIndex = 0;
            }
            
            renderImageGrid();
            updateFileInput();
        }
    }

    // Set primary image
    function setPrimaryImage(index) {
        uploadedImages.forEach((img, i) => {
            img.isPrimary = i === index;
        });
        primaryImageIndex = index;
        renderImageGrid();
    }

    // Update file input with remaining files (primary image first)
    function updateFileInput() {
        const dt = new DataTransfer();
        
        // Sort: primary image first, then others
        const sortedImages = [...uploadedImages].sort((a, b) => {
            if (a.isPrimary) return -1;
            if (b.isPrimary) return 1;
            return 0;
        });
        
        sortedImages.forEach(image => {
            dt.items.add(image.file);
        });
        fileInput.files = dt.files;
    }

    // Ensure primary image is first before form submit
    document.getElementById('product-form').addEventListener('submit', function(e) {
        updateFileInput();
    });

    // Highlight SKU variables (placeholder function for future enhancements)
    function highlightSKUVariables(input, previewId = null) {
        // Function kept for compatibility, can be enhanced later
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        // Any initialization code can go here
    });
</script>
@endpush
@php
    $activeMenu = 'products';
@endphp





