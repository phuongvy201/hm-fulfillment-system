<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    /**
     * Get the users for the team.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the custom prices for the team.
     */
    public function customPrices(): HasMany
    {
        return $this->hasMany(TeamPrice::class);
    }
}
