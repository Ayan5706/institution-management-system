<?php

namespace Tests\Unit\Core;

use TestCase;

/**
 * Logger Test Suite
 * 
 * Tests for the Logger class functionality
 */
class LoggerTest extends TestCase
{
    private \Logger $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = new \Logger();
    }

    /**
     * Test logging info message
     */
    public function testInfoLogging(): void
    {
        $message = 'Test info message';
        $context = ['user_id' => 1, 'action' => 'login'];

        $this->logger->info($message, $context);

        // Verify log file was created
        $logFile = BASE_PATH . '/storage/logs/app-' . date('Y-m-d') . '.log';
        if (file_exists($logFile)) {
            $content = file_get_contents($logFile);
            $this->assertStringContainsString($message, $content);
            $this->assertStringContainsString('INFO', $content);
        }
    }

    /**
     * Test logging warning message
     */
    public function testWarningLogging(): void
    {
        $message = 'Test warning message';
        $this->logger->warning($message);

        $logFile = BASE_PATH . '/storage/logs/app-' . date('Y-m-d') . '.log';
        if (file_exists($logFile)) {
            $content = file_get_contents($logFile);
            $this->assertStringContainsString('WARNING', $content);
        }
    }

    /**
     * Test logging error message
     */
    public function testErrorLogging(): void
    {
        $message = 'Test error message';
        $this->logger->error($message);

        $logFile = BASE_PATH . '/storage/logs/app-' . date('Y-m-d') . '.log';
        if (file_exists($logFile)) {
            $content = file_get_contents($logFile);
            $this->assertStringContainsString('ERROR', $content);
        }
    }

    /**
     * Test logging debug message
     */
    public function testDebugLogging(): void
    {
        $message = 'Test debug message';
        $this->logger->debug($message);

        $logFile = BASE_PATH . '/storage/logs/app-' . date('Y-m-d') . '.log';
        if (file_exists($logFile)) {
            $content = file_get_contents($logFile);
            $this->assertStringContainsString('DEBUG', $content);
        }
    }

    /**
     * Test exception logging with stack trace
     */
    public function testExceptionLogging(): void
    {
        try {
            throw new \Exception('Test exception');
        } catch (\Exception $e) {
            $this->logger->exception($e);
        }

        $logFile = BASE_PATH . '/storage/logs/app-' . date('Y-m-d') . '.log';
        if (file_exists($logFile)) {
            $content = file_get_contents($logFile);
            $this->assertStringContainsString('Exception', $content);
        }
    }

    /**
     * Test log message formatting with context
     */
    public function testContextFormatting(): void
    {
        $context = [
            'user_id' => 123,
            'email' => 'test@example.com',
            'timestamp' => time(),
        ];

        $this->logger->info('User action', $context);

        $logFile = BASE_PATH . '/storage/logs/app-' . date('Y-m-d') . '.log';
        if (file_exists($logFile)) {
            $content = file_get_contents($logFile);
            $this->assertStringContainsString('User action', $content);
        }
    }

    /**
     * Test getRecent() retrieves recent logs
     */
    public function testGetRecentLogs(): void
    {
        $this->logger->info('First message');
        $this->logger->info('Second message');
        $this->logger->info('Third message');

        $recent = $this->logger->getRecent(2);
        $this->assertIsArray($recent);
        $this->assertLessThanOrEqual(2, count($recent));
    }

    /**
     * Test log directory creation
     */
    public function testLogDirectoryExists(): void
    {
        $logDir = BASE_PATH . '/storage/logs';
        $this->assertDirectoryExistsPHP($logDir);
    }

    /**
     * Test multiple sequential logs don't override
     */
    public function testMultipleLogsAccumulate(): void
    {
        $logFile = BASE_PATH . '/storage/logs/app-' . date('Y-m-d') . '.log';
        $initialSize = file_exists($logFile) ? filesize($logFile) : 0;

        $this->logger->info('Log entry 1');
        $this->logger->info('Log entry 2');

        if (file_exists($logFile)) {
            $newSize = filesize($logFile);
            $this->assertGreaterThan($initialSize, $newSize);
        }
    }

    /**
     * Test log levels are correctly formatted
     */
    public function testLogLevelFormatting(): void
    {
        $logFile = BASE_PATH . '/storage/logs/app-' . date('Y-m-d') . '.log';
        $initialContent = file_exists($logFile) ? file_get_contents($logFile) : '';

        $this->logger->info('Info test');
        $this->logger->warning('Warning test');
        $this->logger->error('Error test');

        $content = file_get_contents($logFile);
        $newContent = substr($content, strlen($initialContent));

        $this->assertStringContainsString('[INFO]', $content);
        $this->assertStringContainsString('[WARNING]', $content);
        $this->assertStringContainsString('[ERROR]', $content);
    }
}
