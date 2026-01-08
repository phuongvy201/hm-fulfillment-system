<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopUpRequest extends Model
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
     * Get the user that owns the top-up request.
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
     * Get the payment method used for this top-up.
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Approve the top-up request.
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

        // Add balance to user's wallet
        $wallet = $this->user->wallet ?? Wallet::create([
            'user_id' => $this->user_id,
            'balance' => 0,
            'currency' => 'USD',
        ]);

        $wallet->addBalance(
            $this->amount,
            "Top-up via {$this->payment_method}",
            $this
        );
    }

    /**
     * Reject the top-up request.
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
