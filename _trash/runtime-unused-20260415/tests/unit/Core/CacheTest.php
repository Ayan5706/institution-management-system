<?php

namespace Tests\Unit\Core;

use TestCase;

/**
 * Cache Test Suite
 * 
 * Tests for the Cache class functionality
 */
class CacheTest extends TestCase
{
    private \Cache $cache;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cache = new \Cache();
        $this->cache->flush(); // Clear cache before each test
    }

    /**
     * Test storing a value in cache
     */
    public function testCachePut(): void
    {
        $this->cache->put('test_key', 'test_value', 3600);
        $this->assertTrue($this->cache->has('test_key'));
    }

    /**
     * Test retrieving a cached value
     */
    public function testCacheGet(): void
    {
        $value = 'test_value_123';
        $this->cache->put('my_key', $value);

        $retrieved = $this->cache->get('my_key');
        $this->assertEquals($value, $retrieved);
    }

    /**
     * Test getting non-existent key returns null
     */
    public function testGetNonExistentKey(): void
    {
        $result = $this->cache->get('non_existent_key');
        $this->assertNull($result);
    }

    /**
     * Test has() returns true for existing key
     */
    public function testHasExistingKey(): void
    {
        $this->cache->put('existing_key', 'value');
        $this->assertTrue($this->cache->has('existing_key'));
    }

    /**
     * Test has() returns false for non-existing key
     */
    public function testHasNonExistingKey(): void
    {
        $this->assertFalse($this->cache->has('non_existent_key'));
    }

    /**
     * Test forget() removes a key
     */
    public function testForgetKey(): void
    {
        $this->cache->put('key_to_forget', 'value');
        $this->assertTrue($this->cache->has('key_to_forget'));

        $this->cache->forget('key_to_forget');
        $this->assertFalse($this->cache->has('key_to_forget'));
    }

    /**
     * Test flush() clears all cache
     */
    public function testFlushCache(): void
    {
        $this->cache->put('key1', 'value1');
        $this->cache->put('key2', 'value2');
        $this->cache->put('key3', 'value3');

        $this->cache->flush();

        $this->assertFalse($this->cache->has('key1'));
        $this->assertFalse($this->cache->has('key2'));
        $this->assertFalse($this->cache->has('key3'));
    }

    /**
     * Test remember() stores and retrieves value
     */
    public function testRememberPattern(): void
    {
        $callCount = 0;
        $callback = function () use (&$callCount) {
            $callCount++;
            return 'computed_value';
        };

        $result1 = $this->cache->remember('computed_key', 3600, $callback);
        $result2 = $this->cache->remember('computed_key', 3600, $callback);

        $this->assertEquals('computed_value', $result1);
        $this->assertEquals('computed_value', $result2);
        // Callback should be called only once
        $this->assertEquals(1, $callCount);
    }

    /**
     * Test TTL expiration (with small timeout)
     */
    public function testTTLExpiration(): void
    {
        // Store with 1 second TTL
        $this->cache->put('short_lived', 'value', 1);
        $this->assertTrue($this->cache->has('short_lived'));

        // Wait for expiration
        sleep(2);

        // Should be expired now
        $result = $this->cache->get('short_lived');
        $this->assertNull($result);
    }

    /**
     * Test storing different data types
     */
    public function testCachingDataTypes(): void
    {
        // String
        $this->cache->put('string', 'value');
        $this->assertEquals('value', $this->cache->get('string'));

        // Integer
        $this->cache->put('int', 123);
        $this->assertEquals(123, $this->cache->get('int'));

        // Array
        $array = ['a' => 1, 'b' => 2];
        $this->cache->put('array', $array);
        $this->assertEquals($array, $this->cache->get('array'));

        // Object
        $obj = (object)['id' => 1, 'name' => 'test'];
        $this->cache->put('object', $obj);
        $cached = $this->cache->get('object');
        $this->assertEquals($obj->id, $cached->id);
    }

    /**
     * Test cache key hashing
     */
    public function testCacheKeyHashing(): void
    {
        // Different keys should store different values
        $this->cache->put('key_a', 'value_a');
        $this->cache->put('key_b', 'value_b');

        $this->assertEquals('value_a', $this->cache->get('key_a'));
        $this->assertEquals('value_b', $this->cache->get('key_b'));
    }

    /**
     * Test stats() returns cache statistics
     */
    public function testCacheStats(): void
    {
        $this->cache->put('key1', 'value1');
        $this->cache->put('key2', 'value2');

        $stats = $this->cache->stats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('files', $stats);
        $this->assertArrayHasKey('expired', $stats);
        $this->assertArrayHasKey('size', $stats);
    }

    /**
     * Test cache directory exists
     */
    public function testCacheDirectoryExists(): void
    {
        $cacheDir = BASE_PATH . '/storage/cache';
        $this->assertDirectoryExistsPHP($cacheDir);
    }

    /**
     * Test multiple cache instances don't interfere
     */
    public function testMultipleCacheInstances(): void
    {
        $cache1 = new \Cache();
        $cache2 = new \Cache();

        $cache1->put('shared_key', 'from_cache1');
        // Both instances should see the same cache file
        $this->assertEquals('from_cache1', $cache2->get('shared_key'));
    }

    /**
     * Test default TTL
     */
    public function testDefaultTTL(): void
    {
        // Put without explicit TTL (should use default, usually 3600)
        $this->cache->put('default_ttl', 'value');
        $this->assertTrue($this->cache->has('default_ttl'));
    }
}
