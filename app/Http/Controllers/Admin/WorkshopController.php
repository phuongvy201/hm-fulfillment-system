<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Workshop;
use App\Models\Market;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkshopController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $workshops = Workshop::with(['market'])
            ->withCount(['skus', 'prices'])
            ->latest()
            ->paginate(15);

        return view('admin.workshops.index', compact('workshops'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $markets = Market::where('status', 'active')->get();
        return view('admin.workshops.create', compact('markets'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'market_id' => ['required', 'exists:markets,id'],
            'code' => ['required', 'string', 'max:255', 'unique:workshops,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'product_types' => ['nullable', 'array'],
            'product_types.*' => ['string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        Workshop::create($validated);

        return redirect()->route('admin.workshops.index')
            ->with('success', 'Workshop created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Workshop $workshop)
    {
        $workshop->load(['market', 'skus.variant.product', 'prices.product', 'prices.variant']);

        return view('admin.workshops.show', compact('workshop'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Workshop $workshop)
    {
        $markets = Market::where('status', 'active')->get();
        return view('admin.workshops.edit', compact('workshop', 'markets'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Workshop $workshop)
    {
        $validated = $request->validate([
            'market_id' => ['required', 'exists:markets,id'],
            'code' => ['required', 'string', 'max:255', Rule::unique('workshops')->ignore($workshop->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'product_types' => ['nullable', 'array'],
            'product_types.*' => ['string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $workshop->update($validated);

        return redirect()->route('admin.workshops.index')
            ->with('success', 'Workshop updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Workshop $workshop)
    {
        // Check if workshop has SKUs or prices
        if ($workshop->skus()->count() > 0) {
            return redirect()->route('admin.workshops.index')
                ->with('error', 'Cannot delete workshop with existing SKUs.');
        }

        if ($workshop->prices()->count() > 0) {
            return redirect()->route('admin.workshops.index')
                ->with('error', 'Cannot delete workshop with existing prices.');
        }

        $workshop->delete();

        return redirect()->route('admin.workshops.index')
            ->with('success', 'Workshop deleted successfully.');
    }
}
