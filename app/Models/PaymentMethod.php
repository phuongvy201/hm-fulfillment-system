<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type',
        'bank_name',
        'account_number',
        'account_holder',
        'qr_code',
        'instructions',
        'min_amount',
        'max_amount',
        'currency',
        'exchange_rate',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get active payment methods ordered by sort_order.
     */
    public static function getActive()
    {
        return static::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
