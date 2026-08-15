<?php

declare(strict_types=1);

$resolveEnv = static function (string $key, string $default = ''): string {
    $value = getenv($key);
    if ($value !== false && $value !== '') {
        return (string) $value;
    }

    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return (string) $_ENV[$key];
    }

    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        return (string) $_SERVER[$key];
    }

    $envFile = defined('BASE_PATH') ? BASE_PATH . DIRECTORY_SEPARATOR . '.env' : null;
    if ($envFile && is_file($envFile) && is_readable($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines !== false) {
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                    continue;
                }

                [$name, $value] = explode('=', $line, 2);
                if (trim($name) !== $key) {
                    continue;
                }

                $value = trim($value);
                if (
                    (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))
                ) {
                    $value = substr($value, 1, -1);
                }

                return $value !== '' ? $value : $default;
            }
        }
    }

    return $default;
};

return [
    'host' => $resolveEnv('MAIL_HOST'),
    'port' => (int) $resolveEnv('MAIL_PORT', '587'),
    'username' => $resolveEnv('MAIL_USERNAME'),
    'password' => $resolveEnv('MAIL_PASSWORD'),
    'encryption' => $resolveEnv('MAIL_ENCRYPTION', 'tls'),
    'from_address' => $resolveEnv('MAIL_FROM_ADDRESS', 'no-reply@example.com'),
    'from_name' => $resolveEnv('MAIL_FROM_NAME', $resolveEnv('APP_NAME', 'IMS')),
];
