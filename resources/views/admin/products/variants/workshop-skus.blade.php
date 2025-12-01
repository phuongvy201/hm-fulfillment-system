@extends('layouts.app')

@section('title', 'Manage Workshop SKUs - ' . config('app.name', 'Laravel'))

@section('header-title', 'Workshop SKUs for ' . $variant->name)
@section('header-subtitle', 'Manage workshop SKUs for this variant')

@section('header-actions')
<a href="{{ route('admin.products.show', $variant->product) }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;">
    ← Back to Product
</a>
@endsection

@section('content')
<div class="max-w-5xl">
    <div class="bg-white rounded-lg shadow-sm p-8">
        <!-- Variant Info -->
        <div class="mb-6 p-4 rounded-lg border" style="border-color: #DBEAFE; background-color: #EFF6FF;">
            <div class="flex items-center gap-4">
                <div>
                    <h4 class="font-semibold text-gray-900">{{ $variant->display_name }}</h4>
                    <div class="flex items-center gap-4 mt-1 text-sm text-gray-600">
                        @if($variant->sku)
                            <span><strong>Variant SKU:</strong> <code class="px-2 py-1 rounded bg-white">{{ $variant->sku }}</code></span>
                        @endif
                        @if($variant->attributes && $variant->attributes->count() > 0)
                            @foreach($variant->attributes as $attr)
                                <span><strong>{{ $attr->attribute_name }}:</strong> {{ $attr->attribute_value }}</span>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-lg" style="background-color: #FEE2E2; border: 1px solid #EF4444;">
                <ul class="text-sm" style="color: #991B1B;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.workshop-skus.store', $variant) }}">
            @csrf

            <div class="space-y-4">
                <div class="mb-4 p-3 rounded-lg" style="background-color: #FEF3C7; border: 1px solid #FCD34D;">
                    <p class="text-sm" style="color: #92400E;">
                        <strong>💡 Tip:</strong> Leave SKU field empty to auto-generate format: <code>{WORKSHOP_SKU_CODE}-{COLOR_CODE}-{SIZE}</code><br>
                        Mỗi loại sản phẩm có thể có Workshop SKU Code khác nhau.<br>
                        Example: <code>GD05-BLAC-L</code> (T-Shirt) hoặc <code>GD056-BLAC-L</code> (Sweatshirt)
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b" style="border-color: #E5E7EB;">
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Workshop</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Workshop SKU</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="divide-color: #E5E7EB;">
                            @foreach($workshops as $index => $workshop)
                                @php
                                    $existingSku = $variant->workshopSkus->firstWhere('workshop_id', $workshop->id);
                                @endphp
                                @php
                                    $workshopSkuCode = $variant->product->workshopProductSkuCodes->firstWhere('workshop_id', $workshop->id);
                                    $skuCodePrefix = $workshopSkuCode ? $workshopSkuCode->sku_code : $workshop->code;
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div>
                                            <span class="font-medium text-gray-900">{{ $workshop->name }}</span>
                                            <span class="text-gray-500 text-xs ml-2">(Code: {{ $workshop->code }})</span>
                                            @if($workshopSkuCode && $workshopSkuCode->sku_code != $workshop->code)
                                                <div class="text-xs text-blue-600 mt-1">
                                                    SKU Code: <code>{{ $workshopSkuCode->sku_code }}</code>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input 
                                            type="hidden" 
                                            name="workshop_skus[{{ $index }}][workshop_id]" 
                                            value="{{ $workshop->id }}"
                                        >
                                        <input 
                                            type="text" 
                                            name="workshop_skus[{{ $index }}][sku]" 
                                            value="{{ old("workshop_skus.{$index}.sku", $existingSku->sku ?? '') }}"
                                            placeholder="Auto-generate if empty"
                                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 transition-all font-mono text-sm"
                                            style="border-color: #D1D5DB;"
                                            onfocus="this.style.borderColor='#2563EB';"
                                            onblur="this.style.borderColor='#D1D5DB';"
                                        >
                                        @if($existingSku)
                                            <p class="text-xs text-gray-500 mt-1">Current: {{ $existingSku->sku }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <select 
                                            name="workshop_skus[{{ $index }}][status]"
                                            class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                                            style="border-color: #D1D5DB;"
                                        >
                                            <option value="active" {{ old("workshop_skus.{$index}.status", $existingSku->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ old("workshop_skus.{$index}.status", $existingSku->status ?? 'active') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($workshops->isEmpty())
                    <div class="text-center py-12">
                        <p class="text-sm text-gray-600">No workshops available for this product's market.</p>
                    </div>
                @endif

                <div class="flex items-center gap-4 pt-4 border-t" style="border-color: #E5E7EB;">
                    <button 
                        type="submit"
                        class="px-6 py-3 rounded-lg font-semibold text-white transition-all"
                        style="background-color: #2563EB;"
                        onmouseover="this.style.backgroundColor='#1D4ED8';"
                        onmouseout="this.style.backgroundColor='#2563EB';"
                        {{ $workshops->isEmpty() ? 'disabled' : '' }}
                    >
                        Save Workshop SKUs
                    </button>
                    <a href="{{ route('admin.products.show', $variant->product) }}" class="px-6 py-3 rounded-lg font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@php
    $activeMenu = 'products';
@endphp

