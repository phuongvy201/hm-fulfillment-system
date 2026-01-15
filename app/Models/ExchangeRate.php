<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ExchangeRate extends Model
{
    protected $fillable = [
        'from_currency',
        'to_currency',
        'rate',
        'effective_date',
        'expires_at',
        'created_by',
        'status',
        'notes',
    ];

    protected $casts = [
        'rate' => 'decimal:6',
        'effective_date' => 'date',
        'expires_at' => 'date',
    ];

    /**
     * Get the user who created this exchange rate.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get active rates.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get rates effective for a specific date.
     */
    public function scopeEffectiveFor($query, $date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();
        
        return $query->where('effective_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', $date);
            });
    }

    /**
     * Scope to get rates for a currency pair.
     */
    public function scopeForPair($query, string $fromCurrency, string $toCurrency)
    {
        return $query->where('from_currency', $fromCurrency)
            ->where('to_currency', $toCurrency);
    }

    /**
     * Get the current active exchange rate for a currency pair.
     */
    public static function getCurrentRate(string $fromCurrency, string $toCurrency, $date = null): ?float
    {
        if ($fromCurrency === $toCurrency) {
            return 1.0;
        }

        $rate = static::active()
            ->effectiveFor($date)
            ->forPair($fromCurrency, $toCurrency)
            ->orderBy('effective_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        return $rate ? (float) $rate->rate : null;
    }
}
