@extends('layouts.admin-dashboard')

@section('title', 'Import Data')

@section('header-title', 'Import Data')
@section('header-subtitle', 'Import products, variants, and prices from CSV files')

@section('content')
<div class="space-y-6">
    <!-- Instructions -->
    <div class="bg-white rounded-lg shadow-sm p-6" style="border: 1px solid #E5E7EB;">
        <h3 class="text-lg font-semibold mb-3" style="color: #111827;">Import Instructions</h3>
        <ol class="list-decimal list-inside space-y-2 text-sm" style="color: #6B7280;">
            <li>Import <strong>Products</strong> first (required)</li>
            <li>Then import <strong>Variants</strong> (optional, but required if products have variants)</li>
            <li>Finally import <strong>Product Prices</strong> (tier prices - required for pricing)</li>
            <li>Optionally import <strong>User Prices</strong> or <strong>Team Prices</strong> for custom pricing</li>
        </ol>
        <p class="mt-4 text-sm" style="color: #6B7280;">
            <strong>Note:</strong> Download sample CSV files below to see the correct format.
        </p>
    </div>

    <!-- Import Errors Display -->
    @if(session('import_errors') && count(session('import_errors')) > 0)
    <div class="bg-red-50 rounded-lg shadow-sm p-6" style="border: 1px solid #FCA5A5;">
        <h3 class="text-lg font-semibold mb-3" style="color: #991B1B;">Import Errors ({{ session('error_count', 0) }} errors)</h3>
        <div class="max-h-64 overflow-y-auto space-y-1">
            @foreach(session('import_errors') as $error)
            <p class="text-sm" style="color: #991B1B;">• {{ $error }}</p>
            @endforeach
        </div>
        @if(session('success_count', 0) > 0)
        <p class="mt-3 text-sm font-medium" style="color: #059669;">
            {{ session('success_count') }} records were imported successfully.
        </p>
        @endif
    </div>
    @endif

    <!-- Import Forms -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Import Products -->
        <div class="bg-white rounded-lg shadow-sm p-6" style="border: 1px solid #E5E7EB;">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold" style="color: #111827;">1. Import Products</h3>
                <a href="{{ route('admin.import.sample', 'products') }}" class="text-sm px-3 py-1 rounded" style="background-color: #EFF6FF; color: #2563EB; border: 1px solid #BFDBFE;">
                    Download Sample
                </a>
            </div>
            <p class="text-sm mb-4" style="color: #6B7280;">
                Import products with name, SKU, description, and status.
            </p>
            <form action="{{ route('admin.import.products') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <input type="file" name="file" accept=".csv,.txt" required 
                        class="w-full px-4 py-2 rounded-lg border text-sm"
                        style="border-color: #D1D5DB; color: #111827;">
                    <p class="mt-1 text-xs" style="color: #9CA3AF;">CSV or TXT file, max 10MB</p>
                </div>
                <button type="submit" class="w-full px-4 py-2 rounded-lg text-sm font-medium text-white transition-all"
                    style="background-color: #2563EB;"
                    onmouseover="this.style.backgroundColor='#1D4ED8'"
                    onmouseout="this.style.backgroundColor='#2563EB'">
                    Import Products
                </button>
            </form>
        </div>

        <!-- Import Variants -->
        <div class="bg-white rounded-lg shadow-sm p-6" style="border: 1px solid #E5E7EB;">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold" style="color: #111827;">2. Import Variants</h3>
                <a href="{{ route('admin.import.sample', 'variants') }}" class="text-sm px-3 py-1 rounded" style="background-color: #EFF6FF; color: #2563EB; border: 1px solid #BFDBFE;">
                    Download Sample
                </a>
            </div>
            <p class="text-sm mb-4" style="color: #6B7280;">
                Import product variants with attributes (JSON format).
            </p>
            <form action="{{ route('admin.import.variants') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <input type="file" name="file" accept=".csv,.txt" required 
                        class="w-full px-4 py-2 rounded-lg border text-sm"
                        style="border-color: #D1D5DB; color: #111827;">
                    <p class="mt-1 text-xs" style="color: #9CA3AF;">CSV or TXT file, max 10MB</p>
                </div>
                <button type="submit" class="w-full px-4 py-2 rounded-lg text-sm font-medium text-white transition-all"
                    style="background-color: #2563EB;"
                    onmouseover="this.style.backgroundColor='#1D4ED8'"
                    onmouseout="this.style.backgroundColor='#2563EB'">
                    Import Variants
                </button>
            </form>
        </div>

        <!-- Import Product Prices (Tier Prices) -->
        <div class="bg-white rounded-lg shadow-sm p-6" style="border: 1px solid #E5E7EB;">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold" style="color: #111827;">3. Import Product Prices</h3>
                <a href="{{ route('admin.import.sample', 'product-prices') }}" class="text-sm px-3 py-1 rounded" style="background-color: #EFF6FF; color: #2563EB; border: 1px solid #BFDBFE;">
                    Download Sample
                </a>
            </div>
            <p class="text-sm mb-4" style="color: #6B7280;">
                Import tier-based prices (wood, silver, gold, diamond, special). Currency is automatically detected from location (US→USD, UK→GBP, VN→VND).
            </p>
            <form action="{{ route('admin.import.product-prices') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <input type="file" name="file" accept=".csv,.txt" required 
                        class="w-full px-4 py-2 rounded-lg border text-sm"
                        style="border-color: #D1D5DB; color: #111827;">
                    <p class="mt-1 text-xs" style="color: #9CA3AF;">CSV or TXT file, max 10MB</p>
                </div>
                <button type="submit" class="w-full px-4 py-2 rounded-lg text-sm font-medium text-white transition-all"
                    style="background-color: #2563EB;"
                    onmouseover="this.style.backgroundColor='#1D4ED8'"
                    onmouseout="this.style.backgroundColor='#2563EB'">
                    Import Product Prices
                </button>
            </form>
        </div>

        <!-- Import User Prices -->
        <div class="bg-white rounded-lg shadow-sm p-6" style="border: 1px solid #E5E7EB;">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold" style="color: #111827;">4. Import User Prices (Optional)</h3>
                <a href="{{ route('admin.import.sample', 'user-prices') }}" class="text-sm px-3 py-1 rounded" style="background-color: #EFF6FF; color: #2563EB; border: 1px solid #BFDBFE;">
                    Download Sample
                </a>
            </div>
            <p class="text-sm mb-4" style="color: #6B7280;">
                Import custom prices for specific users. Currency is automatically detected from location.
            </p>
            <form action="{{ route('admin.import.user-prices') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <input type="file" name="file" accept=".csv,.txt" required 
                        class="w-full px-4 py-2 rounded-lg border text-sm"
                        style="border-color: #D1D5DB; color: #111827;">
                    <p class="mt-1 text-xs" style="color: #9CA3AF;">CSV or TXT file, max 10MB</p>
                </div>
                <button type="submit" class="w-full px-4 py-2 rounded-lg text-sm font-medium text-white transition-all"
                    style="background-color: #2563EB;"
                    onmouseover="this.style.backgroundColor='#1D4ED8'"
                    onmouseout="this.style.backgroundColor='#2563EB'">
                    Import User Prices
                </button>
            </form>
        </div>

        <!-- Import Team Prices -->
        <div class="bg-white rounded-lg shadow-sm p-6" style="border: 1px solid #E5E7EB;">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold" style="color: #111827;">5. Import Team Prices (Optional)</h3>
                <a href="{{ route('admin.import.sample', 'team-prices') }}" class="text-sm px-3 py-1 rounded" style="background-color: #EFF6FF; color: #2563EB; border: 1px solid #BFDBFE;">
                    Download Sample
                </a>
            </div>
            <p class="text-sm mb-4" style="color: #6B7280;">
                Import custom prices for specific teams. Currency is automatically detected from location.
            </p>
            <form action="{{ route('admin.import.team-prices') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <input type="file" name="file" accept=".csv,.txt" required 
                        class="w-full px-4 py-2 rounded-lg border text-sm"
                        style="border-color: #D1D5DB; color: #111827;">
                    <p class="mt-1 text-xs" style="color: #9CA3AF;">CSV or TXT file, max 10MB</p>
                </div>
                <button type="submit" class="w-full px-4 py-2 rounded-lg text-sm font-medium text-white transition-all"
                    style="background-color: #2563EB;"
                    onmouseover="this.style.backgroundColor='#1D4ED8'"
                    onmouseout="this.style.backgroundColor='#2563EB'">
                    Import Team Prices
                </button>
            </form>
        </div>
    </div>

    <!-- CSV Format Reference -->
    <div class="bg-white rounded-lg shadow-sm p-6" style="border: 1px solid #E5E7EB;">
        <h3 class="text-lg font-semibold mb-4" style="color: #111827;">CSV Format Reference</h3>
        <div class="space-y-4 text-sm" style="color: #6B7280;">
            <div>
                <h4 class="font-semibold mb-2" style="color: #111827;">Products CSV:</h4>
                <code class="block p-3 rounded bg-gray-50 text-xs" style="color: #374151;">name,sku,description,status</code>
            </div>
            <div>
                <h4 class="font-semibold mb-2" style="color: #111827;">Variants CSV:</h4>
                <code class="block p-3 rounded bg-gray-50 text-xs" style="color: #374151;">product_sku,variant_name,variant_sku,attributes,status</code>
            </div>
            <div>
                <h4 class="font-semibold mb-2" style="color: #111827;">Product Prices CSV:</h4>
                <code class="block p-3 rounded bg-gray-50 text-xs" style="color: #374151;">product_sku,variant_sku,location_code,pricing_tier_slug,price,status,valid_from,valid_to</code>
                <p class="mt-1 text-xs" style="color: #6B7280;">Note: Currency is auto-detected from location (US→USD, UK→GBP, VN→VND)</p>
            </div>
            <div>
                <h4 class="font-semibold mb-2" style="color: #111827;">User Prices CSV:</h4>
                <code class="block p-3 rounded bg-gray-50 text-xs" style="color: #374151;">user_email,product_sku,variant_sku,location_code,price,status,valid_from,valid_to</code>
                <p class="mt-1 text-xs" style="color: #6B7280;">Note: Currency is auto-detected from location</p>
            </div>
            <div>
                <h4 class="font-semibold mb-2" style="color: #111827;">Team Prices CSV:</h4>
                <code class="block p-3 rounded bg-gray-50 text-xs" style="color: #374151;">team_slug,product_sku,variant_sku,location_code,price,status,valid_from,valid_to</code>
                <p class="mt-1 text-xs" style="color: #6B7280;">Note: Currency is auto-detected from location</p>
            </div>
        </div>
    </div>
</div>
@endsection

