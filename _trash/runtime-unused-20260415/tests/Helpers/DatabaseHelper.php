<?php

/**
 * Database Test Helper
 * 
 * Utilities for database operations in tests
 */

class DatabaseHelper
{
    /**
     * Create test database if it doesn't exist
     */
    public static function createTestDatabase(): bool
    {
        try {
            $pdo = new \PDO(
                'mysql:host=' . ($_ENV['DB_HOST'] ?? 'localhost'),
                $_ENV['DB_USERNAME'] ?? 'root',
                $_ENV['DB_PASSWORD'] ?? '',
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
            
            $testDb = ($_ENV['DB_DATABASE_TEST'] ?? $_ENV['DB_DATABASE'] . '_test');
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$testDb` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            return true;
        } catch (\Exception $e) {
            echo "Error creating test database: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Drop test database
     */
    public static function dropTestDatabase(): bool
    {
        try {
            $pdo = new \PDO(
                'mysql:host=' . ($_ENV['DB_HOST'] ?? 'localhost'),
                $_ENV['DB_USERNAME'] ?? 'root',
                $_ENV['DB_PASSWORD'] ?? '',
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
            
            $testDb = ($_ENV['DB_DATABASE_TEST'] ?? $_ENV['DB_DATABASE'] . '_test');
            $pdo->exec("DROP DATABASE IF EXISTS `$testDb`");
            
            return true;
        } catch (\Exception $e) {
            echo "Error dropping test database: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Truncate all tables in test database
     */
    public static function truncateAllTables(\PDO $pdo): void
    {
        try {
            $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
            
            $tables = $pdo->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE()")
                ->fetchAll(\PDO::FETCH_COLUMN);
            
            foreach ($tables as $table) {
                $pdo->exec("TRUNCATE TABLE `$table`");
            }
            
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
        } catch (\Exception $e) {
            echo "Error truncating tables: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Truncate specific table
     */
    public static function truncateTable(\PDO $pdo, string $table): void
    {
        try {
            $pdo->exec("TRUNCATE TABLE `$table`");
        } catch (\Exception $e) {
            echo "Error truncating table $table: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Insert test data into table
     */
    public static function insertTestData(\PDO $pdo, string $table, array $data): int
    {
        try {
            $columns = array_keys($data);
            $placeholders = array_fill(0, count($columns), '?');
            
            $sql = "INSERT INTO `$table` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_values($data));
            
            return $pdo->lastInsertId();
        } catch (\Exception $e) {
            echo "Error inserting test data: " . $e->getMessage() . "\n";
            return 0;
        }
    }

    /**
     * Insert multiple test records
     */
    public static function insertMultiple(\PDO $pdo, string $table, array $records): array
    {
        $ids = [];
        foreach ($records as $record) {
            $id = self::insertTestData($pdo, $table, $record);
            if ($id) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    /**
     * Get record from table
     */
    public static function getRecord(\PDO $pdo, string $table, int $id): ?array
    {
        try {
            $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            echo "Error fetching record: " . $e->getMessage() . "\n";
            return null;
        }
    }

    /**
     * Count records in table
     */
    public static function countRecords(\PDO $pdo, string $table, ?string $where = null): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM `$table`";
            if ($where) {
                $sql .= " WHERE $where";
            }
            return (int) $pdo->query($sql)->fetchColumn();
        } catch (\Exception $e) {
            echo "Error counting records: " . $e->getMessage() . "\n";
            return 0;
        }
    }

    /**
     * Update record
     */
    public static function updateRecord(\PDO $pdo, string $table, int $id, array $data): bool
    {
        try {
            $columns = array_keys($data);
            $setSql = implode(', ', array_map(fn($col) => "`$col` = ?", $columns));
            
            $sql = "UPDATE `$table` SET $setSql WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            
            return $stmt->execute(array_merge(array_values($data), [$id]));
        } catch (\Exception $e) {
            echo "Error updating record: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Delete record
     */
    public static function deleteRecord(\PDO $pdo, string $table, int $id): bool
    {
        try {
            $stmt = $pdo->prepare("DELETE FROM `$table` WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (\Exception $e) {
            echo "Error deleting record: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Get table row count
     */
    public static function getTableRowCount(\PDO $pdo, string $table): int
    {
        try {
            return (int) $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Verify foreign key constraint
     */
    public static function verifyForeignKey(\PDO $pdo, string $table, string $column, int $value, string $refTable, string $refColumn = 'id'): bool
    {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM `$refTable` WHERE `$refColumn` = ?");
            $stmt->execute([$value]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Begin transaction
     */
    public static function beginTransaction(\PDO $pdo): void
    {
        try {
            $pdo->beginTransaction();
        } catch (\Exception $e) {
            echo "Error beginning transaction: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Commit transaction
     */
    public static function commitTransaction(\PDO $pdo): void
    {
        try {
            $pdo->commit();
        } catch (\Exception $e) {
            echo "Error committing transaction: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Rollback transaction
     */
    public static function rollbackTransaction(\PDO $pdo): void
    {
        try {
            $pdo->rollBack();
        } catch (\Exception $e) {
            echo "Error rolling back transaction: " . $e->getMessage() . "\n";
        }
    }
}
