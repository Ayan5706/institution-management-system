<?php

/**
 * Base Test Case Class
 * 
 * Provides common testing utilities for all test cases.
 * Extends PHPUnit\Framework\TestCase with application-specific capabilities.
 */

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    /**
     * Test database connection
     * @var \PDO
     */
    protected static ?\PDO $db = null;

    /**
     * Temporary storage for test artifacts
     * @var string
     */
    protected string $tempDir;

    /**
     * Setup test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create temporary directory for this test
        $this->tempDir = BASE_PATH . '/storage/temp/test/test_' . uniqid();
        @mkdir($this->tempDir, 0755, true);
        
        // Reset any global state
        $_SESSION = [];
        $_FILES = [];
    }

    /**
     * Cleanup after test
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Clean up temporary files
        if (is_dir($this->tempDir)) {
            array_map('unlink', array_filter((array) glob("{$this->tempDir}/*")));
            @rmdir($this->tempDir);
        }
    }

    /**
     * Get database connection for test database
     */
    protected static function getTestDB(): \PDO
    {
        if (self::$db === null) {
            $config = $GLOBALS['test_db'];
            try {
                self::$db = new \PDO(
                    "mysql:host={$config['host']};port={$config['port']};charset=utf8mb4",
                    $config['username'],
                    $config['password'],
                    [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    ]
                );
            } catch (\PDOException $e) {
                throw new \Exception("Test database connection failed: " . $e->getMessage());
            }
        }
        
        return self::$db;
    }

    /**
     * Create a temporary test file
     * 
     * @param string $filename
     * @param string $content
     * @return string Path to created file
     */
    protected function createTempFile(string $filename, string $content = ''): string
    {
        $path = $this->tempDir . '/' . $filename;
        $dir = dirname($path);
        
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        
        file_put_contents($path, $content);
        return $path;
    }

    /**
     * Create a temporary directory
     * 
     * @param string $dirname
     * @return string Path to created directory
     */
    protected function createTempDir(string $dirname): string
    {
        $path = $this->tempDir . '/' . $dirname;
        @mkdir($path, 0755, true);
        return $path;
    }

    /**
     * Assert file exists
     */
    protected function assertFileExistsPHP(string $file, string $message = ''): void
    {
        $this->assertTrue(
            file_exists($file),
            $message ?: "File does not exist: $file"
        );
    }

    /**
     * Assert directory exists
     */
    protected function assertDirectoryExistsPHP(string $dir, string $message = ''): void
    {
        $this->assertTrue(
            is_dir($dir),
            $message ?: "Directory does not exist: $dir"
        );
    }

    /**
     * Assert file contains text
     */
    protected function assertFileContains(string $file, string $text, string $message = ''): void
    {
        $content = file_get_contents($file);
        $this->assertStringContainsString(
            $text,
            $content,
            $message ?: "File '$file' does not contain '$text'"
        );
    }

    /**
     * Get a container instance
     * 
     * @return mixed
     */
    protected function getContainer()
    {
        return $GLOBALS['container'] ?? null;
    }

    /**
     * Create a mock object for testing
     * 
     * @param string $class
     * @param array $methods
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function createMockObject(string $class, array $methods = []): \PHPUnit\Framework\MockObject\MockObject
    {
        return $this->createPartialMock($class, $methods);
    }

    /**
     * Assert response has status code
     */
    protected function assertResponseStatusCode(int $expected, int $actual, string $message = ''): void
    {
        $this->assertEquals(
            $expected,
            $actual,
            $message ?: "Expected status code $expected, got $actual"
        );
    }

    /**
     * Assert array has keys
     */
    protected function assertArrayHasKeys(array $keys, array $array, string $message = ''): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey(
                $key,
                $array,
                $message ?: "Array missing key: $key"
            );
        }
    }
}
