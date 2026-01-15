@extends('layouts.admin-dashboard')

@section('title', 'Edit Exchange Rate - ' . config('app.name', 'Laravel'))

@section('header-title', 'Edit Exchange Rate')
@section('header-subtitle', 'Update exchange rate')

@section('header-actions')
<a href="{{ route('admin.currencies.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
    ← Back to Exchange Rates
</a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
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

    <form method="POST" action="{{ route('admin.currencies.update', $exchangeRate) }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <!-- Currency Pair -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="from_currency" class="block text-sm font-semibold mb-2 text-gray-700">
                        From Currency <span class="text-red-500">*</span>
                    </label>
                    <select name="from_currency" id="from_currency" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        <option value="">-- Select currency --</option>
                        @foreach($currencies as $currency)
                        <option value="{{ $currency->code }}" {{ old('from_currency', $exchangeRate->from_currency) == $currency->code ? 'selected' : '' }}>
                            {{ $currency->code }} - {{ $currency->name }} ({{ $currency->symbol }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="to_currency" class="block text-sm font-semibold mb-2 text-gray-700">
                        To Currency <span class="text-red-500">*</span>
                    </label>
                    <select name="to_currency" id="to_currency" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        <option value="">-- Select currency --</option>
                        @foreach($currencies as $currency)
                        <option value="{{ $currency->code }}" {{ old('to_currency', $exchangeRate->to_currency) == $currency->code ? 'selected' : '' }}>
                            {{ $currency->code }} - {{ $currency->name }} ({{ $currency->symbol }})
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Exchange Rate -->
            <div>
                <label for="rate" class="block text-sm font-semibold mb-2 text-gray-700">
                    Exchange Rate (Rate) <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="number" 
                        name="rate" 
                        id="rate" 
                        step="0.000001"
                        min="0"
                        max="999999999.999999"
                        value="{{ old('rate', $exchangeRate->rate) }}"
                        placeholder="Example: 24500 (for USD to VND)"
                        required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <p class="mt-1 text-xs text-gray-500">Example: 1 <span id="from-currency-display">{{ old('from_currency', $exchangeRate->from_currency) }}</span> = <span id="rate-preview">{{ number_format(old('rate', $exchangeRate->rate), 6) }}</span> <span id="to-currency-display">{{ old('to_currency', $exchangeRate->to_currency) }}</span></p>
                </div>
            </div>

            <!-- Effective Date -->
            <div>
                <label for="effective_date" class="block text-sm font-semibold mb-2 text-gray-700">
                    Effective Date <span class="text-red-500">*</span>
                </label>
                <input type="date" 
                    name="effective_date" 
                    id="effective_date" 
                    value="{{ old('effective_date', $exchangeRate->effective_date->format('Y-m-d')) }}"
                    required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                <p class="mt-1 text-xs text-gray-500">The date on which this exchange rate becomes effective</p>
            </div>

            <!-- Expires At (Optional) -->
            <div>
                <label for="expires_at" class="block text-sm font-semibold mb-2 text-gray-700">
                    Expiration Date (Optional)
                </label>
                <input type="date" 
                    name="expires_at" 
                    id="expires_at" 
                    value="{{ old('expires_at', $exchangeRate->expires_at ? $exchangeRate->expires_at->format('Y-m-d') : '') }}"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                <p class="mt-1 text-xs text-gray-500">Leave blank if this exchange rate has no expiration date</p>
            </div>

            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-semibold mb-2 text-gray-700">
                    Status <span class="text-red-500">*</span>
                </label>
                <select name="status" id="status" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="active" {{ old('status', $exchangeRate->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $exchangeRate->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-semibold mb-2 text-gray-700">
                    Notes (Optional)
                </label>
                <textarea name="notes" 
                    id="notes" 
                    rows="3"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                    placeholder="Add notes about this exchange rate...">{{ old('notes', $exchangeRate->notes) }}</textarea>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.currencies.index') }}" 
                    class="px-6 py-2.5 rounded-lg text-sm font-semibold transition-all border" 
                    style="color: #374151; border-color: #D1D5DB;" 
                    onmouseover="this.style.backgroundColor='#F3F4F6';" 
                    onmouseout="this.style.backgroundColor='transparent';">
                    Cancel
                </a>
                <button type="submit" 
                    class="px-6 py-2.5 rounded-lg text-sm font-semibold text-white transition-colors shadow-sm"
                    style="background-color: #F97316;"
                    onmouseover="this.style.backgroundColor='#EA580C';" 
                    onmouseout="this.style.backgroundColor='#F97316';">
                    Update Exchange Rate
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    // Update rate preview when rate or currencies change
    document.getElementById('rate').addEventListener('input', updatePreview);
    document.getElementById('from_currency').addEventListener('change', updatePreview);
    document.getElementById('to_currency').addEventListener('change', updatePreview);

    function updatePreview() {
        const fromCurrency = document.getElementById('from_currency').value || 'USD';
        const toCurrency = document.getElementById('to_currency').value || 'VND';
        const rate = document.getElementById('rate').value || '0';
        
        document.getElementById('from-currency-display').textContent = fromCurrency;
        document.getElementById('to-currency-display').textContent = toCurrency;
        
        const preview = document.getElementById('rate-preview');
        preview.textContent = parseFloat(rate).toLocaleString('vi-VN', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 6
        });
    }

    // Validate that to_currency is different from from_currency
    document.getElementById('to_currency').addEventListener('change', function() {
        const fromCurrency = document.getElementById('from_currency').value;
        const toCurrency = this.value;
        
        if (fromCurrency && toCurrency && fromCurrency === toCurrency) {
            alert('From currency and To currency must be different!');
            this.value = '';
        }
    });
</script>
@endsection

