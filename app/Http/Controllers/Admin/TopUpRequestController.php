<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TopUpRequest;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TopUpRequestController extends Controller
{
    /**
     * Display a listing of top-up requests.
     */
    public function index(Request $request)
    {
        $query = TopUpRequest::with(['user', 'approver', 'paymentMethod']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by currency
        if ($request->filled('currency')) {
            $query->where('currency', $request->currency);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $requests = $query->paginate(20)->withQueryString();
        $users = User::whereDoesntHave('role', function ($q) {
            $q->whereIn('slug', ['super-admin', 'it-admin']);
        })->orderBy('name')->get();

        $currencies = TopUpRequest::distinct()->pluck('currency');

        return view('admin.top-up-requests.index', compact('requests', 'users', 'currencies'));
    }


    /**
     * Display the specified top-up request.
     */
    public function show(TopUpRequest $topUpRequest)
    {
        $topUpRequest->load(['user', 'approver']);

        return view('admin.top-up-requests.show', compact('topUpRequest'));
    }

    /**
     * Approve a top-up request.
     */
    public function approve(Request $request, TopUpRequest $topUpRequest)
    {
        if ($topUpRequest->status !== 'pending') {
            return back()->withErrors(['error' => 'This request has already been processed.']);
        }

        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $topUpRequest->approve(auth()->user(), $validated['admin_notes'] ?? null);

            return redirect()->route('admin.top-up-requests.index')
                ->with('success', 'Top-up request approved successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to approve top-up request', [
                'request_id' => $topUpRequest->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to approve request: ' . $e->getMessage()]);
        }
    }

    /**
     * Reject a top-up request.
     */
    public function reject(Request $request, TopUpRequest $topUpRequest)
    {
        if ($topUpRequest->status !== 'pending') {
            return back()->withErrors(['error' => 'This request has already been processed.']);
        }

        $validated = $request->validate([
            'admin_notes' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $topUpRequest->reject(auth()->user(), $validated['admin_notes']);

            return redirect()->route('admin.top-up-requests.index')
                ->with('success', 'Top-up request rejected successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to reject top-up request', [
                'request_id' => $topUpRequest->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to reject request: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing a top-up request.
     */
    public function edit(TopUpRequest $topUpRequest)
    {
        $topUpRequest->load(['user', 'approver', 'paymentMethod']);
        return view('admin.top-up-requests.edit', compact('topUpRequest'));
    }

    /**
     * Update a top-up request.
     */
    public function update(Request $request, TopUpRequest $topUpRequest)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
            'amount' => ['nullable', 'numeric', 'min:0.01'],
        ]);

        // If status changed from pending to approved, add balance
        if ($topUpRequest->status === 'pending' && $validated['status'] === 'approved') {
            try {
                $topUpRequest->approve(auth()->user(), $validated['admin_notes'] ?? null);
                
                if (isset($validated['amount']) && $validated['amount'] != $topUpRequest->amount) {
                    // Adjust the wallet balance if amount changed
                    $wallet = $topUpRequest->user->wallet;
                    if ($wallet) {
                        $difference = $validated['amount'] - $topUpRequest->amount;
                        if ($difference != 0) {
                            if ($difference > 0) {
                                $wallet->addBalance($difference, "Top-up amount adjustment", $topUpRequest);
                            } else {
                                $wallet->deductBalance(abs($difference), "Top-up amount adjustment", $topUpRequest);
                            }
                        }
                    }
                    $topUpRequest->amount = $validated['amount'];
                    $topUpRequest->save();
                }

                return redirect()->route('admin.top-up-requests.index')
                    ->with('success', 'Top-up request updated and approved successfully.');
            } catch (\Exception $e) {
                Log::error('Failed to update and approve top-up request', [
                    'request_id' => $topUpRequest->id,
                    'error' => $e->getMessage(),
                ]);
                return back()->withErrors(['error' => 'Failed to approve: ' . $e->getMessage()]);
            }
        } else {
            // Just update status and notes
            $topUpRequest->update([
                'status' => $validated['status'],
                'admin_notes' => $validated['admin_notes'] ?? $topUpRequest->admin_notes,
            ]);

            if ($validated['status'] !== 'pending' && !$topUpRequest->approved_by) {
                $topUpRequest->update([
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);
            }

            return redirect()->route('admin.top-up-requests.index')
                ->with('success', 'Top-up request updated successfully.');
        }
    }
}
