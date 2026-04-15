<?php

declare(strict_types=1);

namespace App\Core;

use App\Config\Config;

final class Application
{
    private Router $router;

    public function __construct()
    {
        $this->router = new Router();
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function loadRoutes(string $routesFile): void
    {
        if (!is_file($routesFile)) {
            return;
        }

        $router = $this->router;
        require $routesFile;
    }

    public function run(): void
    {
        // Start output buffering to prevent "headers already sent" errors
        // This allows us to set response headers even if something outputs during initialization
        $startedBuffer = false;
        if (ob_get_level() === 0) {
            ob_start();
            $startedBuffer = true;
        }
        
        try {
            $this->startSessionIfNeeded();

            $request = Request::capture();
            $this->router->dispatch($request);
        } finally {
            // Always flush buffer if we started it, even on errors
            if ($startedBuffer && ob_get_level() > 0) {
                ob_end_flush();
            }
        }
    }

    private function startSessionIfNeeded(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $sessionName = (string) Config::get('session.name', 'IMSSESSID');
        if ($sessionName !== '') {
            session_name($sessionName);
        }

        $sessionPath = (string) Config::get('paths.sessions', '');

        if ($sessionPath !== '' && !is_dir($sessionPath)) {
            @mkdir($sessionPath, 0777, true);
        }

        if ($sessionPath !== '' && is_dir($sessionPath)) {
            session_save_path($sessionPath);
        }

        $lifetime = (int) Config::get('session.lifetime', 7200);
        $expireOnClose = (bool) Config::get('session.expire_on_close', false);
        $secure = (bool) Config::get('session.secure', false);
        $httponly = (bool) Config::get('session.httponly', true);
        $samesite = (string) Config::get('session.samesite', 'Lax');

        if ($expireOnClose) {
            $lifetime = 0;
        }

        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path' => '/',
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => $samesite,
        ]);

        session_start();
    }
}
