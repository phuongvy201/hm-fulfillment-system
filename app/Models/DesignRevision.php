<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesignRevision extends Model
{
    protected $fillable = [
        'design_task_id',
        'designer_id',
        'design_file',
        'notes',
        'revision_notes',
        'version',
        'status',
        'submitted_at',
        'approved_at',
    ];

    protected $casts = [
        'version' => 'integer',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * Get design files as array (handles both single file and JSON array)
     */
    public function getDesignFilesAttribute()
    {
        if (!$this->design_file) {
            return [];
        }

        // Try to decode as JSON (multiple files)
        $decoded = json_decode($this->design_file, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // Return as single file array
        return [$this->design_file];
    }

    /**
     * Get the design task that owns this revision.
     */
    public function designTask(): BelongsTo
    {
        return $this->belongsTo(DesignTask::class);
    }

    /**
     * Get the designer who created this revision.
     */
    public function designer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'designer_id');
    }
}
