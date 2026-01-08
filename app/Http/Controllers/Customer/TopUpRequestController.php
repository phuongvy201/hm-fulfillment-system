<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TopUpRequest;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TopUpRequestController extends Controller
{
    /**
     * Display a listing of user's top-up requests.
     */
    public function index()
    {
        $requests = auth()->user()->topUpRequests()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('customer.top-up-requests.index', compact('requests'));
    }

    /**
     * Show the form for creating a new top-up request.
     */
    public function create(Request $request)
    {
        $wallet = auth()->user()->wallet;
        $paymentMethods = PaymentMethod::getActive();
        
        // Get selected payment method from query or default to first
        $selectedMethod = $request->get('method');
        if ($selectedMethod) {
            $selectedMethod = PaymentMethod::where('slug', $selectedMethod)->where('is_active', true)->first();
        }
        if (!$selectedMethod && $paymentMethods->count() > 0) {
            $selectedMethod = $paymentMethods->first();
        }
        
        // Generate transaction code
        $transactionCode = 'TXN-' . time() . '-' . rand(100, 999);
        
        return view('customer.top-up-requests.create', compact('wallet', 'paymentMethods', 'selectedMethod', 'transactionCode'));
    }

    /**
     * Store a newly created top-up request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'size:3'],
            'payment_method' => ['required', 'in:bank_transfer,lianpay,pingpong,worldfirst,payoneer'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'transaction_code' => ['required', 'string', 'max:255'],
            'proof_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Validate minimum amount based on payment method
        if ($request->payment_method_id) {
            $paymentMethod = PaymentMethod::find($request->payment_method_id);
            if ($paymentMethod && $validated['amount'] < $paymentMethod->min_amount) {
                return back()->withErrors(['amount' => "Số tiền tối thiểu là {$paymentMethod->currency} " . number_format($paymentMethod->min_amount, 2)]);
            }
            if ($paymentMethod && $paymentMethod->max_amount && $validated['amount'] > $paymentMethod->max_amount) {
                return back()->withErrors(['amount' => "Số tiền tối đa là {$paymentMethod->currency} " . number_format($paymentMethod->max_amount, 2)]);
            }
        }

        if ($request->hasFile('proof_file')) {
            $validated['proof_file'] = $request->file('proof_file')->store('top-up-proofs', 'public');
        }

        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending';

        TopUpRequest::create($validated);

        return redirect()->route('customer.top-up-requests.index')
            ->with('success', 'Đã tạo yêu cầu top-up thành công. Vui lòng chờ admin duyệt.');
    }

    /**
     * Display the specified top-up request.
     */
    public function show(TopUpRequest $topUpRequest)
    {
        // Ensure user can only view their own requests
        if ($topUpRequest->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $topUpRequest->load('approver');

        return view('customer.top-up-requests.show', compact('topUpRequest'));
    }
}
