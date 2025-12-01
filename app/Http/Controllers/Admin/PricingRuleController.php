<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingRule;
use App\Models\Market;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PricingRuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rules = PricingRule::with(['market', 'product'])
            ->orderBy('priority', 'desc')
            ->orderBy('market_id')
            ->latest()
            ->paginate(15);

        return view('admin.pricing-rules.index', compact('rules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $markets = Market::where('status', 'active')->get();
        $products = Product::where('status', 'active')->get();

        return view('admin.pricing-rules.create', compact('markets', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'market_id' => ['required', 'exists:markets,id'],
            'product_id' => ['nullable', 'exists:products,id'],
            'rule_type' => ['required', 'string', 'max:255'],
            'condition_key' => ['nullable', 'string', 'max:255'],
            'condition_value' => ['nullable', 'string', 'max:255'],
            'operation' => ['required', 'in:add,subtract,multiply,divide,set'],
            'amount' => ['required', 'numeric'],
            'currency' => ['nullable', 'string', 'size:3'],
            'priority' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        PricingRule::create($validated);

        return redirect()->route('admin.pricing-rules.index')
            ->with('success', 'Pricing rule created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PricingRule $pricingRule)
    {
        $pricingRule->load(['market', 'product']);

        return view('admin.pricing-rules.show', compact('pricingRule'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PricingRule $pricingRule)
    {
        $markets = Market::where('status', 'active')->get();
        $products = Product::where('status', 'active')->get();

        return view('admin.pricing-rules.edit', compact('pricingRule', 'markets', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PricingRule $pricingRule)
    {
        $validated = $request->validate([
            'market_id' => ['required', 'exists:markets,id'],
            'product_id' => ['nullable', 'exists:products,id'],
            'rule_type' => ['required', 'string', 'max:255'],
            'condition_key' => ['nullable', 'string', 'max:255'],
            'condition_value' => ['nullable', 'string', 'max:255'],
            'operation' => ['required', 'in:add,subtract,multiply,divide,set'],
            'amount' => ['required', 'numeric'],
            'currency' => ['nullable', 'string', 'size:3'],
            'priority' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $pricingRule->update($validated);

        return redirect()->route('admin.pricing-rules.index')
            ->with('success', 'Pricing rule updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PricingRule $pricingRule)
    {
        $pricingRule->delete();

        return redirect()->route('admin.pricing-rules.index')
            ->with('success', 'Pricing rule deleted successfully.');
    }
}
