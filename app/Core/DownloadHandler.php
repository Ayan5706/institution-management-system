<?php

/**
 * Download Handler - Serve downloaded files safely
 */

class DownloadHandler
{
    const ALLOWED_PATHS = [
        'uploads/products/documents',
        'uploads/products/images',
        'uploads/documents',
        'uploads/avatars'
    ];

    private $errors = [];

    /**
     * Download file
     */
    public function download($filePath, $customName = null)
    {
        // Security: Validate file path
        if (!$this->isValidPath($filePath)) {
            $this->errors[] = 'Invalid file path';
            return false;
        }

        $fullPath = __DIR__ . '/../../public/' . $filePath;

        // Security: Prevent directory traversal
        $realPath = realpath($fullPath);
        if ($realPath === false || strpos($realPath, realpath(__DIR__ . '/../../public/uploads')) !== 0) {
            $this->errors[] = 'File not found or access denied';
            return false;
        }

        // Check if file exists
        if (!file_exists($realPath) || !is_file($realPath)) {
            $this->errors[] = 'File not found';
            return false;
        }

        // Check if readable
        if (!is_readable($realPath)) {
            $this->errors[] = 'File not readable';
            return false;
        }

        // Get file info
        $fileName = $customName ?? basename($realPath);
        $fileSize = filesize($realPath);
        $fileMime = $this->getMimeType($realPath);

        // Set headers
        header('Content-Type: ' . $fileMime);
        header('Content-Length: ' . $fileSize);
        header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Log download if enabled
        $this->logDownload($filePath);

        // Stream file
        readfile($realPath);

        return true;
    }

    /**
     * Stream file for viewing
     */
    public function stream($filePath)
    {
        // Similar to download but with different headers
        if (!$this->isValidPath($filePath)) {
            $this->errors[] = 'Invalid file path';
            return false;
        }

        $fullPath = __DIR__ . '/../../public/' . $filePath;
        $realPath = realpath($fullPath);

        if ($realPath === false || !file_exists($realPath)) {
            $this->errors[] = 'File not found';
            return false;
        }

        $fileSize = filesize($realPath);
        $fileMime = $this->getMimeType($realPath);

        // For streaming, allow inline display for supported types
        $inlineTypes = ['text/plain', 'application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $disposition = in_array($fileMime, $inlineTypes) ? 'inline' : 'attachment';

        header('Content-Type: ' . $fileMime);
        header('Content-Length: ' . $fileSize);
        header('Content-Disposition: ' . $disposition . '; filename="' . basename($filePath) . '"');
        header('Accept-Ranges: bytes');

        // Handle range requests for video/large files
        if (isset($_SERVER['HTTP_RANGE'])) {
            $this->handleRangeRequest($realPath, $fileSize);
        } else {
            readfile($realPath);
        }

        return true;
    }

    /**
     * Handle HTTP range requests
     */
    private function handleRangeRequest($filePath, $fileSize)
    {
        $range = $_SERVER['HTTP_RANGE'];

        if (preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
            $start = intval($matches[1]);
            $end = $matches[2] !== '' ? intval($matches[2]) : $fileSize - 1;

            if ($start > 0 || $end < $fileSize - 1) {
                header('HTTP/1.1 206 Partial Content');
                header('Content-Range: bytes ' . $start . '-' . $end . '/' . $fileSize);
                header('Content-Length: ' . ($end - $start + 1));

                $fp = fopen($filePath, 'rb');
                fseek($fp, $start);
                echo fread($fp, $end - $start + 1);
                fclose($fp);

                return;
            }
        }

        readfile($filePath);
    }

    /**
     * Validate file path
     */
    private function isValidPath($filePath)
    {
        // Check if path contains allowed directories
        foreach (self::ALLOWED_PATHS as $allowedPath) {
            if (strpos($filePath, $allowedPath) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get MIME type
     */
    private function getMimeType($filePath)
    {
        // Try finfo first
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $filePath);
            finfo_close($finfo);

            if ($mime !== false) {
                return $mime;
            }
        }

        // Fallback to extension-based detection
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'txt' => 'text/plain',
        ];

        return $mimes[$extension] ?? 'application/octet-stream';
    }

    /**
     * Log download
     */
    private function logDownload($filePath)
    {
        $config = include(__DIR__ . '/uploads.php');

        if ($config['logging']['log_downloads'] && isset($config['logging']['log_file'])) {
            $logFile = $config['logging']['log_file'];
            $logDir = dirname($logFile);

            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }

            $entry = sprintf(
                "[%s] Downloaded: %s by %s (IP: %s)\n",
                date('Y-m-d H:i:s'),
                $filePath,
                $_SESSION['user_id'] ?? 'anonymous',
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            );

            file_put_contents($logFile, $entry, FILE_APPEND);
        }
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
}
