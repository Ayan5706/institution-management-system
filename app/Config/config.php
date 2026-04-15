<?php

declare(strict_types=1);

namespace App\Config;

final class Config
{
    private static array $cache = [];

    public static function boot(?string $envFilePath = null): void
    {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'constants.php';

        $envPath = $envFilePath ?: BASE_PATH . DIRECTORY_SEPARATOR . '.env';
        Env::load($envPath);

        $timezone = self::get('app.timezone', 'UTC');
        date_default_timezone_set((string) $timezone);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $file = array_shift($segments);

        if ($file === null || $file === '') {
            return $default;
        }

        $config = self::loadConfigFile($file);

        if (empty($segments)) {
            return $config;
        }

        $value = $config;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    private static function loadConfigFile(string $file): array
    {
        if (isset(self::$cache[$file])) {
            return self::$cache[$file];
        }

        $path = __DIR__ . DIRECTORY_SEPARATOR . $file . '.php';

        if (!is_file($path)) {
            self::$cache[$file] = [];
            return self::$cache[$file];
        }

        $loaded = require $path;
        self::$cache[$file] = is_array($loaded) ? $loaded : [];

        return self::$cache[$file];
    }
}
