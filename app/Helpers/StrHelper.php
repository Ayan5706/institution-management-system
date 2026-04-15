<?php

declare(strict_types=1);

namespace App\Helpers;

final class StrHelper
{
    public static function slug(string $value, string $separator = '-'): string
    {
        $value = trim(strtolower($value));
        $value = preg_replace('/[^a-z0-9]+/i', $separator, $value) ?? '';

        return trim($value, $separator);
    }

    public static function limit(string $value, int $limit = 100, string $end = '...'): string
    {
        if (mb_strlen($value) <= $limit) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $limit)) . $end;
    }

    public static function startsWith(string $haystack, string $needle): bool
    {
        if ($needle === '') {
            return true;
        }

        return str_starts_with($haystack, $needle);
    }

    public static function random(int $length = 32): string
    {
        if ($length < 1) {
            $length = 1;
        }

        $bytes = random_bytes((int) ceil($length / 2));
        return substr(bin2hex($bytes), 0, $length);
    }
}
