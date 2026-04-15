<?php

/**
 * Avatar Manager - Handle user profile pictures/avatars
 */

class AvatarManager
{
    const UPLOAD_DIR = __DIR__ . '/../../public/uploads/avatars';
    const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    const MAX_SIZE = 2 * 1024 * 1024; // 2MB

    private $fileUploader;
    private $errors = [];

    public function __construct()
    {
        $this->fileUploader = new FileUploadHandler();
    }

    /**
     * Upload avatar
     */
    public function upload($file, $userId)
    {
        $this->errors = [];

        if (!$this->fileUploader->uploadAvatar($file, $userId)) {
            $this->errors = $this->fileUploader->getErrors();
            return false;
        }

        $fileInfo = $this->fileUploader->getFileInfo();

        // Create avatar record info
        return [
            'user_id' => $userId,
            'path' => $fileInfo['path'],
            'url' => $fileInfo['url'],
            'filename' => $fileInfo['filename'],
            'size' => $fileInfo['size'],
            'mime_type' => $fileInfo['mime_type'],
            'uploaded_at' => $fileInfo['upload_time']
        ];
    }

    /**
     * Delete avatar
     */
    public function delete($avatarPath)
    {
        if (!$this->fileUploader->deleteFile($avatarPath)) {
            $this->errors = $this->fileUploader->getErrors();
            return false;
        }

        return true;
    }

    /**
     * Replace avatar (delete old, upload new)
     */
    public function replace($oldAvatarPath, $newFile, $userId)
    {
        // Delete old avatar
        if ($oldAvatarPath) {
            $this->delete($oldAvatarPath);
        }

        // Upload new avatar
        return $this->upload($newFile, $userId);
    }

    /**
     * Get avatar URL with fallback
     */
    public static function getAvatarUrl($user)
    {
        if (isset($user['avatar_url']) && !empty($user['avatar_url'])) {
            return $user['avatar_url'];
        }

        // Generate initials avatar fallback
        return self::getInitialsAvatar($user);
    }

    /**
     * Get initials avatar (fallback)
     */
    public static function getInitialsAvatar($user)
    {
        $initials = '';

        if (isset($user['first_name']) && isset($user['last_name'])) {
            $initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
        } elseif (isset($user['name'])) {
            $parts = explode(' ', $user['name']);
            $initials = strtoupper(substr($parts[0], 0, 1));
            if (isset($parts[1])) {
                $initials .= strtoupper(substr($parts[1], 0, 1));
            }
        }

        // Generate color based on initials
        $color = self::getColorForInitials($initials);

        // Return SVG initial avatar
        return self::generateInitialsSVG($initials, $color);
    }

    /**
     * Get color for initials
     */
    private static function getColorForInitials($initials)
    {
        $colors = [
            '#4A90E2', // Blue
            '#7C3AED', // Purple
            '#EC4899', // Pink
            '#F59E0B', // Amber
            '#10B981', // Green
            '#06B6D4', // Cyan
            '#EF4444', // Red
            '#8B5CF6', // Violet
        ];

        $index = (ord($initials[0] ?? 'A') + (ord($initials[1] ?? 'A'))) % count($colors);
        return $colors[$index];
    }

    /**
     * Generate initials SVG
     */
    private static function generateInitialsSVG($initials, $color)
    {
        $svg = sprintf(
            'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" fill="%s"/><text x="50" y="65" text-anchor="middle" font-size="40" font-weight="bold" fill="white">%s</text></svg>',
            urlencode($color),
            urlencode($initials)
        );

        return $svg;
    }

    /**
     * Get avatar HTML
     */
    public static function getAvatarHtml($user, $size = 64, $class = '')
    {
        $url = self::getAvatarUrl($user);
        $name = $user['name'] ?? $user['first_name'] . ' ' . $user['last_name'] ?? 'User';

        return sprintf(
            '<img src="%s" alt="%s" class="avatar avatar-%d %s" width="%d" height="%d">',
            htmlspecialchars($url),
            htmlspecialchars($name),
            $size,
            htmlspecialchars($class),
            $size,
            $size
        );
    }

    /**
     * Validate avatar file
     */
    public static function validateAvatarFile($file)
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
            $errors[] = 'File exceeds maximum size of 2MB';
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            $errors[] = 'Invalid file type. Allowed: ' . implode(', ', self::ALLOWED_EXTENSIONS);
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
