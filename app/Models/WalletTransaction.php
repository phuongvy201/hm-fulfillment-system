<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'user_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'reference_type',
        'reference_id',
        'status',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    /**
     * Get the wallet that owns the transaction.
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Get the user that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who created the transaction.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the reference model (polymorphic).
     */
    public function reference(): MorphTo
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }
    
    /**
     * Override getAttribute to safely handle invalid reference_type.
     */
    public function getAttribute($key)
    {
        // If trying to access reference and reference_type is invalid, return null
        if ($key === 'reference' && $this->attributes['reference_type'] ?? null) {
            $referenceType = $this->attributes['reference_type'];
            // Check if reference_type is a valid class name (not a number or invalid string)
            if (!class_exists($referenceType)) {
                // Return null instead of trying to load invalid class
                return null;
            }
        }
        
        return parent::getAttribute($key);
    }
    
    /**
     * Safely get reference model with null check.
     */
    public function getReferenceSafeAttribute()
    {
        $referenceType = $this->attributes['reference_type'] ?? null;
        
        if (!$referenceType || !class_exists($referenceType)) {
            return null;
        }
        
        try {
            // Only load if reference_id exists and is valid
            if (!$this->reference_id) {
                return null;
            }
            
            return $this->reference;
        } catch (\Exception $e) {
            // If reference doesn't exist or class is invalid, return null
            return null;
        }
    }

    /**
     * Get transaction type display name.
     */
    public function getTypeDisplayAttribute(): string
    {
        $types = [
            'top_up' => 'Deposit',
            'payment' => 'Order Payment',
            'credit_used' => 'Credit Used',
            'credit_payment' => 'Credit Payment',
            'admin_adjustment' => 'Admin Adjustment',
            'refund' => 'Refund',
        ];

        return $types[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    /**
     * Get transaction ID with short ID.
     */
    public function getDisplayIdAttribute(): string
    {
        // Check if reference_type is valid class name
        if ($this->reference_type && class_exists($this->reference_type)) {
            try {
                $reference = $this->reference_safe;
                if (!$reference) {
                    return 'TXN-' . str_pad($this->id, 9, '0', STR_PAD_LEFT);
                }
                
                if ($this->reference_type === 'App\Models\TopUpRequest') {
                    return $reference->transaction_code ?? 'TXN-' . $this->id;
                }
                
                if ($this->reference_type === 'App\Models\Order') {
                    return $reference->order_number ?? 'TXN-' . $this->id;
                }
            } catch (\Exception $e) {
                // If reference doesn't exist, fall through to default
            }
        }
        
        return 'TXN-' . str_pad($this->id, 9, '0', STR_PAD_LEFT);
    }

    /**
     * Get short reference ID.
     */
    public function getShortReferenceIdAttribute(): ?string
    {
        // Check if reference_type is valid class name
        if (!$this->reference_type || !class_exists($this->reference_type)) {
            return null;
        }

        try {
            $reference = $this->reference_safe;
            if (!$reference) {
                return null;
            }

            if ($this->reference_type === 'App\Models\TopUpRequest') {
                return $this->status === 'pending' ? 'Processing...' : 'ID: ' . substr(str_replace('-', '', $reference->transaction_code ?? ''), 0, 12);
            }
            
            if ($this->reference_type === 'App\Models\Order') {
                return 'INV: ' . ($reference->invoice_number ?? $reference->order_number);
            }
        } catch (\Exception $e) {
            // If reference doesn't exist or can't be loaded, return null
            return null;
        }

        return null;
    }

    /**
     * Get status badge color.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'completed' => 'emerald',
            'pending' => 'amber',
            'failed' => 'red',
            'cancelled' => 'slate',
            default => 'slate',
        };
    }
}
