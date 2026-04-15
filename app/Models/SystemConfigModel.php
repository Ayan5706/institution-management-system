<?php

declare(strict_types=1);

namespace App\Models;

final class SystemConfigModel extends BaseModel
{
    protected string $table = 'system_config';

    protected array $fillable = [
        'config_key',
        'config_value',
        'updated_by',
        'updated_at',
    ];

    public function getValue(string $key, ?string $default = null): ?string
    {
        $row = $this->firstWhere('config_key', $key);

        if ($row === null || !array_key_exists('config_value', $row)) {
            return $default;
        }

        $value = $row['config_value'];
        return is_string($value) ? $value : $default;
    }

    /** @return array<string, string|null> */
    public function getValues(array $keys): array
    {
        if ($keys === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $sql = sprintf('SELECT config_key, config_value FROM `%s` WHERE config_key IN (%s)', $this->table, $placeholders);
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(array_values($keys));

        $map = [];
        foreach ($keys as $key) {
            $map[$key] = null;
        }

        $rows = $stmt->fetchAll() ?: [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $key = (string) ($row['config_key'] ?? '');
            $value = $row['config_value'] ?? null;
            if ($key !== '') {
                $map[$key] = is_string($value) ? $value : null;
            }
        }

        return $map;
    }

    /** @return array<int, string> */
    public function getWorkingDayCodes(): array
    {
        $value = $this->getValue('WORKING_DAYS');
        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $value = strtoupper(trim($value));
        $dayMap = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];

        if (is_numeric($value)) {
            $count = (int) $value;
            if ($count < 1) {
                return [];
            }
            $count = min($count, count($dayMap));
            return array_slice($dayMap, 0, $count);
        }

        $parts = array_filter(array_map('trim', explode(',', $value)));
        $filtered = [];
        foreach ($parts as $part) {
            if (in_array($part, $dayMap, true)) {
                $filtered[] = $part;
            }
        }

        return $filtered;
    }
}
