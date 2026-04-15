<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use RuntimeException;

abstract class BaseModel
{
    protected string $table;
    protected string $primaryKey = 'id';

    /** @var array<int, string> */
    protected array $fillable = [];

    protected function db(): PDO
    {
        return Database::connection();
    }

    /** @return array<int, array<string, mixed>> */
    public function all(string $orderBy = 'id', string $direction = 'DESC'): array
    {
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        $orderBy = $this->sanitizeIdentifier($orderBy);

        $sql = sprintf('SELECT * FROM `%s` ORDER BY `%s` %s', $this->table, $orderBy, $direction);
        $stmt = $this->db()->query($sql);

        return $stmt->fetchAll() ?: [];
    }

    /** @return array<string, mixed>|null */
    public function find(int|string $id): ?array
    {
        $sql = sprintf('SELECT * FROM `%s` WHERE `%s` = :id LIMIT 1', $this->table, $this->primaryKey);
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    /** @return array<int, array<string, mixed>> */
    public function where(string $column, mixed $value, string $operator = '='): array
    {
        $column = $this->sanitizeIdentifier($column);
        $operator = $this->sanitizeOperator($operator);

        $sql = sprintf('SELECT * FROM `%s` WHERE `%s` %s :value', $this->table, $column, $operator);
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['value' => $value]);

        return $stmt->fetchAll() ?: [];
    }

    /** @return array<string, mixed>|null */
    public function firstWhere(string $column, mixed $value, string $operator = '='): ?array
    {
        $column = $this->sanitizeIdentifier($column);
        $operator = $this->sanitizeOperator($operator);

        $sql = sprintf('SELECT * FROM `%s` WHERE `%s` %s :value LIMIT 1', $this->table, $column, $operator);
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['value' => $value]);

        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function create(array $attributes): int
    {
        $data = $this->filterFillable($attributes);

        if ($data === []) {
            throw new RuntimeException('No fillable data provided for insert on table ' . $this->table);
        }

        $columns = array_keys($data);
        $columnSql = '`' . implode('`, `', $columns) . '`';
        $valueSql = ':' . implode(', :', $columns);

        $sql = sprintf('INSERT INTO `%s` (%s) VALUES (%s)', $this->table, $columnSql, $valueSql);
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($data);

        return (int) $this->db()->lastInsertId();
    }

    public function updateById(int|string $id, array $attributes): bool
    {
        $data = $this->filterFillable($attributes);

        if ($data === []) {
            return false;
        }

        $sets = [];

        foreach (array_keys($data) as $column) {
            $sets[] = sprintf('`%s` = :%s', $column, $column);
        }

        $sql = sprintf(
            'UPDATE `%s` SET %s WHERE `%s` = :_id',
            $this->table,
            implode(', ', $sets),
            $this->primaryKey
        );

        $stmt = $this->db()->prepare($sql);
        $data['_id'] = $id;

        return $stmt->execute($data);
    }

    public function deleteById(int|string $id): bool
    {
        $sql = sprintf('DELETE FROM `%s` WHERE `%s` = :id', $this->table, $this->primaryKey);
        $stmt = $this->db()->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }

    /** @return array{data: array<int, array<string, mixed>>, total: int, page: int, per_page: int, total_pages: int} */
    public function paginate(int $page = 1, int $perPage = 15, string $orderBy = 'id', string $direction = 'DESC'): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        $orderBy = $this->sanitizeIdentifier($orderBy);

        $total = (int) $this->db()->query(sprintf('SELECT COUNT(*) FROM `%s`', $this->table))->fetchColumn();

        $sql = sprintf('SELECT * FROM `%s` ORDER BY `%s` %s LIMIT :limit OFFSET :offset', $this->table, $orderBy, $direction);
        $stmt = $this->db()->prepare($sql);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetchAll() ?: [];
        $totalPages = (int) ceil($total / $perPage);

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => max(1, $totalPages),
        ];
    }

    protected function filterFillable(array $attributes): array
    {
        if ($this->fillable === []) {
            return [];
        }

        $result = [];

        foreach ($this->fillable as $column) {
            if (array_key_exists($column, $attributes)) {
                $result[$column] = $attributes[$column];
            }
        }

        return $result;
    }

    protected function sanitizeIdentifier(string $identifier): string
    {
        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier) !== 1) {
            throw new RuntimeException('Invalid SQL identifier: ' . $identifier);
        }

        return $identifier;
    }

    protected function sanitizeOperator(string $operator): string
    {
        $allowed = ['=', '!=', '<>', '>', '<', '>=', '<=', 'LIKE'];
        $normalized = strtoupper(trim($operator));

        if (!in_array($normalized, $allowed, true)) {
            throw new RuntimeException('Invalid SQL operator: ' . $operator);
        }

        return $normalized;
    }
}
