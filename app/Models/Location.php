<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = [
        'code',
        'name',
        'currency',
        'timezone',
        'status',
    ];

    // Location model is kept for other purposes
    // Pricing now uses Market instead of Location
}
