# Testing Documentation for IMS

## Overview

This directory contains comprehensive test suites for the Institution Management System (IMS). The testing infrastructure includes unit tests, integration tests, and testing utilities.

## Directory Structure

```
tests/
├── bootstrap.php              # Test environment initialization
├── TestCase.php               # Base test class for all tests
├── run-tests.php              # Test runner script
├── Helpers/
│   ├── TestHelper.php         # Data generation for tests
│   ├── DatabaseHelper.php     # Database operations for tests
│   └── MockHelper.php         # Mock objects for testing
├── unit/
│   ├── Core/
│   │   ├── LoggerTest.php         # Logger class tests
│   │   ├── CacheTest.php          # Cache class tests
│   │   ├── SessionTest.php        # Session class tests
│   │   └── StorageManagerTest.php # StorageManager class tests
│   ├── Models/
│   │   └── ModelTest.php          # Model functionality tests
│   └── Helpers/
│       └── HelperFunctionsTest.php # Global helper functions tests
└── integration/
    ├── DatabaseIntegrationTest.php    # Database operations
    ├── ControllerIntegrationTest.php  # Controller functionality
    └── AuthenticationIntegrationTest.php # Auth flows and security
```

## Running Tests

### All Tests
```bash
php tests/run-tests.php
```

### Unit Tests Only
```bash
php tests/run-tests.php --unit
```

### Integration Tests Only
```bash
php tests/run-tests.php --integration
```

### Specific Test Class
```bash
php tests/run-tests.php --filter=CacheTest
```

### With Coverage Report
```bash
php tests/run-tests.php --coverage
```

### Combined Options
```bash
php tests/run-tests.php --unit --filter=LoggerTest
php tests/run-tests.php --integration --coverage
```

## Test Suites

### Unit Tests

#### LoggerTest (tests/unit/Core/LoggerTest.php)
Tests for the Logger class with multi-level logging capabilities:
- **Info logging**: `testInfoLogging()`
- **Warning logging**: `testWarningLogging()`
- **Error logging**: `testErrorLogging()`
- **Debug logging**: `testDebugLogging()`
- **Exception logging**: `testExceptionLogging()`
- **Context formatting**: `testContextFormatting()`
- **Log retrieval**: `testGetRecentLogs()`
- **Log accumulation**: `testMultipleLogsAccumulate()`

**Total Tests**: 9

#### CacheTest (tests/unit/Core/CacheTest.php)
Tests for the Cache class with TTL support:
- **Cache put**: `testCachePut()`
- **Cache get**: `testCacheGet()`
- **Non-existent keys**: `testGetNonExistentKey()`
- **Key existence check**: `testHasExistingKey()`, `testHasNonExistingKey()`
- **Forget operation**: `testForgetKey()`
- **Flush all**: `testFlushCache()`
- **Remember pattern**: `testRememberPattern()`
- **TTL expiration**: `testTTLExpiration()`
- **Data types**: `testCachingDataTypes()`
- **Statistics**: `testCacheStats()`

**Total Tests**: 12

#### SessionTest (tests/unit/Core/SessionTest.php)
Tests for the Session class with CSRF protection:
- **Session put**: `testSessionPut()`
- **Session get**: `testSessionGet()`
- **Default values**: `testSessionGetWithDefault()`
- **Has check**: `testSessionHas()`, `testSessionHasNot()`
- **Pull operation**: `testSessionPull()`
- **Forget operation**: `testSessionForget()`
- **Flash data**: `testSessionFlash()`
- **CSRF tokens**: `testCsrfTokenGeneration()`, `testCsrfTokenVerification()`
- **Session regeneration**: `testSessionRegenerate()`
- **User data**: `testStoringUserData()`

**Total Tests**: 13

#### StorageManagerTest (tests/unit/Core/StorageManagerTest.php)
Tests for the StorageManager class:
- **Path retrieval**: `testGetStoragePath()`, `testGetAllPaths()`
- **Path existence**: `testStoragePathsExist()`
- **Size calculation**: `testGetStorageSize()`, `testGetSizeByType()`
- **File counting**: `testCountFilesByType()`
- **Statistics**: `testGetStatistics()`
- **Cleanup**: `testCleanupMethod()`
- **Permissions**: `testFixPermissions()`
- **Directory management**: `testEnsureDirectoryExists()`

**Total Tests**: 15

#### ModelTest (tests/unit/Models/ModelTest.php)
Tests for Model base class and specific models:
- **Model instantiation**: `testModelInstantiation()`
- **Attributes**: `testModelSetAttributes()`
- **Fill method**: `testModelFill()`
- **Specific models**: User, Student, Program, Subject, Teacher, etc.
- **Data conversion**: `testModelToArray()`
- **Timestamps**: `testModelTimestamps()`

