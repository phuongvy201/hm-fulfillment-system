<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportFile extends Model
{
    protected $fillable = [
        'file_name',
        'file_path',
        'file_url',
        'original_name',
        'file_size',
        'mime_type',
        'uploaded_by',
        'total_orders',
        'processed_orders',
        'failed_orders',
        'status',
        'errors',
        'order_data',
        'processed_at',
    ];

    protected $casts = [
        'errors' => 'array',
        'order_data' => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the user who uploaded the file
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
