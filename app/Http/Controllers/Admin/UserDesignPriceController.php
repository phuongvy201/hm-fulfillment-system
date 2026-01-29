<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDesignPrice;
use Illuminate\Http\Request;

class UserDesignPriceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $prices = UserDesignPrice::with('user')
            ->latest()
            ->paginate(20);

        return view('admin.design-prices.users.index', compact('prices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::whereHas('role', function ($q) {
            $q->where('slug', 'customer');
        })->orderBy('name')->get();

        return view('admin.design-prices.users.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'first_side_price_vnd' => 'required|numeric|min:0',
            'additional_side_price_vnd' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
        ]);

        // Deactivate other active prices for this user
        if ($validated['status'] === 'active') {
            UserDesignPrice::where('user_id', $validated['user_id'])
                ->where('status', 'active')
                ->update(['status' => 'inactive']);
        }

        UserDesignPrice::create($validated);

        return redirect()->route('admin.design-prices.users.index')
            ->with('success', 'User design price created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserDesignPrice $userDesignPrice)
    {
        $userDesignPrice->load('user');
        $users = User::whereHas('role', function ($q) {
            $q->where('slug', 'customer');
        })->orderBy('name')->get();

        return view('admin.design-prices.users.edit', compact('userDesignPrice', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserDesignPrice $userDesignPrice)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'first_side_price_vnd' => 'required|numeric|min:0',
            'additional_side_price_vnd' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
        ]);

        // Deactivate other active prices for this user if activating this one
        if ($validated['status'] === 'active' && $userDesignPrice->status !== 'active') {
            UserDesignPrice::where('user_id', $validated['user_id'])
                ->where('id', '!=', $userDesignPrice->id)
                ->where('status', 'active')
                ->update(['status' => 'inactive']);
        }

        $userDesignPrice->update($validated);

        return redirect()->route('admin.design-prices.users.index')
            ->with('success', 'User design price updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserDesignPrice $userDesignPrice)
    {
        $userDesignPrice->delete();

        return redirect()->route('admin.design-prices.users.index')
            ->with('success', 'User design price deleted successfully.');
    }
}
