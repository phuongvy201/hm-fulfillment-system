<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DesignTask;
use App\Models\DesignRevision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Aws\S3\S3Client;
use Aws\S3\MultipartUploader;
use Aws\Exception\MultipartUploadException;

class DesignRevisionController extends Controller
{
    /**
     * Upload file to S3
     */
    private function uploadToS3($file, $folder = 'design-tasks/revisions')
    {
        try {
            // Validate file
            if (!$file->isValid()) {
                Log::warning('Invalid file', [
                    'file' => $file->getClientOriginalName(),
                ]);
                return null;
            }

            // Generate unique filename
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $filePath = "{$folder}/{$fileName}";

            // Check S3 configuration
            $s3Config = config('filesystems.disks.s3');
            Log::info('S3 config check', [
                'bucket' => $s3Config['bucket'] ?? 'NOT SET',
                'region' => $s3Config['region'] ?? 'NOT SET',
                'key_set' => !empty($s3Config['key']),
                'secret_set' => !empty($s3Config['secret']),
            ]);

            // Upload using AWS SDK directly
            $uploaded = false;
            try {
                $originalThrow = config('filesystems.disks.s3.throw', false);
                config(['filesystems.disks.s3.throw' => true]);

                $s3Client = new S3Client([
                    'version' => 'latest',
                    'region' => $s3Config['region'],
                    'credentials' => [
                        'key' => $s3Config['key'],
                        'secret' => $s3Config['secret'],
                    ],
                    'use_path_style_endpoint' => $s3Config['use_path_style_endpoint'] ?? false,
                ]);

                $result = $s3Client->putObject([
                    'Bucket' => $s3Config['bucket'],
                    'Key' => $filePath,
                    'Body' => file_get_contents($file->getRealPath()),
                    'ContentType' => $file->getMimeType(),
                ]);

                $uploaded = $result['@metadata']['statusCode'] === 200;
                config(['filesystems.disks.s3.throw' => $originalThrow]);

                Log::info('File upload attempt', [
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'uploaded' => $uploaded,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            } catch (\Aws\S3\Exception\S3Exception $s3Exception) {
                Log::error('S3 Exception (AWS)', [
                    'file_name' => $fileName,
                    'error' => $s3Exception->getMessage(),
                    'aws_code' => $s3Exception->getAwsErrorCode(),
                    'aws_message' => $s3Exception->getAwsErrorMessage(),
                ]);
                $uploaded = false;
            } catch (\Exception $uploadException) {
                Log::error('S3 upload exception', [
                    'file_name' => $fileName,
                    'error' => $uploadException->getMessage(),
                    'class' => get_class($uploadException),
                ]);
                $uploaded = false;
            }

            // Verify file exists on S3
            if ($uploaded) {
                $exists = Storage::disk('s3')->exists($filePath);
                Log::info('File existence check', [
                    'file_path' => $filePath,
                    'exists' => $exists,
                ]);

                if ($exists) {
                    return $filePath;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error uploading file to S3', [
                'file' => $file->getClientOriginalName() ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Delete file from S3
     */
    private function deleteFromS3($filePath)
    {
        try {
            if ($filePath && Storage::disk('s3')->exists($filePath)) {
                Storage::disk('s3')->delete($filePath);
                Log::info('File deleted from S3', ['file_path' => $filePath]);
                return true;
            }
        } catch (\Exception $e) {
            Log::error('Error deleting file from S3', [
                'file_path' => $filePath,
                'error' => $e->getMessage(),
            ]);
        }
        return false;
    }

    /**
     * Get file URL (S3 or local storage)
     */
    public static function getFileUrl($filePath)
    {
        if (!$filePath) {
            return null;
        }

        try {
            // Check if file exists on S3
            if (Storage::disk('s3')->exists($filePath)) {
                $s3Config = config('filesystems.disks.s3');
                $bucket = $s3Config['bucket'] ?? '';
                $region = $s3Config['region'] ?? 'us-east-1';
                // Construct S3 URL in path-style format: https://s3.{region}.amazonaws.com/{bucket}/{path}
                $url = "https://s3.{$region}.amazonaws.com/{$bucket}/{$filePath}";
                return $url;
            }
        } catch (\Exception $e) {
            Log::warning('Error checking S3 file', ['file_path' => $filePath, 'error' => $e->getMessage()]);
        }

        // Fallback to local storage for backward compatibility
        if (Storage::disk('public')->exists($filePath)) {
            return asset('storage/' . $filePath);
        }

        return null;
    }
    /**
     * Store a newly created revision.
     */
    public function store(Request $request, DesignTask $designTask)
    {
        $user = auth()->user();
        $isDesigner = $user->hasRole('designer') || $user->isSuperAdmin();

        if (!$isDesigner || $designTask->designer_id !== $user->id) {
            abort(403, 'Only the assigned designer can submit revisions.');
        }

        // Validate based on sides_count
        $requiredFiles = $designTask->sides_count;
        $validationRules = [
            'notes' => 'nullable|string',
        ];

        // Add validation for each required file (100MB = 102400 KB)
        for ($i = 0; $i < $requiredFiles; $i++) {
            if ($i === 0) {
                $validationRules['design_files.' . $i] = 'required|file|mimes:jpg,jpeg,png,pdf,psd,ai|max:102400';
            } else {
                $validationRules['design_files.' . $i] = 'nullable|file|mimes:jpg,jpeg,png,pdf,psd,ai|max:102400';
            }
        }

        $validated = $request->validate($validationRules);

        // Get next version number
        $latestRevision = $designTask->revisions()->latest('version')->first();
        $nextVersion = $latestRevision ? $latestRevision->version + 1 : 1;

        // Handle multiple design file uploads to S3
        $designFiles = [];
        if ($request->hasFile('design_files')) {
            foreach ($request->file('design_files') as $index => $file) {
                if ($file && $file->isValid()) {
                    $path = $this->uploadToS3($file, 'design-tasks/revisions');
                    if ($path) {
                        $designFiles[] = $path;
                    }
                }
            }
        }

        // Ensure at least the first file is uploaded
        if (empty($designFiles)) {
            return redirect()->back()->withErrors(['design_files.0' => 'At least the first design file is required.'])->withInput();
        }

        // Store files as JSON array (or single path if only one file)
        $designFile = count($designFiles) === 1 ? $designFiles[0] : json_encode($designFiles);

        $revision = new DesignRevision();
        $revision->design_task_id = $designTask->id;
        $revision->designer_id = $user->id;
        $revision->design_file = $designFile;
        $revision->notes = $validated['notes'] ?? null;
        $revision->version = $nextVersion;
        $revision->status = 'submitted';
        $revision->submitted_at = now();
        $revision->save();

        // Update task status
        $designTask->status = 'completed';
        // Store first file as main design file for backward compatibility
        $designTask->design_file = $designFiles[0];
        $designTask->save();

        return redirect()->back()->with('success', 'Revision submitted successfully.');
    }

    /**
     * Approve a revision.
     */
    public function approve(DesignTask $designTask, DesignRevision $designRevision)
    {
        $user = auth()->user();
        $isCustomer = $user->hasRole('customer') && !$user->isSuperAdmin();

        if (!$isCustomer || $designTask->customer_id !== $user->id) {
            abort(403, 'Only the customer can approve revisions.');
        }

        if ($designRevision->design_task_id !== $designTask->id) {
            abort(404, 'Revision not found for this task.');
        }

        $designRevision->status = 'approved';
        $designRevision->approved_at = now();
        $designRevision->save();

        // Update task
        $designTask->status = 'approved';
        $designTask->design_file = $designRevision->design_file;
        $designTask->save();

        return redirect()->back()->with('success', 'Revision approved successfully.');
    }

    /**
     * Request revision.
     */
    public function requestRevision(Request $request, DesignTask $designTask, DesignRevision $designRevision)
    {
        $user = auth()->user();
        $isCustomer = $user->hasRole('customer') && !$user->isSuperAdmin();

        if (!$isCustomer || $designTask->customer_id !== $user->id) {
            abort(403, 'Only the customer can request revisions.');
        }

        $validated = $request->validate([
            'revision_notes' => 'required|string',
        ]);

        $designRevision->revision_notes = $validated['revision_notes'];
        $designRevision->status = 'revision_requested';
        $designRevision->save();

        // Update task
        $designTask->status = 'revision';
        $designTask->revision_notes = $validated['revision_notes'];
        $designTask->save();

        return redirect()->back()->with('success', 'Revision requested successfully.');
    }

    /**
     * Update an existing revision (for updating submitted design).
     */
    public function update(Request $request, DesignTask $designTask, DesignRevision $designRevision)
    {
        $user = auth()->user();
        $isDesigner = $user->hasRole('designer') && !$user->isSuperAdmin();

        if (!$isDesigner || $designRevision->designer_id !== $user->id) {
            abort(403, 'Only the assigned designer can update revisions.');
        }

        if ($designRevision->design_task_id !== $designTask->id) {
            abort(404, 'Revision not found for this task.');
        }

        // Only allow updating submitted or revision_requested revisions
        if (!in_array($designRevision->status, ['submitted', 'revision_requested'])) {
            abort(403, 'You can only update submitted or revision-requested revisions.');
        }

        // Validate based on sides_count (same as store method)
        $requiredFiles = $designTask->sides_count;
        $validationRules = [
            'notes' => 'nullable|string',
            'save_as_draft' => 'nullable|boolean',
        ];

        // Add validation for each required file (100MB = 102400 KB)
        for ($i = 0; $i < $requiredFiles; $i++) {
            if ($i === 0) {
                $validationRules['design_files.' . $i] = 'required|file|mimes:jpg,jpeg,png,pdf,psd,ai|max:102400'; // Max 100MB
            } else {
                $validationRules['design_files.' . $i] = 'nullable|file|mimes:jpg,jpeg,png,pdf,psd,ai|max:102400';
            }
        }

        $validated = $request->validate($validationRules);

        // Handle multiple design file uploads to S3
        $designFiles = [];
        if ($request->hasFile('design_files')) {
            foreach ($request->file('design_files') as $index => $file) {
                if ($file && $file->isValid()) {
                    $path = $this->uploadToS3($file, 'design-tasks/revisions');
                    if ($path) {
                        $designFiles[] = $path;
                    }
                }
            }
        }

        // Ensure at least the first file is uploaded
        if (empty($designFiles)) {
            return redirect()->back()->withErrors(['design_files.0' => 'At least the first design file is required.'])->withInput();
        }

        // Delete old files from S3
        $oldDesignFiles = $designRevision->design_files; // Use accessor to get array
        if (is_array($oldDesignFiles)) {
            foreach ($oldDesignFiles as $oldFile) {
                $this->deleteFromS3($oldFile);
            }
        } else {
            $this->deleteFromS3($designRevision->design_file);
        }

        // Store files as JSON array (or single path if only one file)
        $designFile = count($designFiles) === 1 ? $designFiles[0] : json_encode($designFiles);

        // Update revision
        $designRevision->design_file = $designFile;
        if (isset($validated['notes'])) {
            $designRevision->notes = $validated['notes'];
        }

        // If not saving as draft, update status and task
        if (!($validated['save_as_draft'] ?? false)) {
            $designRevision->status = 'submitted';
            $designRevision->submitted_at = now();

            // Update task
            $designTask->status = 'completed';
            $designTask->design_file = $designFiles[0]; // Store first file as main design file for backward compatibility
            $designTask->save();
        }

        $designRevision->save();

        $message = $validated['save_as_draft'] ?? false
            ? 'Draft saved successfully.'
            : 'Revision updated and submitted successfully.';

        return redirect()->back()->with('success', $message);
    }

    /**
     * Delete a revision.
     */
    public function destroy(DesignTask $designTask, DesignRevision $designRevision)
    {
        $user = auth()->user();
        $isDesigner = $user->hasRole('designer') || $user->isSuperAdmin();

        if (!$isDesigner || $designRevision->designer_id !== $user->id) {
            abort(403, 'You can only delete your own revisions.');
        }

        if ($designRevision->design_task_id !== $designTask->id) {
            abort(404, 'Revision not found for this task.');
        }

        // Delete file from S3
        if ($designRevision->design_file) {
            $designFiles = is_array($designRevision->design_file) ? $designRevision->design_file : (is_string($designRevision->design_file) ? json_decode($designRevision->design_file, true) : [$designRevision->design_file]);
            if (is_array($designFiles)) {
                foreach ($designFiles as $file) {
                    $this->deleteFromS3($file);
                }
            } else {
                $this->deleteFromS3($designRevision->design_file);
            }
        }

        $designRevision->delete();

        return redirect()->back()->with('success', 'Revision deleted successfully.');
    }

    /**
     * Initialize multipart upload for design files
     * Returns UploadId and key for each file
     */
    public function initMultipartUpload(Request $request, DesignTask $designTask)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.'
                ], 401);
            }

            $isDesigner = $user->hasRole('designer') && !$user->isSuperAdmin();

            if (!$isDesigner || $designTask->designer_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only the assigned designer can upload files.'
                ], 403);
            }

            $validated = $request->validate([
                'files' => 'required|array|min:1|max:' . $designTask->sides_count,
                'files.*.name' => 'required|string',
                'files.*.size' => 'required|integer|min:1|max:104857600', // Max 100MB (100 * 1024 * 1024)
                'files.*.type' => 'required|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in initMultipartUpload', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }

        try {
            $s3Config = config('filesystems.disks.s3');
            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => $s3Config['region'],
                'credentials' => [
                    'key' => $s3Config['key'],
                    'secret' => $s3Config['secret'],
                ],
                'use_path_style_endpoint' => $s3Config['use_path_style_endpoint'] ?? false,
            ]);

            $uploads = [];
            foreach ($validated['files'] as $index => $file) {
                // Generate unique filename
                $fileName = time() . '_' . Str::random(10) . '_' . $file['name'];
                $filePath = "design-tasks/revisions/{$fileName}";

                // Initialize multipart upload
                $result = $s3Client->createMultipartUpload([
                    'Bucket' => $s3Config['bucket'],
                    'Key' => $filePath,
                    'ContentType' => $file['type'],
                ]);

                $uploads[] = [
                    'index' => $index,
                    'key' => $filePath,
                    'upload_id' => $result['UploadId'],
                    'file_name' => $file['name'],
                ];
            }

            return response()->json([
                'success' => true,
                'uploads' => $uploads,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to initialize multipart upload', [
                'error' => $e->getMessage(),
                'task_id' => $designTask->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initialize multipart upload: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate presigned URLs for multipart upload parts
     */
    public function getMultipartPartUrls(Request $request, DesignTask $designTask)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.'
                ], 401);
            }

