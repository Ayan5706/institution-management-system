<?php

/**
 * Test Examples and Patterns
 * 
 * This file demonstrates common testing patterns used in the IMS test suite.
 * Refer to this when creating new tests.
 */

namespace Tests\Examples;

use TestCase;

// ═══════════════════════════════════════════════════════════════
// UNIT TEST EXAMPLES
// ═══════════════════════════════════════════════════════════════

/**
 * Example: Testing a simple service class
 */
class SimpleServiceTestExample extends TestCase
{
    public function testServiceCalculation(): void
    {
        // Arrange
        $input = 5;
        $expected = 10;

        // Act
        $result = $input * 2;

        // Assert
        $this->assertEquals($expected, $result);
    }

    public function testServiceWithMultipleAssertions(): void
    {
        // Arrange
        $data = ['name' => 'John', 'age' => 30, 'active' => true];

        // Act & Assert
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('age', $data);
        $this->assertEquals('John', $data['name']);
        $this->assertGreaterThanOrEqual(18, $data['age']);
        $this->assertTrue($data['active']);
    }
}

/**
 * Example: Testing with setup and teardown
 */
class ServiceWithSetupTestExample extends TestCase
{
    private $service;
    private $tempFile;

    protected function setUp(): void
    {
        parent::setUp();
        // Initialize service
        $this->service = new \stdClass();
        // Create temp file
        $this->tempFile = $this->createTempFile('service_test.txt', 'initial content');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Cleanup happens automatically for temp files
    }

    public function testUsingService(): void
    {
        $this->assertIsObject($this->service);
        $this->assertFileExistsPHP($this->tempFile);
    }
}

/**
 * Example: Testing with mocks
 */
class ServiceWithMockTestExample extends TestCase
{
    public function testServiceWithMockedDependency(): void
    {
        // Create mock
        $mockCache = MockHelper::createMockCache();

        // Use mock
        $mockCache->put('key', 'value');
        $result = $mockCache->get('key');

        // Assert
        $this->assertEquals('value', $result);
    }

    public function testMultipleMocks(): void
    {
        $mockLogger = MockHelper::createMockLogger();
        $mockCache = MockHelper::createMockCache();
        $mockDb = MockHelper::createMockDatabase();

        // Use all mocks
        $mockLogger->info('Test message');
        $mockCache->put('test', 'data');
        $mockDb->insert('table', ['id' => 1, 'name' => 'Test']);

        // Verify interactions
        $this->assertTrue(true);
    }
}

/**
 * Example: Testing data generation
 */
class DataGenerationTestExample extends TestCase
{
    public function testGeneratingTestUsers(): void
    {
        // Generate single user
        $user = TestHelper::createFakeUser();
        $this->assertArrayHasKey('email', $user);
        $this->assertEqual('active', $user['status']);

        // Generate with overrides
        $customUser = TestHelper::createFakeUser([
            'email' => 'custom@example.com',
            'role' => 'admin',
        ]);
        $this->assertEquals('custom@example.com', $customUser['email']);
        $this->assertEquals('admin', $customUser['role']);
    }

    public function testGeneratingVariousModels(): void
    {
        $student = TestHelper::createFakeStudent();
        $program = TestHelper::createFakeProgram();
        $subject = TestHelper::createFakeSubject();
        $fee = TestHelper::createFakeFee();

        $this->assertArrayHasKey('roll_number', $student);
        $this->assertArrayHasKey('code', $program);
        $this->assertArrayHasKey('credits', $subject);
        $this->assertArrayHasKey('amount', $fee);
    }
}

// ═══════════════════════════════════════════════════════════════
// EXCEPTION AND ERROR TESTING
// ═══════════════════════════════════════════════════════════════

/**
 * Example: Testing exception handling
 */
class ExceptionTestingExample extends TestCase
{
    public function testExceptionIsThrown(): void
    {
        $this->expectException(\Exception::class);

        throw new \Exception('Test exception');
    }

