<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Credit;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditController extends Controller
{
    /**
     * Display a listing of users with credit.
     */
    public function index(Request $request)
    {
        $query = User::with(['credit', 'wallet'])
            ->whereDoesntHave('role', function ($q) {
                $q->whereIn('slug', ['super-admin', 'it-admin']);
            });

        // Filter by credit enabled
        if ($request->has('enabled') && $request->enabled !== '') {
            if ($request->enabled == '1') {
                $query->whereHas('credit', function ($q) {
                    $q->where('enabled', true);
                });
            } else {
                $query->whereDoesntHave('credit', function ($q) {
                    $q->where('enabled', true);
                });
            }
        }

        $users = $query->orderBy('name')->paginate(20);

        return view('admin.credits.index', compact('users'));
    }

    /**
     * Show the form for editing credit for a user.
     */
    public function edit(User $user)
    {
        $credit = $user->credit ?? Credit::create([
            'user_id' => $user->id,
            'credit_limit' => 0,
            'current_credit' => 0,
            'enabled' => false,
        ]);

        return view('admin.credits.edit', compact('user', 'credit'));
    }

    /**
     * Update credit settings for a user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'credit_limit' => ['required', 'numeric', 'min:0'],
            'enabled' => ['required', 'boolean'],
        ]);

        $credit = $user->credit ?? Credit::create([
            'user_id' => $user->id,
            'credit_limit' => 0,
            'current_credit' => 0,
            'enabled' => false,
        ]);

        // If disabling credit, check if there's outstanding credit
        if (!$validated['enabled'] && $credit->enabled && $credit->current_credit > 0) {
            return back()->withErrors(['error' => 'Không thể tắt credit khi còn công nợ. Vui lòng thanh toán công nợ trước.'])->withInput();
        }

        // If reducing credit limit, check if current credit exceeds new limit
        if ($validated['credit_limit'] < $credit->current_credit) {
            return back()->withErrors(['error' => 'Hạn mức mới không được nhỏ hơn công nợ hiện tại.'])->withInput();
        }

        $credit->update($validated);

        return redirect()->route('admin.credits.index')
            ->with('success', 'Đã cập nhật cài đặt credit cho user thành công.');
    }

    /**
     * Pay credit from wallet.
     */
    public function payFromWallet(Request $request, User $user)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $credit = $user->credit;
        if (!$credit || !$credit->enabled) {
            return back()->withErrors(['error' => 'User không có credit được kích hoạt.']);
        }

        if ($validated['amount'] > $credit->current_credit) {
            return back()->withErrors(['error' => 'Số tiền thanh toán không được vượt quá công nợ hiện tại.'])->withInput();
        }

        $wallet = $user->wallet ?? Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
            'currency' => 'USD',
        ]);

        if ($wallet->balance < $validated['amount']) {
            return back()->withErrors(['error' => 'Số dư ví không đủ để thanh toán.'])->withInput();
        }

        DB::beginTransaction();
        try {
            // Deduct from wallet
            $wallet->deductBalance(
                $validated['amount'],
                "Thanh toán công nợ",
                $credit
            );

            // Update transaction type
            $transaction = $wallet->transactions()->latest()->first();
            $transaction->update([
                'type' => 'credit_payment',
                'created_by' => auth()->id(),
            ]);

            // Pay credit
            $credit->payCredit($validated['amount']);

            DB::commit();

            return redirect()->route('admin.credits.edit', $user)
                ->with('success', 'Đã thanh toán công nợ từ ví thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to pay credit from wallet', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Không thể thanh toán công nợ: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Adjust current debt manually (admin only).
     */
    public function adjustDebt(Request $request, User $user)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric'],
            'description' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:increase,decrease'],
        ]);

        $credit = $user->credit;
        if (!$credit || !$credit->enabled) {
            return back()->withErrors(['error' => 'User does not have credit enabled.']);
        }

        DB::beginTransaction();
        try {
            if ($validated['type'] === 'increase') {
                $credit->current_credit += $validated['amount'];
            } else {
                if ($validated['amount'] > $credit->current_credit) {
                    return back()->withErrors(['error' => 'Amount exceeds current debt.'])->withInput();
                }
                $credit->current_credit -= $validated['amount'];
            }
            $credit->save();

            // Log the adjustment
            Log::info('Admin adjusted credit debt', [
                'user_id' => $user->id,
                'admin_id' => auth()->id(),
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'description' => $validated['description'],
                'new_current_credit' => $credit->current_credit,
            ]);

            DB::commit();

            return redirect()->route('admin.credits.edit', $user)
                ->with('success', 'Credit debt adjusted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to adjust credit debt', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to adjust debt: ' . $e->getMessage()])->withInput();
        }
    }
}