            $isDesigner = $user->hasRole('designer') && !$user->isSuperAdmin();

            if (!$isDesigner || $designTask->designer_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only the assigned designer can upload files.'
                ], 403);
            }

            $validated = $request->validate([
                'key' => 'required|string',
                'upload_id' => 'required|string',
                'part_numbers' => 'required|array|min:1',
                'part_numbers.*' => 'required|integer|min:1|max:10000',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in getMultipartPartUrls', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }

        try {
            $s3Config = config('filesystems.disks.s3');
            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => $s3Config['region'],
                'credentials' => [
                    'key' => $s3Config['key'],
                    'secret' => $s3Config['secret'],
                ],
                'use_path_style_endpoint' => $s3Config['use_path_style_endpoint'] ?? false,
            ]);

            $partUrls = [];
            foreach ($validated['part_numbers'] as $partNumber) {
                $cmd = $s3Client->getCommand('UploadPart', [
                    'Bucket' => $s3Config['bucket'],
                    'Key' => $validated['key'],
                    'UploadId' => $validated['upload_id'],
                    'PartNumber' => $partNumber,
                ]);

                $presigned = $s3Client->createPresignedRequest($cmd, '+15 minutes');

                $partUrls[] = [
                    'part_number' => $partNumber,
                    'url' => (string) $presigned->getUri(),
                ];
            }

            return response()->json([
                'success' => true,
                'part_urls' => $partUrls,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate multipart part URLs', [
                'error' => $e->getMessage(),
                'key' => $validated['key'] ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate part URLs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete multipart upload and save revision
     */
    public function completeMultipartUpload(Request $request, DesignTask $designTask)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.'
                ], 401);
            }

            $isDesigner = $user->hasRole('designer') && !$user->isSuperAdmin();

            if (!$isDesigner || $designTask->designer_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only the assigned designer can upload files.'
                ], 403);
            }

            $validated = $request->validate([
                'uploads' => 'required|array|min:1',
                'uploads.*.key' => 'required|string',
                'uploads.*.upload_id' => 'required|string',
                'uploads.*.parts' => 'required|array|min:1',
                'uploads.*.parts.*.part_number' => 'required|integer',
                'uploads.*.parts.*.etag' => 'required|string',
                'notes' => 'nullable|string',
                'save_as_draft' => 'nullable|boolean',
                'revision_id' => 'nullable|exists:design_revisions,id', // For update
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in completeMultipartUpload', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }

        try {
            $s3Config = config('filesystems.disks.s3');
            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => $s3Config['region'],
                'credentials' => [
                    'key' => $s3Config['key'],
                    'secret' => $s3Config['secret'],
                ],
                'use_path_style_endpoint' => $s3Config['use_path_style_endpoint'] ?? false,
            ]);

            $completedFiles = [];
            foreach ($validated['uploads'] as $upload) {
                // Complete multipart upload
                $result = $s3Client->completeMultipartUpload([
                    'Bucket' => $s3Config['bucket'],
                    'Key' => $upload['key'],
                    'UploadId' => $upload['upload_id'],
                    'MultipartUpload' => [
                        'Parts' => array_map(function ($part) {
                            return [
                                'PartNumber' => $part['part_number'],
                                'ETag' => $part['etag'],
                            ];
                        }, $upload['parts']),
                    ],
                ]);

                $completedFiles[] = $upload['key'];
            }

            // Ensure at least the first file is uploaded
            if (empty($completedFiles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'At least one file must be uploaded.'
                ], 400);
            }

            // Check if this is an update or new revision
            if (isset($validated['revision_id'])) {
                // Update existing revision
                $designRevision = DesignRevision::findOrFail($validated['revision_id']);

                if ($designRevision->designer_id !== $user->id || $designRevision->design_task_id !== $designTask->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can only update your own revisions.'
                    ], 403);
                }

                // Delete old files from S3
                $oldDesignFiles = $designRevision->design_files;
                if (is_array($oldDesignFiles)) {
                    foreach ($oldDesignFiles as $oldFile) {
                        $this->deleteFromS3($oldFile);
                    }
                } else {
                    $this->deleteFromS3($designRevision->design_file);
                }

                // Store files as JSON array (or single path if only one file)
                $designFile = count($completedFiles) === 1 ? $completedFiles[0] : json_encode($completedFiles);
                $designRevision->design_file = $designFile;
                if (isset($validated['notes'])) {
                    $designRevision->notes = $validated['notes'];
                }

                if (!($validated['save_as_draft'] ?? false)) {
                    $designRevision->status = 'submitted';
                    $designRevision->submitted_at = now();
                    $designTask->status = 'completed';
                    $designTask->design_file = $completedFiles[0];
                    $designTask->save();
                }

                $designRevision->save();

                return response()->json([
                    'success' => true,
                    'message' => $validated['save_as_draft'] ?? false
                        ? 'Draft saved successfully.'
                        : 'Revision updated successfully.',
                    'revision_id' => $designRevision->id,
                ]);
            } else {
                // Create new revision
                $latestRevision = $designTask->revisions()->latest('version')->first();
                $nextVersion = $latestRevision ? $latestRevision->version + 1 : 1;

                // Store files as JSON array (or single path if only one file)
                $designFile = count($completedFiles) === 1 ? $completedFiles[0] : json_encode($completedFiles);

                $revision = new DesignRevision();
                $revision->design_task_id = $designTask->id;
                $revision->designer_id = $user->id;
                $revision->design_file = $designFile;
                $revision->notes = $validated['notes'] ?? null;
                $revision->version = $nextVersion;
                $revision->status = $validated['save_as_draft'] ?? false ? 'draft' : 'submitted';
                $revision->submitted_at = $validated['save_as_draft'] ?? false ? null : now();
                $revision->save();

                if (!($validated['save_as_draft'] ?? false)) {
                    $designTask->status = 'completed';
                    $designTask->design_file = $completedFiles[0];
                    $designTask->save();
                }

                return response()->json([
                    'success' => true,
                    'message' => $validated['save_as_draft'] ?? false
                        ? 'Draft saved successfully.'
                        : 'Revision submitted successfully.',
                    'revision_id' => $revision->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to complete multipart upload', [
                'error' => $e->getMessage(),
                'task_id' => $designTask->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to complete upload: ' . $e->getMessage()
            ], 500);
        }
    }
}
