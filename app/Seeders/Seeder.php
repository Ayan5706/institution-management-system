<?php

namespace App\Seeders;

use PDO;

abstract class Seeder
{
    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Execute the seeder to populate the database with data.
     *
     * @return void
     */
    abstract public function run(): void;

    /**
     * Insert a row into a table.
     *
     * @param string $table The table name
     * @param array $data The data to insert
     * @return void
     */
    protected function insert(string $table, array $data): void
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array_values($data));
        } catch (\PDOException $e) {
            throw new \Exception("insert into {$table} failed: {$e->getMessage()}");
        }
    }

    /**
     * Insert multiple rows into a table.
     *
     * @param string $table The table name
     * @param array $rows Array of row data arrays
     * @return void
     */
    protected function insertMany(string $table, array $rows): void
    {
        foreach ($rows as $row) {
            $this->insert($table, $row);
        }
    }

    /**
     * Truncate a table (clear all data).
     *
     * @param string $table The table name
     * @return void
     */
    protected function truncate(string $table): void
    {
        try {
            $this->pdo->exec("TRUNCATE TABLE {$table}");
        } catch (\PDOException $e) {
            throw new \Exception("truncate {$table} failed: {$e->getMessage()}");
        }
    }

    /**
     * Execute raw SQL.
     *
     * @param string $sql The SQL statement
     * @param array $params Optional parameters for prepared statements
     * @return void
     */
    protected function execute(string $sql, array $params = []): void
    {
        try {
            if (empty($params)) {
                $this->pdo->exec($sql);
            } else {
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
            }
        } catch (\PDOException $e) {
            throw new \Exception("execute failed: {$e->getMessage()}");
        }
    }

    /**
     * Get a count of rows in a table.
     *
     * @param string $table The table name
     * @return int The row count
     */
    protected function count(string $table): int
    {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM {$table}");
            return $stmt->fetchColumn();
        } catch (\PDOException $e) {
            throw new \Exception("count {$table} failed: {$e->getMessage()}");
        }
    }
}
