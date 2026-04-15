<?php

namespace Tests\Unit\Core;

use TestCase;

/**
 * Session Test Suite
 * 
 * Tests for the Session class functionality
 */
class SessionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Reset session for each test
        $_SESSION = [];
    }

    /**
     * Test storing a value in session
     */
    public function testSessionPut(): void
    {
        \Session::init();
        \Session::put('user_id', 123);

        $this->assertTrue(\Session::has('user_id'));
    }

    /**
     * Test retrieving session value
     */
    public function testSessionGet(): void
    {
        \Session::init();
        \Session::put('username', 'testuser');

        $value = \Session::get('username');
        $this->assertEquals('testuser', $value);
    }

    /**
     * Test get with default value
     */
    public function testSessionGetWithDefault(): void
    {
        \Session::init();

        $value = \Session::get('non_existent', 'default_value');
        $this->assertEquals('default_value', $value);
    }

    /**
     * Test has() returns true for existing key
     */
    public function testSessionHas(): void
    {
        \Session::init();
        \Session::put('key', 'value');

        $this->assertTrue(\Session::has('key'));
    }

    /**
     * Test has() returns false for non-existing key
     */
    public function testSessionHasNot(): void
    {
        \Session::init();

        $this->assertFalse(\Session::has('non_existent'));
    }

    /**
     * Test pull() retrieves and removes value
     */
    public function testSessionPull(): void
    {
        \Session::init();
        \Session::put('temp_key', 'temp_value');

        $value = \Session::pull('temp_key');
        $this->assertEquals('temp_value', $value);
        $this->assertFalse(\Session::has('temp_key'));
    }

    /**
     * Test forget() removes a key
     */
    public function testSessionForget(): void
    {
        \Session::init();
        \Session::put('key_to_forget', 'value');

        $this->assertTrue(\Session::has('key_to_forget'));

        \Session::forget('key_to_forget');

        $this->assertFalse(\Session::has('key_to_forget'));
    }

    /**
     * Test flash data stores for one retrieval
     */
    public function testSessionFlash(): void
    {
        \Session::init();
        \Session::flash('success', 'Operation successful');

        // First retrieval should get the value
        $value = \Session::getFlash('success');
        $this->assertEquals('Operation successful', $value);

        // Second retrieval should return default (data was removed)
        $value = \Session::getFlash('success', 'default');
        $this->assertEquals('default', $value);
    }

    /**
     * Test CSRF token generation
     */
    public function testCsrfTokenGeneration(): void
    {
        \Session::init();

        $token = \Session::generateCsrfToken();

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        $this->assertGreaterThan(20, strlen($token)); // Tokens are typically 32+ chars
    }

    /**
     * Test CSRF token verification
     */
    public function testCsrfTokenVerification(): void
    {
        \Session::init();

        $token = \Session::generateCsrfToken();

        // Verify the same token
        $this->assertTrue(\Session::verifyCsrfToken($token));

        // Invalid token should fail
        $this->assertFalse(\Session::verifyCsrfToken('invalid_token'));
    }

    /**
     * Test session regeneration
     */
    public function testSessionRegenerate(): void
    {
        \Session::init();
        \Session::put('user_id', 1);

        $oldToken = \Session::generateCsrfToken();

        \Session::regenerate();

        // Data should still exist after regeneration
        $this->assertEquals(1, \Session::get('user_id'));

        // But CSRF token should be different (or regenerated)
        $newToken = \Session::generateCsrfToken();
        $this->assertNotEquals($oldToken, $newToken);
    }

    /**
     * Test storing user data
     */
    public function testStoringUserData(): void
    {
        \Session::init();

        $userData = [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'admin',
        ];

        \Session::put('user', $userData);

        $retrieved = \Session::get('user');
        $this->assertEquals($userData, $retrieved);
    }

    /**
     * Test storing multiple values
     */
    public function testStoringMultipleValues(): void
    {
        \Session::init();

        \Session::put('key1', 'value1');
        \Session::put('key2', 'value2');
        \Session::put('key3', 'value3');

        $this->assertEquals('value1', \Session::get('key1'));
        $this->assertEquals('value2', \Session::get('key2'));
        $this->assertEquals('value3', \Session::get('key3'));
    }

    /**
     * Test flash data with multiple values
     */
    public function testMultipleFlashData(): void
    {
        \Session::init();

        \Session::flash('success', 'Success message');
        \Session::flash('error', 'Error message');

        $success = \Session::getFlash('success');
        $error = \Session::getFlash('error');

        $this->assertEquals('Success message', $success);
        $this->assertEquals('Error message', $error);
    }

    /**
     * Test session directory exists
     */
    public function testSessionDirectoryExists(): void
    {
        $sessionDir = BASE_PATH . '/storage/sessions';
        $this->assertDirectoryExistsPHP($sessionDir);
    }

    /**
     * Test pull with default value
     */
    public function testPullWithDefault(): void
    {
        \Session::init();

        $value = \Session::pull('non_existent', 'default');
        $this->assertEquals('default', $value);
    }
}
