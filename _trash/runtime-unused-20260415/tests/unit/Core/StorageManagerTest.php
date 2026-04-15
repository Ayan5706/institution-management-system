<?php

namespace Tests\Unit\Core;

use TestCase;

/**
 * StorageManager Test Suite
 * 
 * Tests for the StorageManager class functionality
 */
class StorageManagerTest extends TestCase
{
    private \StorageManager $storage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->storage = new \StorageManager();
    }

    /**
     * Test getting storage path
     */
    public function testGetStoragePath(): void
    {
        $path = $this->storage->getPath('logs');
        $this->assertNotEmpty($path);
        $this->assertStringContainsString('storage', $path);
        $this->assertStringContainsString('logs', $path);
    }

    /**
     * Test getting all storage paths
     */
    public function testGetAllPaths(): void
    {
        $paths = $this->storage->getPaths();

        $this->assertIsArray($paths);
        $this->assertArrayHasKey('logs', $paths);
        $this->assertArrayHasKey('cache', $paths);
        $this->assertArrayHasKey('sessions', $paths);
        $this->assertArrayHasKey('temp', $paths);
        $this->assertArrayHasKey('backups', $paths);
        $this->assertArrayHasKey('exports', $paths);
    }

    /**
     * Test storage paths exist
     */
    public function testStoragePathsExist(): void
    {
        foreach ($this->storage->getPaths() as $path) {
            $this->assertDirectoryExistsPHP($path);
        }
    }

    /**
     * Test getting storage size
     */
    public function testGetStorageSize(): void
    {
        $size = $this->storage->getSize();

        $this->assertIsInt($size);
        $this->assertGreaterThanOrEqual(0, $size);
    }

    /**
     * Test getting size for specific storage type
     */
    public function testGetSizeByType(): void
    {
        $logSize = $this->storage->getSize('logs');

        $this->assertIsInt($logSize);
        $this->assertGreaterThanOrEqual(0, $logSize);
    }

    /**
     * Test counting files by type
     */
    public function testCountFilesByType(): void
    {
        // Create a test file
        $testFile = $this->storage->getPath('logs') . '/test_' . uniqid() . '.log';
        file_put_contents($testFile, 'test content');

        $count = $this->storage->countFiles('logs');

        $this->assertIsInt($count);
        $this->assertGreaterThan(0, $count);

        // Cleanup
        @unlink($testFile);
    }

    /**
     * Test getting statistics
     */
    public function testGetStatistics(): void
    {
        $stats = $this->storage->getStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_size_bytes', $stats);
        $this->assertArrayHasKey('total_size_mb', $stats);
        $this->assertArrayHasKey('total_files', $stats);

        // Stats should have data for each type
        foreach (['logs', 'cache', 'sessions', 'temp', 'backups', 'exports'] as $type) {
            $this->assertArrayHasKey($type, $stats);
        }
    }

    /**
     * Test cleanup method exists and is callable
     */
    public function testCleanupMethod(): void
    {
        // Create a test temp file that's old
        $tempDir = $this->storage->getPath('temp');
        $oldFile = $tempDir . '/test_old_' . time() . '.tmp';
        file_put_contents($oldFile, 'old content');

        // Cleanup should not throw exception
        try {
            $this->storage->cleanup('temp', 1); // Files older than 1 second
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail('Cleanup threw exception: ' . $e->getMessage());
        }

        // Cleanup
        @unlink($oldFile);
    }

    /**
     * Test fixing permissions
     */
    public function testFixPermissions(): void
    {
        $testDir = $this->createTempDir('permission_test');

        // Fix permissions should not throw
        try {
            $this->storage->fixPermissions();
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail('fixPermissions threw exception: ' . $e->getMessage());
        }
    }

    /**
     * Test ensure directory exists
     */
    public function testEnsureDirectoryExists(): void
    {
        $dir = $this->tempDir . '/test_ensure_' . uniqid();

        $this->assertFalse(is_dir($dir));

        $this->storage->ensureDirectoryExists($dir);

        $this->assertDirectoryExistsPHP($dir);
    }

    /**
     * Test getting logs path
     */
    public function testGetLogsPath(): void
    {
        $logsPath = $this->storage->getPath('logs');
        $this->assertStringContainsString('logs', $logsPath);
        $this->assertDirectoryExistsPHP($logsPath);
    }

    /**
     * Test getting cache path
     */
    public function testGetCachePath(): void
    {
        $cachePath = $this->storage->getPath('cache');
        $this->assertStringContainsString('cache', $cachePath);
        $this->assertDirectoryExistsPHP($cachePath);
    }

    /**
     * Test getting sessions path
     */
    public function testGetSessionsPath(): void
    {
        $sessionPath = $this->storage->getPath('sessions');
        $this->assertStringContainsString('sessions', $sessionPath);
        $this->assertDirectoryExistsPHP($sessionPath);
    }

    /**
     * Test storage manager is singleton-like
     */
    public function testStorageManagerConsistency(): void
    {
        $storage1 = new \StorageManager();
        $storage2 = new \StorageManager();

        $this->assertEquals(
            $storage1->getPath('logs'),
            $storage2->getPath('logs')
        );
    }

    /**
     * Test getting relative storage path
     */
    public function testRelativeStoragePath(): void
    {
        $path = $this->storage->getPath('logs');

        // Should be absolute path
        $this->assertTrue(is_dir($path));
    }

    /**
     * Test invalid type returns null or throws
     */
    public function testInvalidStorageType(): void
    {
        // Should handle gracefully
        try {
            $path = $this->storage->getPath('invalid_type');
            // If it returns, it should be null or empty
            $this->assertTrue($path === null || $path === '');
        } catch (\Exception $e) {
            // Exception is also acceptable
            $this->assertTrue(true);
        }
    }

    /**
     * Test calculating size in bytes and MB
     */
    public function testSizeCalculationFormats(): void
    {
        $stats = $this->storage->getStats();

        $this->assertIsInt($stats['total_size_bytes']);
        $this->assertIsFloat($stats['total_size_mb']);

        // MB should be bytes divided by 1024*1024
        $expectedMB = $stats['total_size_bytes'] / (1024 * 1024);
        $this->assertEqualsWithDelta($expectedMB, $stats['total_size_mb'], 0.01);
    }
}
