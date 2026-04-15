<?php

declare(strict_types=1);

namespace App\Core;

class Cache
{
    private string $cachePath;
    private int $defaultTTL = 3600; // 1 hour

    public function __construct(string $storagePath = '')
    {
        $this->cachePath = $storagePath ?: dirname(__DIR__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache';
    }

    /**
     * Get a cached value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $file = $this->getFilePath($key);

        if (!is_file($file)) {
            return $default;
        }

        $data = unserialize(file_get_contents($file));

        if ($data === false || (isset($data['expires']) && $data['expires'] < time())) {
            unlink($file);
            return $default;
        }

        return $data['value'] ?? $default;
    }

    /**
     * Set a cached value
     */
    public function put(string $key, mixed $value, int $ttl = 0): void
    {
        $ttl = $ttl ?: $this->defaultTTL;
        $file = $this->getFilePath($key);

        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        file_put_contents($file, serialize($data), LOCK_EX);
    }

    /**
     * Check if key exists and is not expired
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Delete a cached value
     */
    public function forget(string $key): void
    {
        $file = $this->getFilePath($key);

        if (is_file($file)) {
            unlink($file);
        }
    }

    /**
     * Clear all cache
     */
    public function flush(): int
    {
        $files = glob($this->cachePath . DIRECTORY_SEPARATOR . '*');
        $count = 0;

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Clear expired cache entries
     */
    public function clearExpired(): int
    {
        $files = glob($this->cachePath . DIRECTORY_SEPARATOR . '*');
        $count = 0;

        foreach ($files as $file) {
            if (is_file($file)) {
                $data = unserialize(file_get_contents($file));

                if ($data !== false && isset($data['expires']) && $data['expires'] < time()) {
                    unlink($file);
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Get cache statistics
     */
    public function stats(): array
    {
        $files = glob($this->cachePath . DIRECTORY_SEPARATOR . '*');
        $totalSize = 0;
        $fileCount = 0;
        $expiredCount = 0;

        foreach ($files as $file) {
            if (is_file($file)) {
                $totalSize += filesize($file);
                $fileCount++;

                $data = unserialize(file_get_contents($file));
                if ($data !== false && isset($data['expires']) && $data['expires'] < time()) {
                    $expiredCount++;
                }
            }
        }

        return [
            'files' => $fileCount,
            'expired' => $expiredCount,
            'size' => $totalSize,
            'size_mb' => round($totalSize / 1024 / 1024, 2),
        ];
    }

    /**
     * Get cache file path for a key
     */
    private function getFilePath(string $key): string
    {
        $filename = md5($key) . '.cache';
        return $this->cachePath . DIRECTORY_SEPARATOR . $filename;
    }

    /**
     * Remember a value or compute and cache it
     */
    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->put($key, $value, $ttl);

        return $value;
    }
}
