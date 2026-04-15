<?php

/**
 * Document Manager - Handle document uploads and management
 */

class DocumentManager
{
    const UPLOAD_DIR = __DIR__ . '/../../public/uploads/documents';
    const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];
    const ALLOWED_MIMES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain'
    ];
    const MAX_SIZE = 20 * 1024 * 1024; // 20MB

    private $fileUploader;
    private $errors = [];

    public function __construct()
    {
        $this->fileUploader = new FileUploadHandler();
    }

    /**
     * Upload document
     */
    public function upload($file, $userId = null, $metadata = [])
    {
        $this->errors = [];

        if (!$this->fileUploader->uploadDocument($file)) {
            $this->errors = $this->fileUploader->getErrors();
            return false;
        }

        $fileInfo = $this->fileUploader->getFileInfo();

        return [
            'user_id' => $userId,
            'path' => $fileInfo['path'],
            'url' => $fileInfo['url'],
            'filename' => $fileInfo['filename'],
            'original_name' => $fileInfo['original_name'],
            'size' => $fileInfo['size'],
            'mime_type' => $fileInfo['mime_type'],
            'extension' => $fileInfo['extension'],
            'uploaded_at' => $fileInfo['upload_time'],
            'metadata' => $metadata
        ];
    }

    /**
     * Delete document
     */
    public function delete($documentPath)
    {
        if (!$this->fileUploader->deleteFile($documentPath)) {
            $this->errors = $this->fileUploader->getErrors();
            return false;
        }

        return true;
    }

    /**
     * Get document display name
     */
    public static function getDisplayName($document)
    {
        return $document['original_name'] ?? $document['filename'] ?? 'Document';
    }

    /**
     * Get file type icon
     */
    public static function getFileIcon($extension)
    {
        $icons = [
            'pdf' => '📄',
            'doc' => '📝',
            'docx' => '📝',
            'xls' => '📊',
            'xlsx' => '📊',
            'ppt' => '🎬',
            'pptx' => '🎬',
            'txt' => '📋',
        ];

        return $icons[strtolower($extension)] ?? '📎';
    }

    /**
     * Get file type label
     */
    public static function getFileTypeLabel($extension)
    {
        $labels = [
            'pdf' => 'PDF Document',
            'doc' => 'Word Document',
            'docx' => 'Word Document',
            'xls' => 'Excel Spreadsheet',
            'xlsx' => 'Excel Spreadsheet',
            'ppt' => 'PowerPoint Presentation',
            'pptx' => 'PowerPoint Presentation',
            'txt' => 'Text File',
        ];

        return $labels[strtolower($extension)] ?? 'Document';
    }

    /**
     * Format file size
     */
    public static function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Get document HTML preview
     */
    public static function getDocumentHtml($document, $showActions = false)
    {
        $icon = self::getFileIcon($document['extension'] ?? 'txt');
        $name = self::getDisplayName($document);
        $size = self::formatFileSize($document['size'] ?? 0);
        $type = self::getFileTypeLabel($document['extension'] ?? 'txt');
        $url = $document['url'] ?? '';

        $html = '
            <div class="document-item">
                <div class="document-icon">' . $icon . '</div>
                <div class="document-info">
                    <a href="' . htmlspecialchars($url) . '" class="document-name" target="_blank" rel="noopener">
                        ' . htmlspecialchars($name) . '
                    </a>
                    <div class="document-meta">
                        <span class="document-type">' . $type . '</span>
                        <span class="document-size">' . $size . '</span>';

        if (isset($document['uploaded_at'])) {
            $html .= '<span class="document-date">' . htmlspecialchars($document['uploaded_at']) . '</span>';
        }

        $html .= '
                    </div>
                </div>';

        if ($showActions) {
            $html .= '
                <div class="document-actions">
                    <a href="' . htmlspecialchars($url) . '" class="btn btn-sm btn-outline" download>Download</a>';
            
            if (isset($document['id'])) {
                $html .= '<button class="btn btn-sm btn-danger" data-action="delete" data-document-id="' . htmlspecialchars($document['id']) . '">Delete</button>';
            }
            
            $html .= '
                </div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Get document row for table
     */
    public static function getDocumentTableRow($document)
    {
        $icon = self::getFileIcon($document['extension'] ?? 'txt');
        $name = self::getDisplayName($document);
        $size = self::formatFileSize($document['size'] ?? 0);
        $type = self::getFileTypeLabel($document['extension'] ?? 'txt');
        $url = $document['url'] ?? '';
        $date = $document['uploaded_at'] ?? date('Y-m-d H:i:s');

        return sprintf(
            '<tr>
                <td class="text-center">%s</td>
                <td>
                    <a href="%s" target="_blank" rel="noopener">%s</a>
                </td>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td class="text-center">
                    <a href="%s" class="btn btn-sm btn-primary" download>Download</a>
                </td>
            </tr>',
            $icon,
            htmlspecialchars($url),
            htmlspecialchars($name),
            $type,
            $size,
            htmlspecialchars($date),
            htmlspecialchars($url)
        );
    }

    /**
     * Validate document file
     */
    public static function validateDocumentFile($file)
    {
        $errors = [];

        if (!isset($file) || !is_array($file)) {
            $errors[] = 'No file provided';
            return $errors;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = self::getUploadErrorMessage($file['error']);
            return $errors;
        }

        if ($file['size'] > self::MAX_SIZE) {
            $errors[] = 'File exceeds maximum size of 20MB';
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            $errors[] = 'Invalid file type. Allowed: ' . implode(', ', self::ALLOWED_EXTENSIONS);
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, self::ALLOWED_MIMES)) {
            $errors[] = 'Invalid file MIME type: ' . $mimeType;
        }

        return $errors;
    }

    /**
     * Get upload error message
     */
    private static function getUploadErrorMessage($errorCode)
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];

        return $errors[$errorCode] ?? 'Unknown upload error';
    }

    /**
     * Check if file can be previewed
     */
    public static function canPreview($extension)
    {
        $previewable = ['pdf', 'txt'];
        return in_array(strtolower($extension), $previewable);
    }

    /**
     * Get preview URL
     */
    public static function getPreviewUrl($document)
    {
        $extension = $document['extension'] ?? '';

        if ($extension === 'pdf') {
            // Use PDF viewer
            return sprintf(
                '/viewer.html?file=%s',
                urlencode($document['url'] ?? '')
            );
        }

        return null;
    }

    /**
     * Get errors
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Has errors
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }
}
