/**
 * Upload Manager - Client-side upload handling with drag & drop
 */

class UploadManager {
    constructor(config = {}) {
        this.config = {
            maxSize: 20 * 1024 * 1024, // 20MB default
            allowedTypes: [],
            uploadUrl: '/api/upload',
            autoRetry: true,
            maxRetries: 3,
            chunkSize: 1 * 1024 * 1024, // 1MB chunks for large files
            ...config
        };

        this.uploads = new Map();
        this.queue = [];
        this.isProcessing = false;
    }

    /**
     * Initialize upload manager
     */
    init() {
        this.setupDragDrop();
        this.setupFileInputs();
    }

    /**
     * Setup drag and drop
     */
    setupDragDrop() {
        const dropZones = document.querySelectorAll('[data-drop-zone]');

        dropZones.forEach(zone => {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(event => {
                zone.addEventListener(event, this.preventDefaults.bind(this), false);
            });

            ['dragenter', 'dragover'].forEach(event => {
                zone.addEventListener(event, () => zone.classList.add('drag-active'), false);
            });

            ['dragleave', 'drop'].forEach(event => {
                zone.addEventListener(event, () => zone.classList.remove('drag-active'), false);
            });

            zone.addEventListener('drop', (e) => {
                const files = e.dataTransfer.files;
                this.handleFiles(files, zone);
            }, false);
        });
    }

    /**
     * Setup file inputs
     */
    setupFileInputs() {
        const inputs = document.querySelectorAll('input[type="file"][data-upload-manager]');

        inputs.forEach(input => {
            input.addEventListener('change', (e) => {
                this.handleFiles(e.target.files, input);
            });
        });
    }

    /**
     * Prevent default drag events
     */
    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    /**
     * Handle file selection
     */
    handleFiles(files, target) {
        Array.from(files).forEach(file => {
            this.addToQueue(file, target);
        });

        this.processQueue();
    }

    /**
     * Add file to upload queue
     */
    addToQueue(file, target) {
        const uploadId = this.generateUploadId();

        const upload = {
            id: uploadId,
            file: file,
            target: target,
            progress: 0,
            status: 'queued',
            retries: 0
        };

        this.uploads.set(uploadId, upload);
        this.queue.push(uploadId);

        // Emit event
        this.emit('file:added', { uploadId, file });
    }

    /**
     * Process upload queue
     */
    async processQueue() {
        if (this.isProcessing || this.queue.length === 0) return;

        this.isProcessing = true;

        while (this.queue.length > 0) {
            const uploadId = this.queue.shift();
            const upload = this.uploads.get(uploadId);

            if (!upload) continue;

            // Validate file
            const validationErrors = this.validateFile(upload.file);
            if (validationErrors.length > 0) {
                upload.status = 'failed';
                upload.error = validationErrors;
                this.emit('file:error', { uploadId, errors: validationErrors });
                continue;
            }

            // Upload file
            await this.uploadFile(uploadId);
        }

        this.isProcessing = false;
        this.emit('queue:complete');
    }

