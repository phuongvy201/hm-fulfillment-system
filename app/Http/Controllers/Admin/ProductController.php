<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Workshop;
use App\Models\PricingTier;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::withCount('variants')->latest()->paginate(15);
        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $workshops = Workshop::where('status', 'active')->get();
        return view('admin.products.create', compact('workshops'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255', 'unique:products'],
            'workshop_id' => ['required', 'exists:workshops,id'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive,draft'],
        ]);

        $product = Product::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'sku' => $validated['sku'] ?? null,
            'workshop_id' => $validated['workshop_id'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load([
            'workshop.market',
            'variants.attributes',
            'variants.tierPrices.pricingTier',
            'variants.tierPrices.market',
            'tierPrices.pricingTier',
            'tierPrices.market',
            'tierPrices.variant'
        ]);
        $workshops = Workshop::where('status', 'active')->get();
        $tiers = PricingTier::where('status', 'active')->orderBy('priority')->get();

        return view('admin.products.show', compact('product', 'workshops', 'tiers'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $workshops = Workshop::where('status', 'active')->get();
        return view('admin.products.edit', compact('product', 'workshops'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('products')->ignore($product->id)],
            'workshop_id' => ['required', 'exists:workshops,id'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive,draft'],
        ]);

        $product->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'sku' => $validated['sku'] ?? null,
            'workshop_id' => $validated['workshop_id'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
