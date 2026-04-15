<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Storage Helper Functions
 *
 * Global helpers for storage operations.
 */

if (!function_exists('storage')) {
    /**
     * Get storage manager instance
     */
    function storage(): StorageManager
    {
        static $manager;
        
        if ($manager === null) {
            $manager = new StorageManager();
        }
        
        return $manager;
    }
}

if (!function_exists('log_message')) {
    /**
     * Log a message
     */
    function log_message(string $message, string $level = 'info', array $context = []): void
    {
        $logger = storage()->logger();
        
        $method = "log{$level}";
        if (method_exists($logger, $method)) {
            $logger->$method($message, $context);
        } else {
            $logger->info($message, array_merge(['level' => $level], $context));
        }
    }
}

if (!function_exists('cache_get')) {
    /**
     * Get from cache
     */
    function cache_get(string $key, mixed $default = null): mixed
    {
        return storage()->cache()->get($key, $default);
    }
}

if (!function_exists('cache_put')) {
    /**
     * Put value in cache
     */
    function cache_put(string $key, mixed $value, int $ttl = 3600): void
    {
        storage()->cache()->put($key, $value, $ttl);
    }
}

if (!function_exists('cache_forget')) {
    /**
     * Forget value from cache
     */
    function cache_forget(string $key): void
    {
        storage()->cache()->forget($key);
    }
}

if (!function_exists('cache_flush')) {
    /**
     * Flush all cache
     */
    function cache_flush(): int
    {
        return storage()->cache()->flush();
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get storage path
     */
    function storage_path(string $type = ''): string
    {
        return storage()->getPath($type);
    }
}

if (!function_exists('storage_size')) {
    /**
     * Get storage size formatted
     */
    function storage_size(string $type = ''): string
    {
        return storage()->getFormattedSize($type);
    }
}

if (!function_exists('storage_stats')) {
    /**
     * Get storage statistics
     */
    function storage_stats(): array
    {
        return storage()->getStats();
    }
}
