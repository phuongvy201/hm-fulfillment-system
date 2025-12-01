<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Team;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $teams = Team::withCount('users')->latest()->paginate(15);
        return view('admin.teams.index', compact('teams'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.teams.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:teams'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $team = Team::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('admin.teams.index')
            ->with('success', 'Team created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Team $team)
    {
        $team->load('users.role');
        return view('admin.teams.show', compact('team'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Team $team)
    {
        return view('admin.teams.edit', compact('team'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('teams')->ignore($team->id)],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $team->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('admin.teams.index')
            ->with('success', 'Team updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team)
    {
        // Check if team has users
        if ($team->users()->count() > 0) {
            return redirect()->route('admin.teams.index')
                ->with('error', 'Cannot delete team with members. Please remove all members first.');
        }

        $team->delete();

        return redirect()->route('admin.teams.index')
            ->with('success', 'Team deleted successfully.');
    }
}
