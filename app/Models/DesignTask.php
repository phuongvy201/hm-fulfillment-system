<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DesignTask extends Model
{
    protected $fillable = [
        'customer_id',
        'designer_id',
        'title',
        'description',
        'sides_count',
        'price',
        'status',
        'mockup_file',
        'design_file',
        'revision_notes',
        'completed_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sides_count' => 'integer',
        'completed_at' => 'datetime',
        'mockup_file' => 'array', // Store as JSON array for multiple files
    ];

    /**
     * Get the customer that owns the design task.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the designer assigned to the design task.
     */
    public function designer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'designer_id');
    }

    /**
     * Get all revisions for this design task.
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(DesignRevision::class);
    }

    /**
     * Get all comments for this design task.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(DesignComment::class);
    }

    /**
     * Get the latest revision.
     */
    public function latestRevision()
    {
        return $this->hasOne(DesignRevision::class)->latestOfMany();
    }
}
