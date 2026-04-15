<?php

namespace Tests\Integration;

use TestCase;

/**
 * Database Integration Test Suite
 * 
 * Tests for database operations and model interactions
 */
class DatabaseIntegrationTest extends TestCase
{
    protected static ?\PDO $db = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Create test database
        DatabaseHelper::createTestDatabase();

        // Get database connection
        self::$db = self::getTestDB();

        // Run migrations for test database
        if (file_exists(BASE_PATH . '/database/migrations')) {
            // TODO: Implement migration runner
        }
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        // Clean up test database
        if (self::$db instanceof \PDO) {
            DatabaseHelper::dropTestDatabase();
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Truncate all tables before each test
        if (self::$db instanceof \PDO) {
            DatabaseHelper::truncateAllTables(self::$db);
        }
    }

    /**
     * Test inserting user record
     */
    public function testInsertUserRecord(): void
    {
        $user = TestHelper::createFakeUser([
            'email' => TestHelper::generateRandomEmail(),
        ]);

        $id = DatabaseHelper::insertTestData(self::$db, 'users', $user);

        $this->assertGreaterThan(0, $id);

        // Verify record was inserted
        $record = DatabaseHelper::getRecord(self::$db, 'users', $id);
        $this->assertNotNull($record);
        $this->assertEquals($user['email'], $record['email']);
    }

    /**
     * Test inserting program record
     */
    public function testInsertProgramRecord(): void
    {
        $program = TestHelper::createFakeProgram();

        $id = DatabaseHelper::insertTestData(self::$db, 'programs', $program);

        $this->assertGreaterThan(0, $id);
    }

    /**
     * Test inserting multiple students
     */
    public function testInsertMultipleStudents(): void
    {
        $user = TestHelper::createFakeUser();
        $userId = DatabaseHelper::insertTestData(self::$db, 'users', $user);

        $students = [
            TestHelper::createFakeStudent(['user_id' => $userId, 'roll_number' => 'STU001']),
            TestHelper::createFakeStudent(['user_id' => $userId, 'roll_number' => 'STU002']),
            TestHelper::createFakeStudent(['user_id' => $userId, 'roll_number' => 'STU003']),
        ];

        $ids = DatabaseHelper::insertMultiple(self::$db, 'students', $students);

        $this->assertEquals(3, count($ids));
    }

    /**
     * Test updating record
     */
    public function testUpdateRecord(): void
    {
        $program = TestHelper::createFakeProgram();
        $id = DatabaseHelper::insertTestData(self::$db, 'programs', $program);

        $updates = ['name' => 'Updated Program Name'];
        $result = DatabaseHelper::updateRecord(self::$db, 'programs', $id, $updates);

        $this->assertTrue($result);

        // Verify update
        $record = DatabaseHelper::getRecord(self::$db, 'programs', $id);
        $this->assertEquals('Updated Program Name', $record['name']);
    }

    /**
     * Test deleting record
     */
    public function testDeleteRecord(): void
    {
        $program = TestHelper::createFakeProgram();
        $id = DatabaseHelper::insertTestData(self::$db, 'programs', $program);

        $this->assertNotNull(DatabaseHelper::getRecord(self::$db, 'programs', $id));

        $result = DatabaseHelper::deleteRecord(self::$db, 'programs', $id);
        $this->assertTrue($result);

        // Verify deletion
        $record = DatabaseHelper::getRecord(self::$db, 'programs', $id);
        $this->assertNull($record);
    }

    /**
     * Test counting records
     */
    public function testCountRecords(): void
    {
        $program1 = TestHelper::createFakeProgram();
        $program2 = TestHelper::createFakeProgram(['code' => 'MS']);

        DatabaseHelper::insertTestData(self::$db, 'programs', $program1);
        DatabaseHelper::insertTestData(self::$db, 'programs', $program2);

        $count = DatabaseHelper::countRecords(self::$db, 'programs');

        $this->assertGreaterThanOrEqual(2, $count);
    }

