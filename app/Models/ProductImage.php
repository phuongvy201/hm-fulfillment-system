<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'image_path',
        'sort_order',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the product that owns the image.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the full URL of the image.
     * Format: https://s3.{region}.amazonaws.com/{bucket}/{path}
     * Example: https://s3.us-east-1.amazonaws.com/image.bluprinter/products/images/filename.jpg
     */
    public function getUrlAttribute(): string
    {
        $region = config('filesystems.disks.s3.region', 'us-east-1');
        $bucket = config('filesystems.disks.s3.bucket');

        // Always build path-style URL: https://s3.{region}.amazonaws.com/{bucket}/{path}
        // This format works better with bucket policies and doesn't require DNS setup
        $path = ltrim($this->image_path, '/');
        return "https://s3.{$region}.amazonaws.com/{$bucket}/{$path}";
    }
}
