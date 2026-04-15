<?php

/**
 * Test Helper Utilities
 * 
 * Common utility functions for testing
 */

class TestHelper
{
    /**
     * Create a fake user array
     */
    public static function createFakeUser(array $overrides = []): array
    {
        $defaults = [
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.test',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
            'role' => 'student',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        return array_merge($defaults, $overrides);
    }

    /**
     * Create a fake student array
     */
    public static function createFakeStudent(array $overrides = []): array
    {
        $defaults = [
            'id' => 1,
            'user_id' => 1,
            'roll_number' => 'STU001',
            'batch' => 2026,
            'program_id' => 1,
            'semester_id' => 1,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        return array_merge($defaults, $overrides);
    }

    /**
     * Create a fake program array
     */
    public static function createFakeProgram(array $overrides = []): array
    {
        $defaults = [
            'id' => 1,
            'name' => 'Bachelor of Science',
            'code' => 'BS',
            'duration_years' => 4,
            'description' => 'Test program',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        return array_merge($defaults, $overrides);
    }

    /**
     * Create a fake subject array
     */
    public static function createFakeSubject(array $overrides = []): array
    {
        $defaults = [
            'id' => 1,
            'code' => 'CSC101',
            'name' => 'Introduction to Programming',
            'credits' => 3,
            'program_id' => 1,
            'semester_id' => 1,
            'teacher_id' => 1,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        return array_merge($defaults, $overrides);
    }

    /**
     * Create a fake attendance record
     */
    public static function createFakeAttendance(array $overrides = []): array
    {
        $defaults = [
            'id' => 1,
            'student_id' => 1,
            'subject_id' => 1,
            'date' => date('Y-m-d'),
            'status' => 'present',
            'remarks' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        return array_merge($defaults, $overrides);
    }

    /**
     * Create a fake fee record
     */
    public static function createFakeFee(array $overrides = []): array
    {
        $defaults = [
            'id' => 1,
            'student_id' => 1,
            'semester_id' => 1,
            'amount' => 50000.00,
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'pending',
            'paid_amount' => 0.00,
            'paid_date' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        return array_merge($defaults, $overrides);
    }

    /**
     * Create a fake file upload array (simulating $_FILES)
     */
    public static function createFakeFileUpload(
        string $filename = 'test.txt',
        string $type = 'text/plain',
        int $size = 1024
    ): array {
        return [
            'name' => $filename,
            'type' => $type,
            'size' => $size,
            'tmp_name' => '/tmp/php_' . uniqid(),
            'error' => UPLOAD_ERR_OK,
        ];
    }

    /**
     * Assert that two dates are equal (comparing only date part)
     */
    public static function assertDatesEqual(\DateTime $expected, \DateTime $actual, string $message = ''): bool
    {
        $expectedDate = $expected->format('Y-m-d');
        $actualDate = $actual->format('Y-m-d');
        
        return $expectedDate === $actualDate;
    }

    /**
     * Generate a random email for testing
     */
    public static function generateRandomEmail(): string
    {
        return 'test_' . uniqid() . '@example.test';
    }

    /**
     * Generate a random phone number for testing
     */
    public static function generateRandomPhone(): string
    {
        return '555' . str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT);
    }

    /**
     * Create a request simulation array
     */
    public static function createFakeRequest(
        string $method = 'GET',
        array $data = [],
        array $headers = []
    ): array {
        return [
            'method' => $method,
            'data' => $data,
            'headers' => array_merge([
                'Content-Type' => 'application/json',
                'User-Agent' => 'PHPUnit/Test',
            ], $headers),
        ];
    }

    /**
     * Wait for a condition to be true (with timeout)
     */
    public static function waitFor(callable $condition, int $timeoutMs = 5000, int $intervalMs = 100): bool
    {
        $elapsed = 0;
        while ($elapsed < $timeoutMs) {
            if ($condition()) {
                return true;
            }
            usleep($intervalMs * 1000);
            $elapsed += $intervalMs;
        }
        return false;
    }
}
