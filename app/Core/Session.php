<?php

declare(strict_types=1);

namespace App\Core;

class Session
{
    private static array $config = [
        'name' => 'IMS_SESSION',
        'lifetime' => 86400, // 24 hours
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ];

    public static function init(array $config = []): void
    {
        self::$config = array_merge(self::$config, $config);

        session_name(self::$config['name']);

        session_set_cookie_params([
            'lifetime' => self::$config['lifetime'],
            'path' => self::$config['path'],
            'domain' => self::$config['domain'],
            'secure' => self::$config['secure'],
            'httponly' => self::$config['httponly'],
            'samesite' => self::$config['samesite'],
        ]);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Put data into session
     */
    public static function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get data from session
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if key exists in session
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Get and forget (retrieve then delete)
     */
    public static function pull(string $key, mixed $default = null): mixed
    {
        $value = self::get($key, $default);
        self::forget($key);
        return $value;
    }

    /**
     * Flash data (one-time use)
     */
    public static function flash(string $key, mixed $value): void
    {
        self::put("_flash_{$key}", $value);
    }

    /**
     * Get flashed data and remove
     */
    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $flashKey = "_flash_{$key}";
        $value = self::get($flashKey, $default);
        self::forget($flashKey);
        return $value;
    }

    /**
     * Check if flashed data exists
     */
    public static function hasFlash(string $key): bool
    {
        return self::has("_flash_{$key}");
    }

    /**
     * Delete from session
     */
    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Delete multiple keys
     */
    public static function forgetMany(array $keys): void
    {
        foreach ($keys as $key) {
            self::forget($key);
        }
    }

    /**
     * Clear all session data
     */
    public static function flush(): void
    {
        $_SESSION = [];
    }

    /**
     * Regenerate session ID
     */
    public static function regenerate(): bool
    {
        return session_regenerate_id(true);
    }

    /**
     * Destroy session
     */
    public static function destroy(): bool
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return session_destroy();
        }
        return false;
    }

    /**
     * Get session ID
     */
    public static function id(?string $id = null): ?string
    {
        if ($id !== null) {
            session_id($id);
        }
        return session_id() ?: null;
    }

    /**
     * Get all session data
     */
    public static function all(): array
    {
        return $_SESSION;
    }

    /**
     * Store CSRF token
     */
    public static function generateCsrfToken(): string
    {
        if (!self::has('_csrf_token')) {
            self::put('_csrf_token', bin2hex(random_bytes(32)));
        }
        return self::get('_csrf_token');
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCsrfToken(string $token): bool
    {
        $stored = self::get('_csrf_token');
        return hash_equals((string) $stored, (string) $token);
    }
}
