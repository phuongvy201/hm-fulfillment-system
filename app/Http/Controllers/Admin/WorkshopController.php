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
    public function index(Request $request)
    {
        $query = Workshop::with(['market'])
            ->withCount(['skus', 'prices', 'products']);

        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Get per page value
        $perPage = $request->get('per_page', 15);
        $perPage = in_array($perPage, [12, 25, 50, 100]) ? $perPage : 15;

        $workshops = $query->latest()->paginate($perPage)->withQueryString();

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
            // API Settings
            'api_enabled' => ['nullable', 'boolean'],
            'api_type' => ['nullable', 'string', 'in:rest,soap,custom'],
            'api_endpoint' => ['nullable', 'url', 'max:500'],
            'api_key' => ['nullable', 'string', 'max:255'],
            'api_secret' => ['nullable', 'string', 'max:255'],
            'api_settings' => ['nullable', 'json'],
            'api_notes' => ['nullable', 'string'],
        ]);

        // Convert api_enabled checkbox
        $validated['api_enabled'] = $request->has('api_enabled');

        $workshop->update($validated);

        return redirect()->route('admin.workshops.index')
            ->with('success', 'Workshop updated successfully.');
    }

    /**
     * Test API connection.
     */
    public function testApi(Workshop $workshop)
    {
        $apiService = app(\App\Services\WorkshopApiService::class);
        $result = $apiService->testConnection($workshop);

        if ($result['success']) {
            return back()->with('success', 'API connection successful!');
        } else {
            return back()->withErrors(['error' => 'API connection failed: ' . $result['error']]);
        }
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
