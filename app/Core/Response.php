<?php

declare(strict_types=1);

namespace App\Core;

use App\Config\Config;

final class Response
{
    public static function json(array $payload, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public static function redirect(string $path): void
    {
        $baseUrl = rtrim((string) Config::get('app.url', ''), '/');
        $target = str_starts_with($path, 'http://') || str_starts_with($path, 'https://')
            ? $path
            : $baseUrl . '/' . ltrim($path, '/');

        header('Location: ' . $target);
        exit;
    }

    public static function text(string $content, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: text/plain; charset=utf-8');
        echo $content;
    }
}
