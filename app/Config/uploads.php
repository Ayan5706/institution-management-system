<?php

/**
 * Upload Configuration
 * Central configuration for file uploads
 */

return [
    'upload_dir' => __DIR__ . '/../uploads',
    
    'max_file_sizes' => [
        'image' => 5 * 1024 * 1024,      // 5MB
        'document' => 20 * 1024 * 1024,  // 20MB
        'avatar' => 2 * 1024 * 1024,     // 2MB
        'video' => 100 * 1024 * 1024,    // 100MB
    ],

    'allowed_extensions' => [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'],
        'avatar' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'video' => ['mp4', 'webm', 'ogg'],
    ],

    'allowed_mimes' => [
        'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        'document' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain'
        ],
        'avatar' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        'video' => ['video/mp4', 'video/webm', 'video/ogg'],
    ],

    'directories' => [
        'products_images' => 'products/images',
        'products_documents' => 'products/documents',
        'products_thumbnails' => 'products/thumbnails',
        'avatars' => 'avatars',
        'documents' => 'documents',
        'temp' => 'temp',
    ],

    'security' => [
        'scan_for_malware' => false, // Set to true in production with ClamAV
        'disable_script_execution' => true,
        'sandbox_uploads' => true,
    ],

    'cleanup' => [
        'temp_dir_retention' => 3600, // 1 hour
        'orphaned_files_retention' => 2592000, // 30 days
        'auto_cleanup' => true,
    ],

    'thumbnails' => [
        'generate' => true,
        'width' => 200,
        'height' => 200,
        'quality' => 85,
    ],

    'storage' => [
        'disk_quota' => 10 * 1024 * 1024 * 1024, // 10GB
        'per_user_quota' => 500 * 1024 * 1024, // 500MB per user
    ],

    'logging' => [
        'log_uploads' => true,
        'log_downloads' => true,
        'log_file' => __DIR__ . '/../../storage/logs/uploads.log',
    ],
];
