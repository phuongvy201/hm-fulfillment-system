<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DebtPaymentRequest extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'currency',
        'payment_method',
        'payment_method_id',
        'transaction_code',
        'proof_file',
        'notes',
        'status',
        'admin_notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the user that owns the debt payment request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who approved/rejected the request.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the payment method used for this debt payment.
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Approve the debt payment request.
     */
    public function approve(User $admin, string $notes = null): void
    {
        $this->status = 'approved';
        $this->approved_by = $admin->id;
        $this->approved_at = now();
        if ($notes) {
            $this->admin_notes = $notes;
        }
        $this->save();

        // Reduce user's credit debt
        $credit = $this->user->credit;
        if ($credit && $credit->enabled) {
            $credit->payCredit($this->amount);
        }
    }

    /**
     * Reject the debt payment request.
     */
    public function reject(User $admin, string $reason): void
    {
        $this->status = 'rejected';
        $this->approved_by = $admin->id;
        $this->approved_at = now();
        $this->admin_notes = $reason;
        $this->save();
    }
}
