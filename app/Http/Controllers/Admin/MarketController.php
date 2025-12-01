<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Market;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MarketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $markets = Market::withCount(['workshops'])
            ->with(['workshops' => function ($query) {
                $query->withCount('products');
            }])
            ->orderBy('code')
            ->paginate(15);

        return view('admin.markets.index', compact('markets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.markets.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', 'unique:markets,code'],
            'name' => ['required', 'string', 'max:255'],
            'currency' => ['required', 'string', 'size:3'],
            'currency_symbol' => ['nullable', 'string', 'max:5'],
            'timezone' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        Market::create($validated);

        return redirect()->route('admin.markets.index')
            ->with('success', 'Market created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Market $market)
    {
        $market->load(['workshops.products', 'pricingRules.product']);

        return view('admin.markets.show', compact('market'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Market $market)
    {
        return view('admin.markets.edit', compact('market'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Market $market)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', Rule::unique('markets')->ignore($market->id)],
            'name' => ['required', 'string', 'max:255'],
            'currency' => ['required', 'string', 'size:3'],
            'currency_symbol' => ['nullable', 'string', 'max:5'],
            'timezone' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $market->update($validated);

        return redirect()->route('admin.markets.index')
            ->with('success', 'Market updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Market $market)
    {
        // Check if market has workshops (products belong to workshops, not directly to market)
        if ($market->workshops()->count() > 0) {
            return redirect()->route('admin.markets.index')
                ->with('error', 'Cannot delete market with existing workshops.');
        }

        $market->delete();

        return redirect()->route('admin.markets.index')
            ->with('success', 'Market deleted successfully.');
    }
}
