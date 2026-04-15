<?php

declare(strict_types=1);

namespace App\Core;

class Logger
{
    private string $logPath;
    private string $level;
    private int $maxFileSize = 10485760; // 10MB

    public function __construct(string $storagePath = '', string $level = 'info')
    {
        $this->logPath = $storagePath ?: dirname(__DIR__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
        $this->level = $level;
    }

    /**
     * Log an info message
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    /**
     * Log an error message
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    /**
     * Log a warning message
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }

    /**
     * Log a debug message
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context);
    }

    /**
     * Log an exception
     */
    public function exception(\Throwable $exception, array $context = []): void
    {
        $message = $exception->getMessage();
        $context['file'] = $exception->getFile();
        $context['line'] = $exception->getLine();
        $context['trace'] = $exception->getTraceAsString();

        $this->log('ERROR', $message, $context);
    }

    /**
     * Main logging method
     */
    private function log(string $level, string $message, array $context = []): void
    {
        // Check log level threshold
        if (!$this->shouldLog($level)) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | ' . json_encode($context) : '';
        $logEntry = "[{$timestamp}] [{$level}] {$message}{$contextStr}\n";

        $logFile = $this->getLogFile();
        $this->rotateLogIfNeeded($logFile);

        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Check if message should be logged based on level
     */
    private function shouldLog(string $level): bool
    {
        $levels = ['DEBUG' => 0, 'INFO' => 1, 'WARNING' => 2, 'ERROR' => 3];
        $threshold = $levels[$this->level] ?? 1;
        return ($levels[$level] ?? 1) >= $threshold;
    }

    /**
     * Get current log file path
     */
    private function getLogFile(): string
    {
        return $this->logPath . DIRECTORY_SEPARATOR . date('Y-m-d') . '.log';
    }

    /**
     * Rotate log file if it exceeds max size
     */
    private function rotateLogIfNeeded(string $logFile): void
    {
        if (is_file($logFile) && filesize($logFile) > $this->maxFileSize) {
            $timestamp = date('Y-m-d_H-i-s');
            $rotatedFile = dirname($logFile) . DIRECTORY_SEPARATOR . 
                          pathinfo($logFile, PATHINFO_FILENAME) . 
                          "_{$timestamp}." . 
                          pathinfo($logFile, PATHINFO_EXTENSION);

            rename($logFile, $rotatedFile);
        }
    }

    /**
     * Clear old log files
     */
    public function clearOldLogs(int $daysOld = 30): int
    {
        $threshold = time() - ($daysOld * 86400);
        $deleted = 0;

        $files = glob($this->logPath . DIRECTORY_SEPARATOR . '*.log');
        foreach ($files as $file) {
            if (filemtime($file) < $threshold) {
                unlink($file);
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Get recent log entries
     */
    public function getRecent(int $lines = 50): string
    {
        $logFile = $this->getLogFile();

        if (!is_file($logFile)) {
            return '';
        }

        $content = file_get_contents($logFile);
        $logLines = array_slice(explode("\n", $content), -$lines);

        return implode("\n", $logLines);
    }
}
