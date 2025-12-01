<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingTier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PricingTierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tiers = PricingTier::withCount(['productTierPrices', 'userPricingTiers'])
            ->orderBy('priority', 'desc')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.pricing-tiers.index', compact('tiers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.pricing-tiers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:255', 'unique:pricing_tiers,slug'],
            'name' => ['required', 'string', 'max:255'],
            'priority' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        PricingTier::create($validated);

        return redirect()->route('admin.pricing-tiers.index')
            ->with('success', 'Pricing tier created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PricingTier $pricingTier)
    {
        $pricingTier->load(['productTierPrices.product', 'productTierPrices.variant', 'productTierPrices.market']);

        return view('admin.pricing-tiers.show', compact('pricingTier'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PricingTier $pricingTier)
    {
        return view('admin.pricing-tiers.edit', compact('pricingTier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PricingTier $pricingTier)
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:255', Rule::unique('pricing_tiers')->ignore($pricingTier->id)],
            'name' => ['required', 'string', 'max:255'],
            'priority' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $pricingTier->update($validated);

        return redirect()->route('admin.pricing-tiers.index')
            ->with('success', 'Pricing tier updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PricingTier $pricingTier)
    {
        // Check if tier has prices or user assignments
        if ($pricingTier->productTierPrices()->count() > 0) {
            return redirect()->route('admin.pricing-tiers.index')
                ->with('error', 'Cannot delete pricing tier with existing product tier prices.');
        }

        if ($pricingTier->userPricingTiers()->count() > 0) {
            return redirect()->route('admin.pricing-tiers.index')
                ->with('error', 'Cannot delete pricing tier with assigned users.');
        }

        $pricingTier->delete();

        return redirect()->route('admin.pricing-tiers.index')
            ->with('success', 'Pricing tier deleted successfully.');
    }
}