**Total Tests**: 15

#### HelperFunctionsTest (tests/unit/Helpers/HelperFunctionsTest.php)
Tests for global helper functions:
- **Storage helper**: `testStorageHelper()`
- **Logging helpers**: `testLogMessageHelper()`, `testLogMessageLevels()`
- **Cache helpers**: `testCacheGetHelper()`, `testCachePutHelper()`, etc.
- **Storage path helpers**: `testStoragePathHelper()`, `testStoragePathHelper()`
- **Cache sequences**: `testCacheHelperSequence()`

**Total Tests**: 13

### Integration Tests

#### DatabaseIntegrationTest (tests/integration/DatabaseIntegrationTest.php)
Tests for database operations and model interactions:
- **Record insertion**: `testInsertUserRecord()`, `testInsertProgramRecord()`
- **Multiple inserts**: `testInsertMultipleStudents()`
- **Updates**: `testUpdateRecord()`
- **Deletions**: `testDeleteRecord()`
- **Row counting**: `testCountRecords()`, `testTableRowCount()`
- **Transactions**: `testTransactionHandling()`, `testTransactionRollback()`
- **Queries**: `testDatabaseQueryExecution()`
- **Constraints**: `testForeignKeyIntegrity()`, `testDatabaseConstraints()`
- **Data types**: `testNullableFields()`, `testNumericPrecision()`

**Total Tests**: 15

#### ControllerIntegrationTest (tests/integration/ControllerIntegrationTest.php)
Tests for controller functionality:
- **Controller instantiation**: All 10 controller tests
- **Method existence**: `testControllerMethodExists()`
- **Model interaction**: `testControllerModelInteraction()`
- **Request/Response**: `testRequestResponseMocking()`, `testControllerWithMockedRequest()`
- **Session usage**: `testSessionInController()`
- **Logging**: `testLoggingInController()`
- **Caching**: `testCachingControllerResponse()`
- **Security**: `testCsrfTokenInController()`, `testControllerErrorHandling()`
- **Business logic**: `testPaginationLogic()`, `testFilteringLogic()`, `testSortingLogic()`
- **Validation**: `testValidationLogic()`

**Total Tests**: 20

#### AuthenticationIntegrationTest (tests/integration/AuthenticationIntegrationTest.php)
Tests for authentication flows and security:
- **CSRF**: `testCsrfTokenGeneration()`, `testCsrfTokenVerification()`
- **Passwords**: `testPasswordHashing()`, `testSecurePasswordRequirements()`
- **Sessions**: `testSessionPersistenceLogin()`, `testLogoutSequence()`
- **Roles**: `testRoleBasedAccess()`, `testPermissionCheck()`, `testUserRoleTransition()`
- **Status checks**: `testUserStatusCheck()`
- **Expiration**: `testSessionExpirationLogic()`
- **2FA**: `testTwoFactorAuthFlow()`
- **Attempts**: `testLoginAttemptTracking()`, `testAccountLockout()`
- **Logging**: `testAuthenticationLogging()`
- **Token management**: `testRememberMeFunctionality()`, `testSessionHijackingProtection()`

**Total Tests**: 17

## Test Helpers

### TestHelper
Provides factory methods for creating test data:

```php
// Create test users
$user = TestHelper::createFakeUser(['email' => 'custom@example.com']);

// Create test models
$student = TestHelper::createFakeStudent();
$program = TestHelper::createFakeProgram();
$subject = TestHelper::createFakeSubject();

// Utility methods
TestHelper::generateRandomEmail();
TestHelper::generateRandomPhone();
TestHelper::createFakeFileUpload('file.pdf', 'application/pdf');
TestHelper::createFakeRequest('POST', ['data' => 'value']);
```

### DatabaseHelper
Provides utilities for database testing:

```php
// Database management
DatabaseHelper::createTestDatabase();
DatabaseHelper::dropTestDatabase();
DatabaseHelper::truncateAllTables($pdo);

// CRUD operations
$id = DatabaseHelper::insertTestData($pdo, 'users', $userData);
DatabaseHelper::updateRecord($pdo, 'users', $id, ['name' => 'New Name']);
DatabaseHelper::deleteRecord($pdo, 'users', $id);

// Queries and counts
$record = DatabaseHelper::getRecord($pdo, 'users', $id);
$count = DatabaseHelper::countRecords($pdo, 'users');
$isValid = DatabaseHelper::verifyForeignKey($pdo, 'students', 'user_id', $userId, 'users');

// Transactions
DatabaseHelper::beginTransaction($pdo);
DatabaseHelper::commitTransaction($pdo);
DatabaseHelper::rollbackTransaction($pdo);
```

### MockHelper
Creates mock objects for testing:

