<?php

/**
 * Storage Configuration
 *
 * Configure logging, caching, sessions, and storage behavior.
 */

return [
    // Logger configuration
    'logger' => [
        'path' => dirname(__DIR__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs',
        'level' => $_ENV['LOG_LEVEL'] ?? 'info', // debug, info, warning, error
        'max_file_size' => 10485760, // 10MB
        'date_format' => 'Y-m-d H:i:s',
        'timezone' => 'UTC',
    ],

    // Cache configuration
    'cache' => [
        'path' => dirname(__DIR__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache',
        'default_ttl' => 3600, // 1 hour
        'cleanup_probability' => 0.1, // 10% chance to cleanup on access
    ],

    // Session configuration
    'session' => [
        'name' => $_ENV['SESSION_NAME'] ?? 'IMS_SESSION',
        'lifetime' => 86400, // 24 hours
        'path' => '/',
        'domain' => $_ENV['SESSION_DOMAIN'] ?? '',
        'secure' => $_ENV['SESSION_SECURE'] ?? false,
        'httponly' => true,
        'samesite' => 'Lax',
        'save_path' => dirname(__DIR__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions',
    ],

    // Storage paths
    'paths' => [
        'logs' => dirname(__DIR__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs',
        'cache' => dirname(__DIR__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache',
        'sessions' => dirname(__DIR__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions',
        'temp' => dirname(__DIR__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'temp',
        'backups' => dirname(__DIR__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backups',
        'exports' => dirname(__DIR__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'exports',
    ],

    // Cleanup policies (days)
    'cleanup' => [
        'logs' => 30,        // Keep logs for 30 days
        'cache' => 7,        // Keep cache for 7 days
        'temp' => 1,         // Keep temp files for 1 day
        'backups' => 30,     // Keep backups for 30 days
        'exports' => 0,      // Keep exports indefinitely (manual cleanup)
        'sessions' => 1,     // PHP handles session cleanup
    ],

    // Permissions (octal)
    'permissions' => [
        'directory' => 0755,
        'file' => 0644,
    ],

    // File limits
    'limits' => [
        'max_log_size' => 10485760,     // 10MB per log file
        'max_cache_size' => 0,          // 0 = unlimited
        'max_temp_size' => 0,           // 0 = unlimited
        'max_backup_size' => 0,         // 0 = unlimited
    ],

    // Monitoring
    'monitoring' => [
        'enabled' => true,
        'track_disk_usage' => true,
        'alert_threshold' => 90,        // Alert when storage >90% full
        'cleanup_threshold' => 80,      // Auto-cleanup when >80% full
    ],
];
