<?php

declare(strict_types=1);

namespace App\Helpers;

function logger_helper(string $channel, string $message): void
{
    $timestamp = date('Y-m-d H:i:s');
    $line = sprintf("[%s] %s: %s\n", $timestamp, $channel, $message);

    if (function_exists('storage_path')) {
        $logFile = \storage_path('logs' . DIRECTORY_SEPARATOR . 'app.log');
    } else {
        $logFile = dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR . 'storage'
            . DIRECTORY_SEPARATOR . 'logs'
            . DIRECTORY_SEPARATOR . 'app.log';
    }

    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }

    @file_put_contents($logFile, $line, FILE_APPEND);
}
