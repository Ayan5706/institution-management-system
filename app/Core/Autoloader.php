<?php

declare(strict_types=1);

namespace App\Core;

final class Autoloader
{
    public static function register(?string $basePath = null): void
    {
        $basePath = $basePath ?: dirname(__DIR__, 2);

        spl_autoload_register(static function (string $class) use ($basePath): void {
            $prefix = 'App\\';

            if (!str_starts_with($class, $prefix)) {
                return;
            }

            $relativeClass = substr($class, strlen($prefix));
            $file = $basePath . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

            if (is_file($file)) {
                require_once $file;
            }
        });
    }
}
