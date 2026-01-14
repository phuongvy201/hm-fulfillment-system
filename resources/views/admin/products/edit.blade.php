@extends('layouts.admin-dashboard') 

@section('title', 'Edit Product - ' . config('app.name', 'Laravel'))

@section('header-title', 'Edit Product')
@section('header-subtitle', 'Update product information')

@section('header-actions')
<a href="{{ route('admin.products.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
    ← Back to Products
</a>
@endsection

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow-sm p-8">
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-lg" style="background-color: #FEE2E2; border: 1px solid #EF4444;">
                <ul class="text-sm" style="color: #991B1B;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="productEditForm" method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div id="formStatus" class="hidden mb-4 px-4 py-3 rounded-lg text-sm font-semibold"></div>
                <div>
                    <label for="name" class="block text-sm font-semibold mb-2" style="color: #111827;">Product Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name', $product->name) }}"
                        required 
                        autofocus
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                        onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                    >
                </div>

                <div>
                    <label for="sku" class="block text-sm font-semibold mb-2" style="color: #111827;">SKU (Optional)</label>
                    <input 
                        type="text" 
                        id="sku" 
                        name="sku" 
                        value="{{ old('sku', $product->sku) }}"
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                        onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                    >
                </div>

                <div>
                    <label for="sku_template" class="block text-sm font-semibold mb-2" style="color: #111827;">
                        Template SKU nội bộ <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="sku_template" 
                        name="sku_template" 
                        value="{{ old('sku_template', $product->sku_template) }}"
                        placeholder="e.g., T004-{COLOR_CODE}-{SIZE}-US"
                        required
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all font-mono text-sm"
                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                        onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                    >
                    <p class="mt-1 text-xs text-gray-500">
                        Sử dụng các biến: <code>{COLOR_CODE}</code>, <code>{COLOR}</code>, <code>{SIZE}</code>, <code>{MARKET_CODE}</code>, <code>{WORKSHOP_CODE}</code><br>
                        Ví dụ: <code>T004-{COLOR_CODE}-{SIZE}-US</code>
                    </p>
                </div>

                <div>
                    <label for="workshop_sku_template" class="block text-sm font-semibold mb-2" style="color: #111827;">
                        Template Workshop SKU
                    </label>
                    <input 
                        type="text" 
                        id="workshop_sku_template" 
                        name="workshop_sku_template" 
                        value="{{ old('workshop_sku_template', $product->workshop_sku_template) }}"
                        placeholder="e.g., COMFORT {SIZE}/ {COLOR_CODE}"
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all font-mono text-sm"
                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                        onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                    >
                    <p class="mt-1 text-xs text-gray-500">
                        Sử dụng các biến: <code>{WORKSHOP_SKU_CODE}</code>, <code>{WORKSHOP_CODE}</code>, <code>{COLOR_CODE}</code>, <code>{COLOR}</code>, <code>{SIZE}</code><br>
                        Ví dụ: <code>COMFORT {SIZE}/ {COLOR_CODE}</code> hoặc <code>{WORKSHOP_SKU_CODE}-{COLOR_CODE}-{SIZE}</code>
                    </p>
                </div>

                <div>
                    <label for="workshop_id" class="block text-sm font-semibold mb-2" style="color: #111827;">Workshop <span class="text-red-500">*</span></label>
                    <select 
                        id="workshop_id" 
                        name="workshop_id"
                        required
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                        onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                    >
                        <option value="">Select Workshop</option>
                        @foreach($workshops as $workshop)
                            <option value="{{ $workshop->id }}" {{ old('workshop_id', $product->workshop_id) == $workshop->id ? 'selected' : '' }}>
                                {{ $workshop->name }} ({{ $workshop->code }}) - {{ $workshop->market->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold mb-2" style="color: #111827;">Description</label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="4"
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all resize-none"
                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                        onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                    >{{ old('description', $product->description) }}</textarea>
                </div>

                <div>
                    <label for="status" class="block text-sm font-semibold mb-2" style="color: #111827;">Status</label>
                    <select 
                        id="status" 
                        name="status"
                        required
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                        onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                    >
                        <option value="draft" {{ old('status', $product->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Existing Images -->
                @if($product->images->count() > 0)
                <div>
                    <label class="block text-sm font-semibold mb-2" style="color: #111827;">Current Images</label>
                    <div class="grid grid-cols-3 gap-4">
                        @foreach($product->images as $image)
                        <div class="relative">
                            <img src="{{ Storage::url($image->image_path) }}" alt="Product Image" class="w-full h-32 object-cover rounded-lg border" style="border-color: #E5E7EB;">
                            @if($image->is_primary)
                            <span class="absolute top-2 left-2 px-2 py-1 text-xs font-semibold rounded" style="background-color: #10B981; color: white;">Primary</span>
                            @endif
                            <div class="mt-2 flex items-center justify-center gap-2">
                                <a href="{{ route('admin.products.images.set-primary', [$product, $image]) }}" class="px-3 py-1 text-xs font-semibold rounded text-white transition-all" style="background-color: #2563EB;" onclick="return confirm('Set this as primary image?');">Set Primary</a>
                                <form method="POST" action="{{ route('admin.products.images.delete', [$product, $image]) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1 text-xs font-semibold rounded text-white transition-all" style="background-color: #EF4444;" onclick="return confirm('Delete this image?');">Delete</button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Upload New Images -->
                <div>
                    <label for="images" class="block text-sm font-semibold mb-2" style="color: #111827;">Upload New Images</label>
                    <input 
                        type="file" 
                        id="images" 
                        name="images[]" 
                        multiple
                        accept="image/*"
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                        onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                    >
                    <p class="text-xs mt-1" style="color: #6B7280;">You can upload multiple images. Accepted formats: JPG, PNG, GIF, WEBP (max 5MB per image)</p>
                    <div id="image-preview" class="mt-4 grid grid-cols-3 gap-4"></div>
                </div>

                <div class="flex items-center gap-4 pt-4">
                    <button 
                        type="submit"
                        data-submit
                        class="px-6 py-3 rounded-lg font-semibold text-white transition-all"
                        style="background-color: #2563EB;"
                        onmouseover="this.style.backgroundColor='#1D4ED8';"
                        onmouseout="this.style.backgroundColor='#2563EB';"
                    >
                        Update Product
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="px-6 py-3 rounded-lg font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@php
    use Illuminate\Support\Facades\Storage;
    $activeMenu = 'products';
@endphp

@push('scripts')
<script>
    // Image preview for new uploads
    document.getElementById('images')?.addEventListener('change', function(e) {
        const preview = document.getElementById('image-preview');
        preview.innerHTML = '';
        
        Array.from(e.target.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'relative';
                div.innerHTML = `
                    <img src="${e.target.result}" alt="Preview" class="w-full h-32 object-cover rounded-lg border" style="border-color: #E5E7EB;">
                `;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    });

    // AJAX submit to avoid wrong method redirects
    document.getElementById('productEditForm')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const form = e.currentTarget;
        const submitBtn = form.querySelector('[data-submit]');
        const statusBox = document.getElementById('formStatus');

        const showStatus = (msg, ok = true) => {
            if (!statusBox) return;
            statusBox.textContent = msg;
            statusBox.classList.remove('hidden');
            statusBox.style.backgroundColor = ok ? '#ECFDF3' : '#FEF2F2';
            statusBox.style.color = ok ? '#065F46' : '#991B1B';
            statusBox.style.border = ok ? '1px solid #10B981' : '1px solid #EF4444';
        };

        const originalText = submitBtn ? submitBtn.textContent : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Updating...';
            submitBtn.style.opacity = '0.75';
        }

        try {
            const formData = new FormData(form);
            formData.set('_method', 'PUT'); // ensure spoof

            const res = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
            });

            if (res.redirected) {
                window.location.href = res.url;
                return;
            }

            if (res.ok) {
                showStatus('Saved successfully. Reloading...', true);
                setTimeout(() => window.location.reload(), 500);
            } else {
                const text = await res.text();
                showStatus('Save failed. Please try again.', false);
                console.error('Update failed', text);
            }
        } catch (err) {
            showStatus('Network error. Please try again.', false);
            console.error(err);
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                submitBtn.style.opacity = '1';
            }
        }
    });
</script>
@endpush
@php
    $activeMenu = 'products';
@endphp





