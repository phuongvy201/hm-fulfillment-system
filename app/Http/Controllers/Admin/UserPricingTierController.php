<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PricingTier;
use App\Models\UserPricingTier;
use Illuminate\Support\Facades\Log;

class UserPricingTierController extends Controller
{
    /**
     * Display a listing of users with their pricing tiers.
     */
    public function index(Request $request)
    {
        $query = User::with(['pricingTier.pricingTier', 'role'])
            ->whereDoesntHave('role', function ($q) {
                $q->whereIn('slug', ['super-admin', 'it-admin']);
            });

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Tier filter
        if ($request->has('tier_id') && $request->tier_id) {
            $query->whereHas('pricingTier', function ($q) use ($request) {
                $q->where('pricing_tier_id', $request->tier_id);
            });
        }

        $users = $query->orderBy('name')->paginate(20);
        $tiers = PricingTier::where('status', 'active')->orderBy('priority')->get();

        return view('admin.user-pricing-tiers.index', compact('users', 'tiers'));
    }

    /**
     * Show the form for editing the user's pricing tier.
     */
    public function edit(User $user)
    {
        $user->load(['pricingTier.pricingTier', 'role']);
        
        // Check if user is admin
        if ($user->role && in_array($user->role->slug, ['super-admin', 'it-admin'])) {
            return redirect()->route('admin.user-pricing-tiers.index')
                ->withErrors(['error' => 'Cannot edit pricing tier for admin users.']);
        }

        $tiers = PricingTier::where('status', 'active')->orderBy('priority')->get();
        $currentTier = $user->pricingTier ? $user->pricingTier->pricingTier : null;

        return view('admin.user-pricing-tiers.edit', compact('user', 'tiers', 'currentTier'));
    }

    /**
     * Update the user's pricing tier.
     */
    public function update(Request $request, User $user)
    {
        $user->load('role');
        
        // Check if user is admin
        if ($user->role && in_array($user->role->slug, ['super-admin', 'it-admin'])) {
            return back()->withErrors(['error' => 'Cannot update pricing tier for admin users.'])->withInput();
        }

        $validated = $request->validate([
            'pricing_tier_id' => ['required', 'exists:pricing_tiers,id'],
        ]);

        try {
            UserPricingTier::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'pricing_tier_id' => $validated['pricing_tier_id'],
                    'assigned_at' => now(),
                ]
            );

            Log::info('User pricing tier updated', [
                'user_id' => $user->id,
                'pricing_tier_id' => $validated['pricing_tier_id'],
            ]);

            return redirect()->route('admin.user-pricing-tiers.index')
                ->with('success', "Đã cập nhật pricing tier cho user {$user->name}.");
        } catch (\Exception $e) {
            Log::error('Failed to update user pricing tier', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to update pricing tier: ' . $e->getMessage()])->withInput();
        }
    }
}

