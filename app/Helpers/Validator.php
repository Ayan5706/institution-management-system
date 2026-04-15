<?php

declare(strict_types=1);

namespace App\Helpers;

final class Validator
{
    public static function required(mixed $value): bool
    {
        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (is_array($value)) {
            return $value !== [];
        }

        return $value !== null;
    }

    public static function email(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function numeric(mixed $value): bool
    {
        return is_numeric($value);
    }

    public static function minLength(string $value, int $length): bool
    {
        return mb_strlen($value) >= $length;
    }

    public static function maxLength(string $value, int $length): bool
    {
        return mb_strlen($value) <= $length;
    }
}
