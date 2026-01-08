<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Credit;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as LogFacade;

class WalletController extends Controller
{
    /**
     * Display a listing of all wallets with filters.
     */
    public function index(Request $request)
    {
        $query = User::with(['wallet', 'credit'])
            ->whereHas('role', function ($q) {
                $q->where('slug', '!=', 'super-admin')
                    ->where('slug', '!=', 'it-admin');
            });

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(20)->withQueryString();

        // Calculate wallet stats for each user
        foreach ($users as $user) {
            $wallet = $user->wallet ?? new Wallet(['balance' => 0, 'currency' => 'USD']);
            $credit = $user->credit ?? new Credit(['credit_limit' => 0, 'current_credit' => 0, 'enabled' => false]);

            $user->available_balance = $wallet->balance;
            $user->credit_limit = $credit->credit_limit;
            $user->current_debt = $credit->current_credit;
            $user->remaining_credit = max(0, $credit->credit_limit - $credit->current_credit);
            $user->total_payment_capacity = $wallet->balance + $user->remaining_credit;
        }

        return view('admin.wallets.index', compact('users'));
    }

    /**
     * Display wallet information for a user.
     */
    public function show(User $user)
    {
        $wallet = $user->wallet ?? Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
            'currency' => 'USD',
        ]);

        $credit = $user->credit ?? Credit::create([
            'user_id' => $user->id,
            'credit_limit' => 0,
            'current_credit' => 0,
            'enabled' => false,
        ]);

        // Calculate payment capacity
        $remaining_credit = max(0, $credit->credit_limit - $credit->current_credit);
        $total_payment_capacity = $wallet->balance + $remaining_credit;

        $transactions = $wallet->transactions()
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.wallets.show', compact('user', 'wallet', 'credit', 'remaining_credit', 'total_payment_capacity', 'transactions'));
    }

    /**
     * Adjust wallet balance (admin only).
     */
    public function adjust(Request $request, User $user)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric'],
            'description' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:add,deduct'],
        ]);

        $wallet = $user->wallet ?? Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
            'currency' => 'USD',
        ]);

        DB::beginTransaction();
        try {
            if ($validated['type'] === 'add') {
                $wallet->addBalance(
                    $validated['amount'],
                    $validated['description'],
                    null
                );
            } else {
                $wallet->deductBalance(
                    $validated['amount'],
                    $validated['description'],
                    null
                );
            }

            // Update created_by and type for admin adjustment
            $admin = auth()->user();
            $transaction = $wallet->transactions()->latest()->first();
            $transaction->update([
                'type' => 'admin_adjustment',
                'created_by' => $admin->id,
            ]);

            // Log the adjustment
            LogFacade::info('Admin adjusted wallet balance', [
                'user_id' => $user->id,
                'admin_id' => $admin->id,
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'description' => $validated['description'],
                'new_balance' => $wallet->balance,
            ]);

            DB::commit();

            return redirect()->route('admin.wallets.show', $user)
                ->with('success', 'Wallet balance adjusted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            LogFacade::error('Failed to adjust wallet balance', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Process a refund (admin only).
     */
    public function refund(Request $request, User $user)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['required', 'string', 'max:255'],
            'reference_type' => ['nullable', 'string', 'max:255'],
            'reference_id' => ['nullable', 'integer'],
        ]);

        $wallet = $user->wallet ?? Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
            'currency' => 'USD',
        ]);

        DB::beginTransaction();
        try {
            // Add balance to wallet
            $wallet->addBalance(
                $validated['amount'],
                $validated['description'],
                null
            );

            // Update transaction type and reference for refund
            $admin = auth()->user();
            $transaction = $wallet->transactions()->latest()->first();
            $transaction->update([
                'type' => 'refund',
                'created_by' => $admin->id,
                'reference_type' => $validated['reference_type'] ?? null,
                'reference_id' => $validated['reference_id'] ?? null,
            ]);

            // Log the refund
            LogFacade::info('Admin processed refund', [
                'user_id' => $user->id,
                'admin_id' => $admin->id,
                'amount' => $validated['amount'],
                'description' => $validated['description'],
                'reference_type' => $validated['reference_type'],
                'reference_id' => $validated['reference_id'],
                'new_balance' => $wallet->balance,
            ]);

            DB::commit();

            return redirect()->route('admin.wallets.show', $user)
                ->with('success', 'Refund processed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            LogFacade::error('Failed to process refund', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }
}
