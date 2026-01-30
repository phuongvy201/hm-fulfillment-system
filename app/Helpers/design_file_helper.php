<?php

if (!function_exists('getDesignFileUrl')) {
    /**
     * Get file URL from S3 or local storage
     *
     * @param string|null $filePath
     * @return string|null
     */
    function getDesignFileUrl($filePath)
    {
        if (!$filePath) {
            return null;
        }

        try {
            // Check if file exists on S3
            if (\Illuminate\Support\Facades\Storage::disk('s3')->exists($filePath)) {
                $s3Config = config('filesystems.disks.s3');
                $bucket = $s3Config['bucket'] ?? '';
                $region = $s3Config['region'] ?? 'us-east-1';
                // Construct S3 URL in path-style format: https://s3.{region}.amazonaws.com/{bucket}/{path}
                return "https://s3.{$region}.amazonaws.com/{$bucket}/{$filePath}";
            }
        } catch (\Exception $e) {
            // Fallback to local storage
        }

        // Fallback to local storage for backward compatibility
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($filePath)) {
            return asset('storage/' . $filePath);
        }

        return null;
    }
}

