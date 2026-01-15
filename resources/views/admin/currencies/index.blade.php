@extends('layouts.admin-dashboard')

@section('title', 'Currency & Exchange Rates Management - ' . config('app.name', 'Laravel'))

@section('header-title', 'Currency & Exchange Rates')
@section('header-subtitle', 'Manage exchange rates')

@section('header-actions')
<a href="{{ route('admin.currencies.create') }}" class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2.5 rounded-lg font-semibold flex items-center gap-2 transition-colors shadow-sm">
    <span class="material-symbols-outlined">add</span>
    Add Exchange Rate
</a>
@endsection

@section('content')
<div class="space-y-6">
    @if(session('success'))
    <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200">
        <div class="flex items-center gap-2 text-green-800">
            <span class="material-symbols-outlined">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
        <div class="flex items-center gap-2 text-red-800">
            <span class="material-symbols-outlined">error</span>
            <span>{{ session('error') }}</span>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
        <div class="flex items-center gap-2 text-red-800">
            <span class="material-symbols-outlined">error</span>
            <span>{{ $errors->first() }}</span>
        </div>
    </div>
    @endif

    <!-- Current Exchange Rates Matrix -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Current Exchange Rates</h2>
            <p class="text-sm text-gray-500 mt-1">Exchange rates that are currently effective (active) for each currency pair</p>
        </div>
        <div class="p-6 overflow-x-auto">
            @if(count($currencies) > 0)
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">From \ To</th>
                        @foreach($currencies as $toCurrency)
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                            {{ $toCurrency->code }}<br>
                            <span class="text-xs text-gray-400">{{ $toCurrency->symbol }}</span>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($currencies as $fromCurrency)
                    <tr>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-gray-900">{{ $fromCurrency->code }}</span>
                                <span class="text-xs text-gray-500">{{ $fromCurrency->symbol }}</span>
                            </div>
                        </td>
                        @foreach($currencies as $toCurrency)
                        <td class="px-4 py-4 whitespace-nowrap text-center">
                            @if($fromCurrency->code === $toCurrency->code)
                            <span class="text-sm text-gray-400">-</span>
                            @elseif(isset($currentRates[$fromCurrency->code][$toCurrency->code]))
                            <span class="text-sm font-semibold text-gray-900">
                                {{ number_format($currentRates[$fromCurrency->code][$toCurrency->code], 6) }}
                            </span>
                            @else
                            <span class="text-sm text-gray-400 italic">No data</span>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="text-center py-12">
                <p class="text-gray-500">No currency in the system.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Exchange Rates History -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">History of Exchange Rates</h2>
            <p class="text-sm text-gray-500 mt-1">All exchange rates have been created</p>
        </div>
        <div class="overflow-x-auto">
            @if($exchangeRates->count() > 0)
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">From</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">To</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rate</th>  
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Effective
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Expiration</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Created by</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>   
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($exchangeRates as $rate)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-semibold text-gray-900">{{ $rate->from_currency }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-semibold text-gray-900">{{ $rate->to_currency }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-mono text-gray-900">{{ number_format($rate->rate, 6) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900">{{ $rate->effective_date->format('d/m/Y') }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($rate->expires_at)
                            <span class="text-sm text-gray-900">{{ $rate->expires_at->format('d/m/Y') }}</span>
                            @else
                            <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-600">{{ $rate->creator->name ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($rate->status === 'active')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                            @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.currencies.edit', $rate) }}" class="text-blue-600 hover:text-blue-900">
                                    <span class="material-symbols-outlined text-base">edit</span>
                                </a>
                                <form action="{{ route('admin.currencies.destroy', $rate) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa exchange rate này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @if($rate->notes)
                    <tr>
                        <td colspan="8" class="px-6 py-2 bg-gray-50">
                            <p class="text-xs text-gray-600 italic"><strong>Ghi chú:</strong> {{ $rate->notes }}</p>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $exchangeRates->links() }}
            </div>
            @else
            <div class="text-center py-12">
                <p class="text-gray-500">Chưa có exchange rate nào. Hãy tạo exchange rate đầu tiên.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