    public function testExceptionMessage(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Expected message');

        throw new \Exception('Expected message');
    }

    public function testExceptionCode(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(500);

        throw new \Exception('Error', 500);
    }

    public function testTryCatchPattern(): void
    {
        try {
            throw new \InvalidArgumentException('Invalid input');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Invalid input', $e->getMessage());
        }
    }
}

// ═══════════════════════════════════════════════════════════════
// CACHE TESTING EXAMPLES
// ═══════════════════════════════════════════════════════════════

/**
 * Example: Testing cache operations
 */
class CacheOperationsExample extends TestCase
{
    public function testCacheBasicOperations(): void
    {
        $cache = new \Cache();
        $cache->flush(); // Clean slate

        // Put and get
        $cache->put('user:1', ['name' => 'John', 'email' => 'john@example.com']);
        $user = $cache->get('user:1');

        $this->assertEquals('John', $user['name']);

        // Forget
        $cache->forget('user:1');
        $this->assertNull($cache->get('user:1'));
    }

    public function testCacheRememberPattern(): void
    {
        $cache = new \Cache();
        $cache->flush();

        $callCount = 0;
        $callback = function () use (&$callCount) {
            $callCount++;
            return ['expensive' => 'computation'];
        };

        // First call
        $result1 = $cache->remember('expensive_key', 3600, $callback);
        $this->assertEquals(1, $callCount);

        // Second call (from cache)
        $result2 = $cache->remember('expensive_key', 3600, $callback);
        $this->assertEquals(1, $callCount); // Callback not called again

        $this->assertEquals($result1, $result2);
    }
}

// ═══════════════════════════════════════════════════════════════
// SESSION TESTING EXAMPLES
// ═══════════════════════════════════════════════════════════════

/**
 * Example: Testing session operations
 */
class SessionOperationsExample extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \Session::init();
    }

    public function testSessionDataPersistence(): void
    {
        $userData = [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'admin',
        ];

        \Session::put('user', $userData);

        $retrievedUser = \Session::get('user');
        $this->assertEquals($userData['id'], $retrievedUser['id']);
        $this->assertEquals($userData['role'], $retrievedUser['role']);
    }

    public function testCsrfTokenFlow(): void
    {
        $token = \Session::generateCsrfToken();
        $this->assertNotEmpty($token);

        // Token should verify
        $verified = \Session::verifyCsrfToken($token);
        $this->assertTrue($verified);

        // Invalid token should fail
        $invalidVerified = \Session::verifyCsrfToken('invalid_token');
        $this->assertFalse($invalidVerified);
    }

    public function testFlashDataPattern(): void
    {
        // Store flash message
        \Session::flash('success', 'User created successfully');

        // Retrieve once
        $message = \Session::getFlash('success');
        $this->assertEquals('User created successfully', $message);

        // Should be gone after retrieval
        $messageAgain = \Session::getFlash('success', 'default');
        $this->assertEquals('default', $messageAgain);
    }
}

// ═══════════════════════════════════════════════════════════════
// LOGGING TESTING EXAMPLES
// ═══════════════════════════════════════════════════════════════

/**
 * Example: Testing logging functionality
 */
class LoggingExample extends TestCase
{
    public function testLoggingMultipleLevels(): void
    {
        $logger = new \Logger();

        $logger->debug('Debug message', ['component' => 'auth']);
        $logger->info('Info message', ['user_id' => 1]);
        $logger->warning('Warning message', ['threshold' => 80]);
        $logger->error('Error message', ['error_code' => 500]);

        // Verify logs were written
        $recent = $logger->getRecent(4);
        $this->assertGreaterThanOrEqual(1, count($recent));
    }

    public function testExceptionLogging(): void
    {
        $logger = new \Logger();

        try {
            throw new \Exception('Test error for logging');
        } catch (\Exception $e) {
            $logger->exception($e);
        }

        $recent = $logger->getRecent(1);
        $this->assertGreaterThanOrEqual(0, count($recent));
    }
}

