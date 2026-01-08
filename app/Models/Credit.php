<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Credit extends Model
{
    protected $fillable = [
        'user_id',
        'credit_limit',
        'current_credit',
        'enabled',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'current_credit' => 'decimal:2',
        'enabled' => 'boolean',
    ];

    /**
     * Get the user that owns the credit.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get available credit (credit_limit - current_credit).
     */
    public function getAvailableCreditAttribute(): float
    {
        return max(0, $this->credit_limit - $this->current_credit);
    }

    /**
     * Check if user can use credit for amount.
     */
    public function canUseCredit(float $amount): bool
    {
        if (!$this->enabled) {
            return false;
        }

        return $this->available_credit >= $amount;
    }

    /**
     * Use credit.
     */
    public function useCredit(float $amount): void
    {
        if (!$this->canUseCredit($amount)) {
            throw new \Exception('Insufficient credit limit');
        }

        $this->current_credit += $amount;
        $this->save();
    }

    /**
     * Pay credit.
     */
    public function payCredit(float $amount): void
    {
        if ($amount > $this->current_credit) {
            throw new \Exception('Payment amount exceeds current credit');
        }

        $this->current_credit -= $amount;
        $this->save();
    }
}
