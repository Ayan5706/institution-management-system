<?php

declare(strict_types=1);

use App\Config\Config;

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);

        if ($path === '') {
            return $base;
        }

        return rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        return $value;
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $baseUrl = rtrim((string) Config::get('app.url', ''), '/');

        if ($path === '') {
            return $baseUrl;
        }

        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        $storage = (string) Config::get('paths.storage', base_path('storage'));

        if ($path === '') {
            return $storage;
        }

        return rtrim($storage, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('e')) {
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_POST)) {
            return $_POST[$key];
        }

        return $default;
    }
}

if (!function_exists('is_post')) {
    function is_post(): bool
    {
        return strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST';
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (!isset($_SESSION['_token']) || !is_string($_SESSION['_token']) || $_SESSION['_token'] === '') {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        $token = csrf_token();
        return '<input type="hidden" name="_token" value="' . e($token) . '">';
    }
}

if (!function_exists('dd')) {
    function dd(mixed ...$values): never
    {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');

        foreach ($values as $value) {
            print_r($value);
            echo PHP_EOL;
        }

        exit(1);
    }
}

if (!function_exists('partial')) {
    function partial(string $path, array $data = []): void
    {
        $file = base_path('app/Views/partials/' . trim($path, '/') . '.php');
        if (!is_file($file)) {
            return;
        }

        if ($data !== []) {
            extract($data, EXTR_SKIP);
        }

        require $file;
    }
}
