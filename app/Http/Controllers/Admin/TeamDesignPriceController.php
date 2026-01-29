<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamDesignPrice;
use Illuminate\Http\Request;

class TeamDesignPriceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $prices = TeamDesignPrice::with('team')
            ->latest()
            ->paginate(20);

        return view('admin.design-prices.teams.index', compact('prices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $teams = Team::orderBy('name')->get();

        return view('admin.design-prices.teams.create', compact('teams'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'team_id' => 'required|exists:teams,id',
            'first_side_price_vnd' => 'required|numeric|min:0',
            'additional_side_price_vnd' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
        ]);

        // Deactivate other active prices for this team
        if ($validated['status'] === 'active') {
            TeamDesignPrice::where('team_id', $validated['team_id'])
                ->where('status', 'active')
                ->update(['status' => 'inactive']);
        }

        TeamDesignPrice::create($validated);

        return redirect()->route('admin.design-prices.teams.index')
            ->with('success', 'Team design price created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TeamDesignPrice $teamDesignPrice)
    {
        $teamDesignPrice->load('team');
        $teams = Team::orderBy('name')->get();

        return view('admin.design-prices.teams.edit', compact('teamDesignPrice', 'teams'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TeamDesignPrice $teamDesignPrice)
    {
        $validated = $request->validate([
            'team_id' => 'required|exists:teams,id',
            'first_side_price_vnd' => 'required|numeric|min:0',
            'additional_side_price_vnd' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
        ]);

        // Deactivate other active prices for this team if activating this one
        if ($validated['status'] === 'active' && $teamDesignPrice->status !== 'active') {
            TeamDesignPrice::where('team_id', $validated['team_id'])
                ->where('id', '!=', $teamDesignPrice->id)
                ->where('status', 'active')
                ->update(['status' => 'inactive']);
        }

        $teamDesignPrice->update($validated);

        return redirect()->route('admin.design-prices.teams.index')
            ->with('success', 'Team design price updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TeamDesignPrice $teamDesignPrice)
    {
        $teamDesignPrice->delete();

        return redirect()->route('admin.design-prices.teams.index')
            ->with('success', 'Team design price deleted successfully.');
    }
}
