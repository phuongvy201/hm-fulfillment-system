<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class TeamDesignPrice extends Model
{
    protected $fillable = [
        'team_id',
        'first_side_price_vnd',
        'additional_side_price_vnd',
        'status',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'first_side_price_vnd' => 'decimal:2',
        'additional_side_price_vnd' => 'decimal:2',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
    ];

    /**
     * Get the team.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Scope to get active prices.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get prices effective for a specific date.
     */
    public function scopeEffectiveFor($query, $date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();
        
        return $query->where(function ($q) use ($date) {
            $q->whereNull('valid_from')
              ->orWhere('valid_from', '<=', $date);
        })
        ->where(function ($q) use ($date) {
            $q->whereNull('valid_to')
              ->orWhere('valid_to', '>=', $date);
        });
    }

    /**
     * Get the current active design price for a team.
     */
    public static function getCurrentPriceForTeam($teamId, $date = null): ?self
    {
        return static::active()
            ->effectiveFor($date)
            ->where('team_id', $teamId)
            ->orderBy('valid_from', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