// ═══════════════════════════════════════════════════════════════
// INTEGRATION TEST EXAMPLES
// ═══════════════════════════════════════════════════════════════

/**
 * Example: Testing feature flow
 */
class FeatureFlowExample extends TestCase
{
    public function testUserRegistrationFlow(): void
    {
        // Create user data
        $userData = TestHelper::createFakeUser([
            'email' => TestHelper::generateRandomEmail(),
        ]);

        // Initialize session
        \Session::init();

        // Simulate registration
        $token = \Session::generateCsrfToken();
        \Session::put('registration_token', $token);

        // Verify flow
        $this->assertTrue(\Session::has('registration_token'));
        $this->assertTrue(\Session::verifyCsrfToken($token));
    }

    public function testUserLoginFlow(): void
    {
        // Generate credentials
        $email = TestHelper::generateRandomEmail();
        $password = 'SecurePass123';
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Start session
        \Session::init();

        // Verify password
        $verified = password_verify($password, $passwordHash);
        $this->assertTrue($verified);

        // Store in session
        \Session::put('authenticated', true);
        \Session::put('user_email', $email);

        $this->assertTrue(\Session::get('authenticated'));
        $this->assertEquals($email, \Session::get('user_email'));
    }
}

// ═══════════════════════════════════════════════════════════════
// ASSERTION EXAMPLES
// ═══════════════════════════════════════════════════════════════

/**
 * Reference guide for common assertions
 */
class AssertionExamplesReference extends TestCase
{
    public function testCommonAssertions(): void
    {
        $value = 'test';
        $array = ['key' => 'value'];

        // String assertions
        $this->assertStringContainsString('es', $value);
        $this->assertEquals('test', $value);

        // Array assertions
        $this->assertArrayHasKey('key', $array);
        $this->assertCount(1, $array);

        // Type assertions
        $this->assertIsString($value);
        $this->assertIsArray($array);
        $this->assertIsInt(42);
        $this->assertIsFloat(3.14);
        $this->assertTrue(true);
        $this->assertFalse(false);

        // Null assertions
        $this->assertNull(null);
        $this->assertNotNull($value);

        // Exception assertions (covered separately)
        // Custom assertions
        $this->assertFileExistsPHP($this->tempDir);
    }

    public function testComparisonAssertions(): void
    {
        $this->assertEquals(10, 10);
        $this->assertNotEquals(10, 20);
        $this->assertGreaterThan(9, 10);
        $this->assertGreaterThanOrEqual(10, 10);
        $this->assertLessThan(11, 10);
        $this->assertLessThanOrEqual(10, 10);
        $this->assertEqualsWithDelta(3.14, 3.14159, 0.01);
    }
}

// ═══════════════════════════════════════════════════════════════
// HELPER FUNCTION EXAMPLES
// ═══════════════════════════════════════════════════════════════

/**
 * Reference: Global helper functions available in tests
 */
class HelperFunctionsReference
{
    /**
     * Storage operations
     */
    public function storageHelpers(): void
    {
        $manager = storage(); // Get StorageManager
        $path = storage_path('logs'); // Get path to logs directory
        $size = storage_size(); // Get total storage size
        $sizeByType = storage_size('logs'); // Get size of specific type
        $stats = storage_stats(); // Get detailed statistics
    }

    /**
     * Cache operations
     */
    public function cacheHelpers(): void
    {
        cache_put('key', 'value', 3600); // Put in cache
        $value = cache_get('key'); // Get from cache
        cache_forget('key'); // Remove from cache
        cache_flush(); // Clear all cache
    }

    /**
     * Logging operations
     */
    public function loggingHelpers(): void
    {
        log_message('info', 'Message here', ['context' => 'data']);
        log_message('warning', 'Warning message', []);
        log_message('error', 'Error message', []);
        log_message('debug', 'Debug message', []);
    }

    /**
     * Logger and cache managers
     */
    public function managerHelpers(): void
    {
        $logger = logger(); // Get Logger instance
        $cache = cache(); // Get cache manager
    }
}
