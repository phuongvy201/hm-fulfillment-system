<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DebtPaymentRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class DebtPaymentRequestController extends Controller
{
    /**
     * Display a listing of debt payment requests.
     */
    public function index(Request $request)
    {
        $query = DebtPaymentRequest::with(['user', 'approver', 'paymentMethod']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
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

        return view('admin.debt-payment-requests.index', compact('requests', 'users'));
    }

    /**
     * Display the specified debt payment request.
     */
    public function show(DebtPaymentRequest $debtPaymentRequest)
    {
        $debtPaymentRequest->load(['user', 'approver', 'paymentMethod']);
        return view('admin.debt-payment-requests.show', compact('debtPaymentRequest'));
    }

    /**
     * Approve a debt payment request.
     */
    public function approve(Request $request, DebtPaymentRequest $debtPaymentRequest)
    {
        if ($debtPaymentRequest->status !== 'pending') {
            return back()->withErrors(['error' => 'This request has already been processed.']);
        }

        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $debtPaymentRequest->approve(auth()->user(), $validated['admin_notes'] ?? null);

            return redirect()->route('admin.debt-payment-requests.index')
                ->with('success', 'Debt payment request approved successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to approve debt payment request', [
                'request_id' => $debtPaymentRequest->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to approve request: ' . $e->getMessage()]);
        }
    }

    /**
     * Reject a debt payment request.
     */
    public function reject(Request $request, DebtPaymentRequest $debtPaymentRequest)
    {
        if ($debtPaymentRequest->status !== 'pending') {
            return back()->withErrors(['error' => 'This request has already been processed.']);
        }

        $validated = $request->validate([
            'admin_notes' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $debtPaymentRequest->reject(auth()->user(), $validated['admin_notes']);

            return redirect()->route('admin.debt-payment-requests.index')
                ->with('success', 'Debt payment request rejected.');
        } catch (\Exception $e) {
            Log::error('Failed to reject debt payment request', [
                'request_id' => $debtPaymentRequest->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to reject request: ' . $e->getMessage()]);
        }
    }
}
