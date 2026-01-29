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