    /**
     * Upload single file
     */
    async uploadFile(uploadId, retry = 0) {
        const upload = this.uploads.get(uploadId);
        if (!upload) return;

        upload.status = 'uploading';
        upload.retries = retry;

        const formData = new FormData();
        formData.append('file', upload.file);
        formData.append('upload_id', uploadId);

        // Add CSRF token
        const csrfToken = document.querySelector('[name="_token"]')?.value;
        if (csrfToken) {
            formData.append('_token', csrfToken);
        }

        // Add custom data from target element
        if (upload.target.dataset.uploadData) {
            try {
                const customData = JSON.parse(upload.target.dataset.uploadData);
                Object.keys(customData).forEach(key => {
                    formData.append(key, customData[key]);
                });
            } catch (e) {
                console.error('Invalid upload data JSON:', e);
            }
        }

        try {
            const xhr = new XMLHttpRequest();

            // Progress tracking
            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const progress = (e.loaded / e.total) * 100;
                    upload.progress = progress;
                    this.emit('file:progress', { uploadId, progress });
                }
            });

            // Upload complete
            xhr.addEventListener('load', () => {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        upload.status = 'success';
                        upload.response = response;
                        this.emit('file:success', { uploadId, response });
                    } catch (e) {
                        upload.status = 'failed';
                        upload.error = 'Invalid server response';
                        this.emit('file:error', { uploadId, error: 'Invalid server response' });
                    }
                } else {
                    this.handleUploadError(uploadId, xhr, retry);
                }
            });

            // Upload error
            xhr.addEventListener('error', () => {
                this.handleUploadError(uploadId, null, retry);
            });

            // Upload abort
            xhr.addEventListener('abort', () => {
                upload.status = 'cancelled';
                this.emit('file:cancelled', { uploadId });
            });

            // Start upload
            xhr.open('POST', this.config.uploadUrl);
            xhr.send(formData);

            // Store XHR for cancellation
            upload.xhr = xhr;

        } catch (error) {
            console.error('Upload error:', error);
            this.handleUploadError(uploadId, null, retry);
        }
    }

    /**
     * Handle upload error with retry
     */
    handleUploadError(uploadId, xhr, retry) {
        const upload = this.uploads.get(uploadId);
        if (!upload) return;

        const shouldRetry = this.config.autoRetry && retry < this.config.maxRetries;

        if (shouldRetry) {
            // Retry after delay
            setTimeout(() => {
                this.uploadFile(uploadId, retry + 1);
            }, Math.pow(2, retry) * 1000); // Exponential backoff

            this.emit('file:retrying', { uploadId, attempt: retry + 1 });
        } else {
            upload.status = 'failed';
            upload.error = xhr?.statusText || 'Upload failed';
            this.emit('file:error', { uploadId, error: upload.error });
        }
    }

    /**
     * Validate file
     */
    validateFile(file) {
        const errors = [];

        if (file.size > this.config.maxSize) {
            errors.push(`File exceeds maximum size of ${this.formatBytes(this.config.maxSize)}`);
        }

        if (this.config.allowedTypes.length > 0) {
            const isAllowed = this.config.allowedTypes.some(type => {
                if (type.includes('/')) {
                    return file.type === type || file.type.match(new RegExp(type));
                }
                return file.name.endsWith('.' + type);
            });

            if (!isAllowed) {
                errors.push(`File type not allowed. Allowed: ${this.config.allowedTypes.join(', ')}`);
            }
        }

        return errors;
    }

    /**
     * Cancel upload
     */
    cancelUpload(uploadId) {
        const upload = this.uploads.get(uploadId);
        if (upload && upload.xhr) {
            upload.xhr.abort();
        }
    }

    /**
     * Cancel all uploads
     */
    cancelAll() {
        this.uploads.forEach((upload, uploadId) => {
            this.cancelUpload(uploadId);
        });
    }

    /**
     * Get upload status
     */
    getUpload(uploadId) {
        return this.uploads.get(uploadId);
    }

    /**
     * Get all uploads
     */
    getAllUploads() {
        return Array.from(this.uploads.values());
    }

    /**
     * Clear completed uploads
     */
    clearCompleted() {
        for (const [uploadId, upload] of this.uploads.entries()) {
            if (upload.status === 'success' || upload.status === 'failed') {
                this.uploads.delete(uploadId);
            }
        }
    }

    /**
     * Format bytes
     */
    formatBytes(bytes) {
        const units = ['B', 'KB', 'MB', 'GB'];
        let size = bytes;
        let unitIndex = 0;

        while (size >= 1024 && unitIndex < units.length - 1) {
            size /= 1024;
            unitIndex++;
        }

        return Math.round(size * 100) / 100 + ' ' + units[unitIndex];
    }

    /**
     * Generate unique upload ID
     */
    generateUploadId() {
        return 'upload_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    /**
     * Emit custom events
     */
    emit(eventName, detail) {
        const event = new CustomEvent('upload:' + eventName, { detail });
        document.dispatchEvent(event);
    }

    /**
     * Listen to events
     */
    on(eventName, callback) {
        document.addEventListener('upload:' + eventName, callback);
    }

    /**
     * Remove event listener
     */
    off(eventName, callback) {
        document.removeEventListener('upload:' + eventName, callback);
    }
}

// Auto-initialize
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('[data-drop-zone]') || document.querySelector('input[data-upload-manager]')) {
        window.uploadManager = new UploadManager();
        window.uploadManager.init();
    }
});
