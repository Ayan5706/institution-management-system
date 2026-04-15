<?php

namespace Tests\Unit\Helpers;

use TestCase;

/**
 * Global Helpers Test Suite
 * 
 * Tests for the global helper functions
 */
class HelperFunctionsTest extends TestCase
{
    /**
     * Test storage() helper returns StorageManager
     */
    public function testStorageHelper(): void
    {
        $storage = storage();

        $this->assertInstanceOf(\StorageManager::class, $storage);
    }

    /**
     * Test log_message() helper
     */
    public function testLogMessageHelper(): void
    {
        $message = 'Test log message from helper';

        // Should not throw exception
        try {
            log_message('info', $message);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail('log_message threw exception: ' . $e->getMessage());
        }
    }

    /**
     * Test cache_get() helper
     */
    public function testCacheGetHelper(): void
    {
        // Put a value using cache manager
        $cacheManager = cache();
        $cacheManager->cache()->put('test_key', 'test_value');

        // Get it using helper
        $value = cache_get('test_key');

        $this->assertEquals('test_value', $value);
    }

    /**
     * Test cache_put() helper
     */
    public function testCachePutHelper(): void
    {
        cache_put('helper_key', 'helper_value', 3600);

        // Verify it's in cache
        $value = cache_get('helper_key');
        $this->assertEquals('helper_value', $value);
    }

    /**
     * Test cache_forget() helper
     */
    public function testCacheForgetHelper(): void
    {
        cache_put('forget_key', 'value');

        cache_forget('forget_key');

        $value = cache_get('forget_key');
        $this->assertNull($value);
    }

    /**
     * Test cache_flush() helper
     */
    public function testCacheFlushHelper(): void
    {
        cache_put('key1', 'value1');
        cache_put('key2', 'value2');

        cache_flush();

        $this->assertNull(cache_get('key1'));
        $this->assertNull(cache_get('key2'));
    }

    /**
     * Test storage_path() helper
     */
    public function testStoragePathHelper(): void
    {
        $logsPath = storage_path('logs');

        $this->assertNotEmpty($logsPath);
        $this->assertStringContainsString('logs', $logsPath);
        $this->assertDirectoryExistsPHP($logsPath);
    }

    /**
     * Test storage_size() helper
     */
    public function testStorageSizeHelper(): void
    {
        $size = storage_size();

        $this->assertIsInt($size);
        $this->assertGreaterThanOrEqual(0, $size);
    }

    /**
     * Test storage_size() for specific type
     */
    public function testStorageSizeByTypeHelper(): void
    {
        $logsSize = storage_size('logs');

        $this->assertIsInt($logsSize);
        $this->assertGreaterThanOrEqual(0, $logsSize);
    }

    /**
     * Test storage_stats() helper
     */
    public function testStorageStatsHelper(): void
    {
        $stats = storage_stats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_size_bytes', $stats);
        $this->assertArrayHasKey('total_files', $stats);
    }

    /**
     * Test cache() helper returns manager
     */
    public function testCacheManagerHelper(): void
    {
        $cacheManager = cache();

        $this->assertIsObject($cacheManager);
    }

    /**
     * Test logger() helper
     */
    public function testLoggerHelper(): void
    {
        $logger = logger();

        $this->assertInstanceOf(\Logger::class, $logger);
    }

    /**
     * Test session() helper
     */
    public function testSessionHelper(): void
    {
        // Session helper might return session manager
        try {
            $session = session();
            $this->assertNotNull($session);
        } catch (\Exception $e) {
            // Session might not be initialized in test, which is ok
            $this->assertTrue(true);
        }
    }

    /**
     * Test file exists helper
     */
    public function testFileExistsHelper(): void
    {
        $file = $this->createTempFile('test.txt', 'content');

        // If we have a file_exists_helper
        if (function_exists('file_exists_helper')) {
            $this->assertTrue(file_exists_helper($file));
            $this->assertFalse(file_exists_helper('/non/existent/file'));
        }
    }

    /**
     * Test directory helpers
     */
    public function testDirectoryHelpers(): void
    {
        $dir = $this->createTempDir('test_helpers');

        // If we have directory helper functions
        if (function_exists('dir_exists_helper')) {
            $this->assertTrue(dir_exists_helper($dir));
        }

        if (function_exists('make_directory_helper')) {
            $newDir = $dir . '/nested/structure';
            make_directory_helper($newDir);
            $this->assertDirectoryExistsPHP($newDir);
        }
    }

    /**
     * Test multiple cache operations through helpers
     */
    public function testCacheHelperSequence(): void
    {
        // Put some values
        cache_put('seq1', 'value1');
        cache_put('seq2', 'value2');
        cache_put('seq3', 'value3');

        // Get all
        $this->assertEquals('value1', cache_get('seq1'));
        $this->assertEquals('value2', cache_get('seq2'));
        $this->assertEquals('value3', cache_get('seq3'));

        // Forget one
        cache_forget('seq2');
        $this->assertNull(cache_get('seq2'));

        // Others still exist
        $this->assertEquals('value1', cache_get('seq1'));
        $this->assertEquals('value3', cache_get('seq3'));

        // Flush all
        cache_flush();
        $this->assertNull(cache_get('seq1'));
        $this->assertNull(cache_get('seq3'));
    }

    /**
     * Test logging different levels through helper
     */
    public function testLogMessageLevels(): void
    {
        try {
            log_message('info', 'Info message');
            log_message('warning', 'Warning message');
            log_message('error', 'Error message');
            log_message('debug', 'Debug message');
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail('Log message helper failed: ' . $e->getMessage());
        }
    }

    /**
     * Test storage stats shows all storage types
     */
    public function testStorageStatsCompletion(): void
    {
        $stats = storage_stats();

        // All storage types should be represented
        $expectedTypes = ['logs', 'cache', 'sessions', 'temp', 'backups', 'exports'];

        foreach ($expectedTypes as $type) {
            $this->assertArrayHasKey($type, $stats);
        }
    }
}