```php
// Create mocks
$logger = MockHelper::createMockLogger();
$cache = MockHelper::createMockCache();
$session = MockHelper::createMockSession();
$request = MockHelper::createMockRequest(['field' => 'value'], 'POST');
$response = MockHelper::createMockResponse();
$db = MockHelper::createMockDatabase();
```

## Base TestCase Class

All tests extend `TestCase` which provides utilities:

```php
// File operations
$path = $this->createTempFile('test.txt', 'content');
$dir = $this->createTempDir('test_dir');

// Assertions
$this->assertFileExistsPHP($file);
$this->assertDirectoryExistsPHP($dir);
$this->assertFileContains($file, 'text');
$this->assertArrayHasKeys(['key1', 'key2'], $array);
$this->assertResponseStatusCode(200, $actual);

// Database access
$db = self::getTestDB();

// Mock creation
$mock = $this->createMockObject(ClassName::class, ['method1', 'method2']);
```

## Configuration

### phpunit.xml
Main PHPUnit configuration:
- Bootstrap file: `tests/bootstrap.php`
- Test suites: Unit and Integration
- Coverage reports: HTML to `storage/reports/coverage`
- Error handling: Strict mode enabled

### tests/bootstrap.php
Test environment setup:
- Loads application autoloader
- Initializes bootstrap configuration
- Sets test database configuration
- Loads test utilities

## Best Practices

### 1. Test Naming
- Method names clearly describe what is being tested
- Use descriptive names: `testUserCanLoginWithValidCredentials()`
- Group related tests in test classes

### 2. Test Structure (Arrange-Act-Assert)
```php
public function testSomething(): void
{
    // Arrange: Set up test data
    $user = TestHelper::createFakeUser();
    
    // Act: Perform action
    $result = $user->authenticate('password');
    
    // Assert: Verify result
    $this->assertTrue($result);
}
```

### 3. Test Isolation
- Each test is independent
- Use `setUp()` to initialize test state
- Use `tearDown()` to clean up resources
- Avoid test interdependencies

### 4. Mocking External Dependencies
```php
$mockDb = MockHelper::createMockDatabase();
$mockCache = MockHelper::createMockCache();
// Use mocks instead of real implementations
```

### 5. Data Cleanup
- Temporary files are automatically cleaned up
- Database transactions are rolled back
- Session data is reset between tests

## Coverage Report

Generate and view coverage reports:

```bash
php tests/run-tests.php --coverage
```

Coverage reports are generated to `storage/reports/coverage/index.html`

Key coverage metrics:
- Line coverage: Percentage of executed lines
- Method coverage: Percentage of executed methods
- Class coverage: Percentage of fully tested classes

## Continuous Integration

Tests can be run in CI/CD pipelines:

```bash
# In GitHub Actions, GitLab CI, etc.
php tests/run-tests.php

# Exit codes:
#   0 = All tests passed
#   1 = Some tests failed
#   2 = Invalid configuration
```

## Troubleshooting

### Tests fail with "Class not found"
- Ensure autoloader is properly configured
- Check that `bootstrap/app.php` is loading all classes
- Run `composer dump-autoload`

### Database tests fail
- Verify test database configuration in `.env`
- Check database user has CREATE/DROP permissions
- Ensure `DB_DATABASE_TEST` is configured

### Timeout errors
- Increase timeout in `phpunit.xml`
- Check for infinite loops in code
- Verify mocks are properly returning values

### Permission denied in temp directory
- Check that `storage/temp` is writable
- Ensure PHP process has write permissions
- Verify disk space is available

## Adding New Tests

### 1. Create test file in appropriate directory
```php
// tests/unit/YourComponent/YourTest.php
namespace Tests\Unit\YourComponent;

use TestCase;

class YourTest extends TestCase
{
    public function testSomething(): void
    {
        // Your test here
    }
}
```

### 2. Run the test
```bash
php tests/run-tests.php --filter=YourTest
```

### 3. Verify it passes
```
OK (1 test, 0 assertions)
```

## Maintenance

### Regular tasks
- Run full test suite after major changes
- Generate coverage reports monthly
- Review and update failing tests
- Maintain helper methods and utilities
- Keep test data realistic and current

### Performance optimization
- Use mocks instead of real implementations where possible
- Avoid sleep() and wait() in tests
- Batch database operations in integration tests
- Use in-memory caching during tests

## Summary

The IMS testing infrastructure provides:
- **65+ unit tests** covering core functionality
- **50+ integration tests** verifying system interactions
- **Comprehensive helpers** for easy test data generation
- **Mock utilities** for isolating components
- **CI/CD ready** configuration
- **Coverage reporting** for quality metrics

Total: **115+ tests** with extensive coverage of core functionality, models, controllers, and authentication flows.
