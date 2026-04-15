<?php

/**
 * File Upload Handler
 * Manages file uploads with validation and storage
 */

class FileUploadHandler
{
    const UPLOAD_DIR = __DIR__ . '/uploads';

    const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    const ALLOWED_DOCUMENT_TYPES = ['application/pdf', 'application/msword', 
                                     'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                     'application/vnd.ms-excel',
                                     'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
    const ALLOWED_DOCUMENT_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];

    const MAX_IMAGE_SIZE = 5 * 1024 * 1024; // 5MB
    const MAX_DOCUMENT_SIZE = 20 * 1024 * 1024; // 20MB
    const MAX_AVATAR_SIZE = 2 * 1024 * 1024; // 2MB

    private $uploadDir;
    private $errors = [];
    private $fileInfo = [];

    public function __construct()
    {
        $this->uploadDir = self::UPLOAD_DIR;
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Handle product image upload
     */
    public function uploadProductImage($file, $productId = null)
    {
        return $this->validateAndUpload(
            $file,
            'products/images',
            self::ALLOWED_IMAGE_TYPES,
            self::ALLOWED_IMAGE_EXTENSIONS,
            self::MAX_IMAGE_SIZE,
            $productId
        );
    }

    /**
     * Handle product document upload
     */
    public function uploadProductDocument($file, $productId = null)
    {
        return $this->validateAndUpload(
            $file,
            'products/documents',
            self::ALLOWED_DOCUMENT_TYPES,
            self::ALLOWED_DOCUMENT_EXTENSIONS,
            self::MAX_DOCUMENT_SIZE,
            $productId
        );
    }

    /**
     * Handle user avatar upload
     */
    public function uploadAvatar($file, $userId = null)
    {
        return $this->validateAndUpload(
            $file,
            'avatars',
            self::ALLOWED_IMAGE_TYPES,
            self::ALLOWED_IMAGE_EXTENSIONS,
            self::MAX_AVATAR_SIZE,
            $userId
        );
    }

    /**
     * Handle general document upload
     */
    public function uploadDocument($file)
    {
        return $this->validateAndUpload(
            $file,
            'documents',
            self::ALLOWED_DOCUMENT_TYPES,
            self::ALLOWED_DOCUMENT_EXTENSIONS,
            self::MAX_DOCUMENT_SIZE
        );
    }

    /**
     * Validate and upload file
     */
    private function validateAndUpload($file, $subdirectory, $allowedTypes, $allowedExtensions, $maxSize, $entityId = null)
    {
        $this->errors = [];
        $this->fileInfo = [];

        // Check if file exists
        if (!isset($file) || !is_array($file)) {
            $this->errors[] = 'No file provided';
            return false;
        }

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadErrorMessage($file['error']);
            return false;
        }

        // Validate file size
        if ($file['size'] > $maxSize) {
            $this->errors[] = "File size exceeds limit of " . $this->formatBytes($maxSize);
            return false;
        }

        // Validate file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            $this->errors[] = "File type not allowed. Allowed types: " . implode(', ', $allowedExtensions);
            return false;
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            $this->errors[] = "Invalid file type. MIME type not allowed: " . $mimeType;
            return false;
        }

