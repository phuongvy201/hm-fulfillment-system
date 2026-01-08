<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DebtPaymentRequest;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DebtPaymentRequestController extends Controller
{
    /**
     * Display a listing of user's debt payment requests.
     */
    public function index()
    {
        $requests = auth()->user()->debtPaymentRequests()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $credit = auth()->user()->credit;
        $currentDebt = $credit && $credit->enabled ? $credit->current_credit : 0;

        return view('customer.debt-payment-requests.index', compact('requests', 'currentDebt'));
    }

    /**
     * Show the form for creating a new debt payment request.
     */
    public function create(Request $request)
    {
        $credit = auth()->user()->credit;
        if (!$credit || !$credit->enabled || $credit->current_credit <= 0) {
            return redirect()->route('customer.debt-payment-requests.index')
                ->withErrors(['error' => 'You do not have any outstanding debt.']);
        }

        $paymentMethods = PaymentMethod::getActive();
        $selectedMethod = $request->get('method');
        if ($selectedMethod) {
            $selectedMethod = PaymentMethod::where('slug', $selectedMethod)->where('is_active', true)->first();
        }
        if (!$selectedMethod && $paymentMethods->count() > 0) {
            $selectedMethod = $paymentMethods->first();
        }

        // Generate transaction code
        $transactionCode = 'DPR-' . time() . '-' . rand(100, 999);

        return view('customer.debt-payment-requests.create', compact('credit', 'paymentMethods', 'selectedMethod', 'transactionCode'));
    }

    /**
     * Store a newly created debt payment request.
     */
    public function store(Request $request)
    {
        $credit = auth()->user()->credit;
        if (!$credit || !$credit->enabled || $credit->current_credit <= 0) {
            return back()->withErrors(['error' => 'You do not have any outstanding debt.']);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:' . $credit->current_credit],
            'currency' => ['required', 'string', 'size:3'],
            'payment_method' => ['required', 'string'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'transaction_code' => ['required', 'string', 'max:255'],
            'proof_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($request->hasFile('proof_file')) {
            $validated['proof_file'] = $request->file('proof_file')->store('debt-payment-proofs', 'public');
        }

        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending';

        try {
            DebtPaymentRequest::create($validated);

            return redirect()->route('customer.debt-payment-requests.index')
                ->with('success', 'Debt payment request submitted successfully. Waiting for admin approval.');
        } catch (\Exception $e) {
            Log::error('Failed to create debt payment request', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to submit request: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified debt payment request.
     */
    public function show(DebtPaymentRequest $debtPaymentRequest)
    {
        if ($debtPaymentRequest->user_id !== auth()->id()) {
            abort(403);
        }

        $debtPaymentRequest->load(['paymentMethod']);
        return view('customer.debt-payment-requests.show', compact('debtPaymentRequest'));
    }
}
