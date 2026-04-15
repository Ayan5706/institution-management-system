<?php

declare(strict_types=1);

use App\Config\Config;

$debug = (bool) Config::get('app.debug', false);
$logDir = (string) Config::get('paths.logs', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs');
$logFile = $logDir . DIRECTORY_SEPARATOR . 'app.log';

if (!is_dir($logDir)) {
    @mkdir($logDir, 0777, true);
}

error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('display_startup_errors', $debug ? '1' : '0');

set_exception_handler(static function (\Throwable $exception) use ($debug, $logFile): void {
    $message = sprintf(
        "[%s] Uncaught %s: %s in %s:%d\n%s\n\n",
        date('Y-m-d H:i:s'),
        get_class($exception),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );

    @file_put_contents($logFile, $message, FILE_APPEND);

    http_response_code(500);
    if ($debug) {
        echo nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
        return;
    }

    echo 'An unexpected error occurred.';
});

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    throw new \ErrorException($message, 0, $severity, $file, $line);
});

register_shutdown_function(static function () use ($debug, $logFile): void {
    $error = error_get_last();

    if ($error === null) {
        return;
    }

    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];

    if (!in_array($error['type'] ?? 0, $fatalTypes, true)) {
        return;
    }

    $message = sprintf(
        "[%s] Fatal error: %s in %s:%d\n\n",
        date('Y-m-d H:i:s'),
        (string) ($error['message'] ?? 'Unknown fatal error'),
        (string) ($error['file'] ?? 'unknown'),
        (int) ($error['line'] ?? 0)
    );

    @file_put_contents($logFile, $message, FILE_APPEND);

    http_response_code(500);
    if ($debug) {
        echo nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
        return;
    }

    echo 'A fatal error occurred.';
});
