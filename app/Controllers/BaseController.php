<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Config;

abstract class BaseController
{
    private ?array $jsonInputCache = null;

    protected function input(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_POST)) {
            return $_POST[$key];
        }

        if (array_key_exists($key, $_GET)) {
            return $_GET[$key];
        }

        $jsonInput = $this->getJsonInput();
        if (array_key_exists($key, $jsonInput)) {
            return $jsonInput[$key];
        }

        return $default;
    }

    private function getJsonInput(): array
    {
        if ($this->jsonInputCache !== null) {
            return $this->jsonInputCache;
        }

        $raw = file_get_contents('php://input');
        if (!is_string($raw) || trim($raw) === '') {
            $this->jsonInputCache = [];
            return $this->jsonInputCache;
        }

        $decoded = json_decode($raw, true);
        $this->jsonInputCache = is_array($decoded) ? $decoded : [];
        return $this->jsonInputCache;
    }

    protected function allInput(): array
    {
        return array_merge($_GET, $_POST);
    }

    protected function json(array $payload, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    protected function view(string $view, array $data = []): void
    {
        $viewsPath = (string) Config::get('paths.views', BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Views');
        $file = $viewsPath . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $view) . '.php';

        if (!is_file($file)) {
            http_response_code(404);
            echo 'View not found: ' . htmlspecialchars($view, ENT_QUOTES, 'UTF-8');
            return;
        }

        extract($data, EXTR_SKIP);
        require $file;
    }

    protected function redirect(string $path): void
    {
        $baseUrl = rtrim((string) Config::get('app.url', ''), '/');
        $target = str_starts_with($path, 'http://') || str_starts_with($path, 'https://')
            ? $path
            : $baseUrl . '/' . ltrim($path, '/');

        header('Location: ' . $target);
        exit;
    }
}