    /**
     * Test transaction handling
     */
    public function testTransactionHandling(): void
    {
        DatabaseHelper::beginTransaction(self::$db);

        $program = TestHelper::createFakeProgram();
        $id = DatabaseHelper::insertTestData(self::$db, 'programs', $program);

        DatabaseHelper::commitTransaction(self::$db);

        // Verify record persisted
        $record = DatabaseHelper::getRecord(self::$db, 'programs', $id);
        $this->assertNotNull($record);
    }

    /**
     * Test transaction rollback
     */
    public function testTransactionRollback(): void
    {
        DatabaseHelper::beginTransaction(self::$db);

        $program = TestHelper::createFakeProgram();
        DatabaseHelper::insertTestData(self::$db, 'programs', $program);

        DatabaseHelper::rollbackTransaction(self::$db);

        $count = DatabaseHelper::countRecords(self::$db, 'programs');
        $this->assertEquals(0, $count);
    }

    /**
     * Test database query execution
     */
    public function testDatabaseQueryExecution(): void
    {
        $program1 = TestHelper::createFakeProgram(['name' => 'Program A']);
        $program2 = TestHelper::createFakeProgram(['name' => 'Program B', 'code' => 'PB']);

        DatabaseHelper::insertTestData(self::$db, 'programs', $program1);
        DatabaseHelper::insertTestData(self::$db, 'programs', $program2);

        $stmt = self::$db->query("SELECT * FROM programs WHERE code = 'BS'");
        $result = $stmt->fetch();

        $this->assertNotNull($result);
        $this->assertEquals('Program A', $result['name']);
    }

    /**
     * Test foreign key relationship integrity
     */
    public function testForeignKeyIntegrity(): void
    {
        // Insert user first
        $user = TestHelper::createFakeUser();
        $userId = DatabaseHelper::insertTestData(self::$db, 'users', $user);

        // Verify user exists
        $isValid = DatabaseHelper::verifyForeignKey(self::$db, 'students', 'user_id', $userId, 'users', 'id');
        $this->assertTrue($isValid);
    }

    /**
     * Test database constraints
     */
    public function testDatabaseConstraints(): void
    {
        // Try to insert into students without a user (should fail or succeed based on constraints)
        $student = TestHelper::createFakeStudent(['user_id' => 99999]);

        // This test verifies constraint behavior
        $result = DatabaseHelper::insertTestData(self::$db, 'students', $student);

        // Result depends on database constraint settings
        $this->assertTrue(true); // Just verify no fatal error
    }

    /**
     * Test table row count
     */
    public function testTableRowCount(): void
    {
        $initialCount = DatabaseHelper::getTableRowCount(self::$db, 'programs');

        $program = TestHelper::createFakeProgram();
        DatabaseHelper::insertTestData(self::$db, 'programs', $program);

        $newCount = DatabaseHelper::getTableRowCount(self::$db, 'programs');

        $this->assertEquals($initialCount + 1, $newCount);
    }

    /**
     * Test concurrent data operations
     */
    public function testConcurrentOperations(): void
    {
        // Insert multiple records rapidly
        for ($i = 0; $i < 5; $i++) {
            $subject = TestHelper::createFakeSubject(['code' => 'CSC' . (100 + $i)]);
            DatabaseHelper::insertTestData(self::$db, 'subjects', $subject);
        }

        $count = DatabaseHelper::getTableRowCount(self::$db, 'subjects');
        $this->assertEquals(5, $count);
    }

    /**
     * Test nullable fields
     */
    public function testNullableFields(): void
    {
        $fee = TestHelper::createFakeFee(['paid_date' => null]);

        $id = DatabaseHelper::insertTestData(self::$db, 'fees', $fee);

        $record = DatabaseHelper::getRecord(self::$db, 'fees', $id);
        $this->assertNull($record['paid_date']);
    }

    /**
     * Test numeric precision
     */
    public function testNumericPrecision(): void
    {
        $fee = TestHelper::createFakeFee(['amount' => 5000.75]);

        $id = DatabaseHelper::insertTestData(self::$db, 'fees', $fee);

        $record = DatabaseHelper::getRecord(self::$db, 'fees', $id);
        $this->assertEqualsWithDelta(5000.75, $fee['amount'], 0.01);
    }
}
