<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CurrencyController extends Controller
{
    /**
     * Display exchange rates management page.
     */
    public function index()
    {
        $currencies = Currency::where('status', 'active')
            ->orderBy('code')
            ->get();

        // Get all active exchange rates
        $exchangeRates = ExchangeRate::with('creator')
            ->active()
            ->orderBy('effective_date', 'desc')
            ->orderBy('from_currency')
            ->orderBy('to_currency')
            ->paginate(20);

        // Get current effective rates for display
        $currentRates = [];
        foreach ($currencies as $fromCurrency) {
            foreach ($currencies as $toCurrency) {
                if ($fromCurrency->code !== $toCurrency->code) {
                    $rate = ExchangeRate::getCurrentRate($fromCurrency->code, $toCurrency->code);
                    if ($rate) {
                        $currentRates[$fromCurrency->code][$toCurrency->code] = $rate;
                    }
                }
            }
        }

        return view('admin.currencies.index', compact('currencies', 'exchangeRates', 'currentRates'))
            ->with('activeMenu', 'currencies');
    }

    /**
     * Show the form for creating a new exchange rate.
     */
    public function create()
    {
        $currencies = Currency::where('status', 'active')
            ->orderBy('code')
            ->get();

        return view('admin.currencies.create', compact('currencies'))
            ->with('activeMenu', 'currencies');
    }

    /**
     * Store a newly created exchange rate.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_currency' => ['required', 'string', 'size:3', 'exists:currencies,code'],
            'to_currency' => ['required', 'string', 'size:3', 'exists:currencies,code', 'different:from_currency'],
            'rate' => ['required', 'numeric', 'min:0', 'max:999999999.999999'],
            'effective_date' => ['required', 'date'],
            'expires_at' => ['nullable', 'date', 'after:effective_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Check if there's already an active rate for this pair and date
        $existing = ExchangeRate::where('from_currency', $validated['from_currency'])
            ->where('to_currency', $validated['to_currency'])
            ->where('effective_date', $validated['effective_date'])
            ->where('status', 'active')
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Đã tồn tại exchange rate cho cặp tiền tệ này vào ngày này. Vui lòng cập nhật hoặc xóa rate cũ.');
        }

        $validated['created_by'] = Auth::id();
        $validated['status'] = 'active';

        ExchangeRate::create($validated);

        return redirect()->route('admin.currencies.index')
            ->with('success', 'Exchange rate đã được tạo thành công.');
    }

    /**
     * Show the form for editing an exchange rate.
     */
    public function edit(ExchangeRate $exchangeRate)
    {
        $currencies = Currency::where('status', 'active')
            ->orderBy('code')
            ->get();

        return view('admin.currencies.edit', compact('exchangeRate', 'currencies'))
            ->with('activeMenu', 'currencies');
    }

    /**
     * Update the specified exchange rate.
     */
    public function update(Request $request, ExchangeRate $exchangeRate)
    {
        $validated = $request->validate([
            'from_currency' => ['required', 'string', 'size:3', 'exists:currencies,code'],
            'to_currency' => ['required', 'string', 'size:3', 'exists:currencies,code', 'different:from_currency'],
            'rate' => ['required', 'numeric', 'min:0', 'max:999999999.999999'],
            'effective_date' => ['required', 'date'],
            'expires_at' => ['nullable', 'date', 'after:effective_date'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Check if there's already an active rate for this pair and date (excluding current one)
        $existing = ExchangeRate::where('from_currency', $validated['from_currency'])
            ->where('to_currency', $validated['to_currency'])
            ->where('effective_date', $validated['effective_date'])
            ->where('status', 'active')
            ->where('id', '!=', $exchangeRate->id)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Đã tồn tại exchange rate cho cặp tiền tệ này vào ngày này. Vui lòng cập nhật hoặc xóa rate cũ.');
        }

        $exchangeRate->update($validated);

        return redirect()->route('admin.currencies.index')
            ->with('success', 'Exchange rate đã được cập nhật thành công.');
    }

    /**
     * Remove the specified exchange rate.
     */
    public function destroy(ExchangeRate $exchangeRate)
    {
        $exchangeRate->delete();

        return redirect()->route('admin.currencies.index')
            ->with('success', 'Exchange rate đã được xóa thành công.');
    }
}
