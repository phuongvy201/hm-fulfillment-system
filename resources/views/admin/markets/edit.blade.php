@extends('layouts.app')

@section('title', 'Edit Market - ' . config('app.name', 'Laravel'))

@section('header-title', 'Edit Market')
@section('header-subtitle', 'Update market information')

@section('header-actions')
<a href="{{ route('admin.markets.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
    ← Back to Markets
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

        <form method="POST" action="{{ route('admin.markets.update', $market) }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <label for="code" class="block text-sm font-semibold mb-2" style="color: #111827;">Market Code</label>
                    <input 
                        type="text" 
                        id="code" 
                        name="code" 
                        value="{{ old('code', $market->code) }}"
                        required 
                        autofocus
                        maxlength="10"
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all uppercase"
                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                        onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                    >
                </div>

                <div>
                    <label for="name" class="block text-sm font-semibold mb-2" style="color: #111827;">Market Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name', $market->name) }}"
                        required 
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                        onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                    >
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="currency" class="block text-sm font-semibold mb-2" style="color: #111827;">Currency Code</label>
                        <input 
                            type="text" 
                            id="currency" 
                            name="currency" 
                            value="{{ old('currency', $market->currency) }}"
                            required 
                            maxlength="3"
                            class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all uppercase"
                            style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                            onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                            onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                        >
                    </div>

                    <div>
                        <label for="currency_symbol" class="block text-sm font-semibold mb-2" style="color: #111827;">Currency Symbol</label>
                        <input 
                            type="text" 
                            id="currency_symbol" 
                            name="currency_symbol" 
                            value="{{ old('currency_symbol', $market->currency_symbol) }}"
                            maxlength="5"
                            class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                            style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                            onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                            onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                        >
                    </div>
                </div>

                <div>
                    <label for="timezone" class="block text-sm font-semibold mb-2" style="color: #111827;">Timezone</label>
                    <input 
                        type="text" 
                        id="timezone" 
                        name="timezone" 
                        value="{{ old('timezone', $market->timezone) }}"
                        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 transition-all"
                        style="border-color: #D1D5DB; color: #111827; background-color: #FFFFFF;"
                        onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)';"
                        onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                    >
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
                        <option value="active" {{ old('status', $market->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $market->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="flex items-center gap-4 pt-4">
                    <button 
                        type="submit"
                        class="px-6 py-3 rounded-lg font-semibold text-white transition-all"
                        style="background-color: #2563EB;"
                        onmouseover="this.style.backgroundColor='#1D4ED8';"
                        onmouseout="this.style.backgroundColor='#2563EB';"
                    >
                        Update Market
                    </button>
                    <a href="{{ route('admin.markets.index') }}" class="px-6 py-3 rounded-lg font-semibold transition-all border" style="color: #374151; border-color: #D1D5DB;" onmouseover="this.style.backgroundColor='#F3F4F6';" onmouseout="this.style.backgroundColor='transparent';">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@php
    $activeMenu = 'markets';
@endphp