        // Create directory if not exists
        $uploadPath = $this->uploadDir . '/' . $subdirectory;
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Generate unique filename
        $filename = $this->generateFilename($file['name'], $entityId);
        $filepath = $uploadPath . '/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            $this->errors[] = "Failed to move uploaded file";
            return false;
        }

        // Set permissions
        chmod($filepath, 0644);

        // Store file info
        $this->fileInfo = [
            'filename' => $filename,
            'original_name' => $file['name'],
            'path' => $subdirectory . '/' . $filename,
            'url' => '/uploads/' . $subdirectory . '/' . $filename,
            'size' => $file['size'],
            'mime_type' => $mimeType,
            'extension' => $extension,
            'upload_time' => date('Y-m-d H:i:s')
        ];

        // Create thumbnail for images
        if (strpos($mimeType, 'image') === 0 && $subdirectory === 'products/images') {
            $this->createThumbnail($filepath, $uploadPath . '/../thumbnails/' . $filename);
        }

        return true;
    }

    /**
     * Generate unique filename
     */
    private function generateFilename($originalName, $entityId = null)
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        
        // Remove special characters from basename
        $basename = preg_replace('/[^a-z0-9]/i', '_', $basename);
        $basename = preg_replace('/_+/', '_', $basename);
        $basename = trim($basename, '_');

        // Add entity ID if provided
        if ($entityId) {
            $filename = $entityId . '_' . $basename . '_' . time() . '.' . $extension;
        } else {
            $filename = $basename . '_' . time() . '.' . $extension;
        }

        return $filename;
    }

    /**
     * Create image thumbnail
     */
    private function createThumbnail($sourcePath, $thumbnailPath, $maxWidth = 200, $maxHeight = 200)
    {
        if (!extension_loaded('gd')) {
            return false;
        }

        try {
            $imageInfo = getimagesize($sourcePath);
            if (!$imageInfo) {
                return false;
            }

            $mimeType = $imageInfo['mime'];
            
            switch ($mimeType) {
                case 'image/jpeg':
                    $sourceImage = imagecreatefromjpeg($sourcePath);
                    break;
                case 'image/png':
                    $sourceImage = imagecreatefrompng($sourcePath);
                    break;
                case 'image/gif':
                    $sourceImage = imagecreatefromgif($sourcePath);
                    break;
                case 'image/webp':
                    $sourceImage = imagecreatefromwebp($sourcePath);
                    break;
                default:
                    return false;
            }

            if (!$sourceImage) {
                return false;
            }

            $sourceWidth = imagesx($sourceImage);
            $sourceHeight = imagesy($sourceImage);

            // Calculate thumbnail dimensions
            $ratio = min($maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
            $thumbWidth = intval($sourceWidth * $ratio);
            $thumbHeight = intval($sourceHeight * $ratio);

            // Create thumbnail
            $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);
            imagecopyresampled($thumbnail, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $sourceWidth, $sourceHeight);

            // Save thumbnail
            switch ($mimeType) {
                case 'image/jpeg':
                    imagejpeg($thumbnail, $thumbnailPath, 85);
                    break;
                case 'image/png':
                    imagepng($thumbnail, $thumbnailPath, 9);
                    break;
                case 'image/gif':
                    imagegif($thumbnail, $thumbnailPath);
                    break;
                case 'image/webp':
                    imagewebp($thumbnail, $thumbnailPath, 85);
                    break;
            }

            imagedestroy($sourceImage);
            imagedestroy($thumbnail);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Delete uploaded file
     */
    public function deleteFile($filepath)
    {
        $fullPath = $this->uploadDir . '/' . $filepath;

        if (!file_exists($fullPath)) {
            $this->errors[] = "File not found: " . $filepath;
            return false;
        }

        if (!unlink($fullPath)) {
            $this->errors[] = "Failed to delete file: " . $filepath;
            return false;
        }

        // Delete associated thumbnail
        if (strpos($filepath, 'products/images') !== false) {
            $thumbnailPath = str_replace('products/images', 'products/thumbnails', $fullPath);
            if (file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }
        }

        return true;
    }

    /**
     * Get upload error message
     */
    private function getUploadErrorMessage($errorCode)
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];

        return $errors[$errorCode] ?? 'Unknown upload error';
    }

    /**
     * Format bytes to readable format
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Get file info
     */
    public function getFileInfo()
    {
        return $this->fileInfo;
    }

    /**
     * Get errors
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get last error
     */
    public function getLastError()
    {
        return end($this->errors);
    }

    /**
     * Has errors
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * Clear errors
     */
    public function clearErrors()
    {
        $this->errors = [];
    }

    /**
     * Get file size in human readable format
     */
    public static function getFileSizeFormatted($filepath)
    {
        if (!file_exists($filepath)) {
            return null;
        }

        $bytes = filesize($filepath);
        return self::formatBytesStatic($bytes);
    }

    /**
     * Format bytes (static version)
     */
    private static function formatBytesStatic($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
