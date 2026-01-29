<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WalletTransaction;
use App\Models\TopUpRequest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WalletController extends Controller
{
    /**
     * Display wallet and transactions.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $wallet = $user->wallet;
        $credit = $user->credit;
        
        // Get wallet statistics
        $currentBalance = $wallet ? $wallet->balance : 0;
        $currency = $wallet ? $wallet->currency : 'USD';
        
        // Get credit statistics
        $currentCredit = ($credit && $credit->enabled) ? $credit->current_credit : 0;
        $creditLimit = ($credit && $credit->enabled) ? $credit->credit_limit : 0;
        $availableCredit = ($credit && $credit->enabled) ? $credit->available_credit : 0;
        
        // Calculate total spent (negative amounts or payment types)
        $totalSpent = WalletTransaction::where('user_id', $user->id)
            ->where(function($query) {
                $query->where('type', 'payment')
                    ->orWhere(function($q) {
                        $q->where('type', 'credit_used')
                            ->where('amount', '<', 0);
                    });
            })
            ->where('status', 'completed')
            ->sum(\DB::raw('ABS(amount)'));
        
        // If sum returned negative, make positive
        $totalSpent = abs($totalSpent);
        
        // Get pending transactions count and amount
        $pendingTransactions = WalletTransaction::where('user_id', $user->id)
            ->where('status', 'pending')
            ->get();
        $pendingAmount = $pendingTransactions->sum('amount');
        $pendingCount = $pendingTransactions->count();
        
        // Get filter parameters
        $search = $request->get('search');
        $type = $request->get('type');
        $dateRange = $request->get('date_range', 'last-30');
        
        // Build query for transactions
        $transactionsQuery = WalletTransaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');
        
        // Apply search filter
        if ($search) {
            $transactionsQuery->where(function($query) use ($search) {
                $query->where('id', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('reference_id', 'like', "%{$search}%");
            });
        }
        
        // Apply type filter
        if ($type) {
            if ($type === 'deposit') {
                $transactionsQuery->whereIn('type', ['top_up', 'refund', 'admin_adjustment'])
                    ->where('amount', '>', 0);
            } elseif ($type === 'payment') {
                $transactionsQuery->where(function($query) {
                    $query->where('type', 'payment')
                        ->orWhere(function($q) {
                            $q->where('type', 'credit_used')
                                ->where('amount', '<', 0);
                        });
                });
            } elseif ($type === 'refund') {
                $transactionsQuery->where('type', 'refund');
            } elseif ($type === 'credit') {
                $transactionsQuery->whereIn('type', ['credit_used', 'credit_payment']);
            }
        }
        
        // Apply date range filter
        if ($dateRange) {
            $dateRangeMap = [
                'last-30' => now()->subDays(30),
                'last-90' => now()->subDays(90),
                'custom' => null, // Handle separately if needed
            ];
            
            if (isset($dateRangeMap[$dateRange]) && $dateRangeMap[$dateRange]) {
                $transactionsQuery->where('created_at', '>=', $dateRangeMap[$dateRange]);
            }
        }
        
        // Get paginated transactions
        $transactions = $transactionsQuery->paginate(10)->withQueryString();
        
        // Calculate last month's total spent for comparison
        $lastMonthSpent = WalletTransaction::where('user_id', $user->id)
            ->where(function($query) {
                $query->where('type', 'payment')
                    ->orWhere(function($q) {
                        $q->where('type', 'credit_used')
                            ->where('amount', '<', 0);
                    });
            })
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(60))
            ->where('created_at', '<', now()->subDays(30))
            ->sum(\DB::raw('ABS(amount)'));
        
        $lastMonthSpent = abs($lastMonthSpent);
        
        $spentChange = $lastMonthSpent > 0 
            ? (($totalSpent - $lastMonthSpent) / $lastMonthSpent) * 100 
            : 0;
        
        return view('customer.top-up-requests.index', compact(
            'wallet',
            'credit',
            'currentBalance',
            'currency',
            'currentCredit',
            'creditLimit',
            'availableCredit',
            'totalSpent',
            'pendingAmount',
            'pendingCount',
            'transactions',
            'spentChange',
            'search',
            'type',
            'dateRange'
        ));
    }
}

