/**
 * Multipart Upload Handler for Design Files
 * Direct upload from client to S3 using presigned URLs
 */

class DesignMultipartUpload {
    constructor(taskId, routePrefix) {
        this.taskId = taskId;
        this.routePrefix = routePrefix;
        this.baseUrl = `/${routePrefix}/design-tasks/${taskId}/revisions`;
        this.chunkSize = 5 * 1024 * 1024; // 5MB chunks
    }

    /**
     * Initialize multipart upload for files
     */
    async initUpload(files) {
        const fileData = Array.from(files).map(file => ({
            name: file.name,
            size: file.size,
            type: file.type,
        }));

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                         document.querySelector('input[name="_token"]')?.value || '';
        
        if (!csrfToken) {
            console.error('CSRF token not found');
            throw new Error('CSRF token not found. Please refresh the page.');
        }

        const response = await fetch(`${this.baseUrl}/init-multipart`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ files: fileData }),
        });

        const contentType = response.headers.get('content-type');
        let errorData;
        
        if (contentType && contentType.includes('application/json')) {
            errorData = await response.json();
        } else {
            const text = await response.text();
            console.error('Non-JSON response:', text.substring(0, 200));
            throw new Error('Server returned an invalid response. Please check the console for details.');
        }

        if (!response.ok) {
            throw new Error(errorData.message || errorData.errors || 'Failed to initialize upload');
        }

        return errorData;
    }

    /**
     * Get presigned URLs for multipart parts
     */
    async getPartUrls(key, uploadId, partNumbers) {
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                         document.querySelector('input[name="_token"]')?.value || '';
        
        if (!csrfToken) {
            console.error('CSRF token not found');
            throw new Error('CSRF token not found. Please refresh the page.');
        }

        const response = await fetch(`${this.baseUrl}/multipart-part-urls`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                key,
                upload_id: uploadId,
                part_numbers: partNumbers,
            }),
        });

        const contentType = response.headers.get('content-type');
        let responseData;
        
        if (contentType && contentType.includes('application/json')) {
            responseData = await response.json();
        } else {
            const text = await response.text();
            console.error('Non-JSON response:', text.substring(0, 200));
            throw new Error('Server returned an invalid response. Please check the console for details.');
        }

        if (!response.ok) {
            throw new Error(responseData.message || responseData.errors || 'Failed to get part URLs');
        }

        return responseData;
    }

    /**
     * Upload a single file using multipart upload
     */
    async uploadFile(file, key, uploadId, onProgress) {
        const totalParts = Math.ceil(file.size / this.chunkSize);
        const parts = [];

        for (let partNumber = 1; partNumber <= totalParts; partNumber++) {
            const start = (partNumber - 1) * this.chunkSize;
            const end = Math.min(start + this.chunkSize, file.size);
            const chunk = file.slice(start, end);

            // Get presigned URL for this part
            const partUrlsResponse = await this.getPartUrls(key, uploadId, [partNumber]);
            const partUrl = partUrlsResponse.part_urls[0].url;

            // Upload chunk to S3
            const uploadResponse = await fetch(partUrl, {
                method: 'PUT',
                body: chunk,
                headers: {
                    'Content-Type': file.type,
                },
            });

            if (!uploadResponse.ok) {
                throw new Error(`Failed to upload part ${partNumber}`);
            }

            const etag = uploadResponse.headers.get('ETag')?.replace(/"/g, '');
            parts.push({
                part_number: partNumber,
                etag: etag,
            });

            // Update progress
            if (onProgress) {
                const progress = (partNumber / totalParts) * 100;
                onProgress(progress);
            }
        }

        return parts;
    }

    /**
     * Upload multiple files
     */
    async uploadFiles(files, onProgress, onFileProgress) {
        try {
            // Initialize multipart uploads
            const initResponse = await this.initUpload(files);
            if (!initResponse.success) {
                throw new Error(initResponse.message || 'Failed to initialize upload');
            }

            const uploads = [];
            const fileArray = Array.from(files);

            // Upload each file
            for (let i = 0; i < fileArray.length; i++) {
                const file = fileArray[i];
                const upload = initResponse.uploads[i];

                if (onFileProgress) {
                    onFileProgress(i, file.name, 0);
                }

                const parts = await this.uploadFile(
                    file,
                    upload.key,
                    upload.upload_id,
                    (progress) => {
                        if (onFileProgress) {
                            onFileProgress(i, file.name, progress);
                        }
                        if (onProgress) {
                            const totalProgress = ((i + progress / 100) / fileArray.length) * 100;
                            onProgress(totalProgress);
                        }
                    }
                );

                uploads.push({
                    key: upload.key,
                    upload_id: upload.upload_id,
                    parts: parts,
                });
            }

            return uploads;
        } catch (error) {
            console.error('Upload error:', error);
            throw error;
        }
    }

    /**
     * Complete multipart upload and save revision
     */
    async completeUpload(uploads, notes = null, saveAsDraft = false, revisionId = null) {
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                         document.querySelector('input[name="_token"]')?.value || '';
        
        if (!csrfToken) {
            console.error('CSRF token not found');
            throw new Error('CSRF token not found. Please refresh the page.');
        }

        const response = await fetch(`${this.baseUrl}/complete-multipart`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                uploads: uploads,
                notes: notes,
                save_as_draft: saveAsDraft,
                revision_id: revisionId,
            }),
        });

        const contentType = response.headers.get('content-type');
        let responseData;
        
        if (contentType && contentType.includes('application/json')) {
            responseData = await response.json();
        } else {
            const text = await response.text();
            console.error('Non-JSON response:', text.substring(0, 200));
            throw new Error('Server returned an invalid response. Please check the console for details.');
        }

        if (!response.ok) {
            throw new Error(responseData.message || responseData.errors || 'Failed to complete upload');
        }

        return responseData;
    }

    /**
     * Full upload process: init -> upload -> complete
     */
    async upload(files, notes = null, saveAsDraft = false, revisionId = null, onProgress = null, onFileProgress = null) {
        try {
            // Step 1: Upload files to S3
            const uploads = await this.uploadFiles(files, onProgress, onFileProgress);

            // Step 2: Complete multipart upload and save revision
            const result = await this.completeUpload(uploads, notes, saveAsDraft, revisionId);

            return result;
        } catch (error) {
            console.error('Full upload process error:', error);
            throw error;
        }
    }
}

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DesignMultipartUpload;
}

