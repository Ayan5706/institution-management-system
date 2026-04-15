<?php

declare(strict_types=1);

namespace App\Helpers;

final class ArrayHelper
{
    public static function get(array $array, string $key, mixed $default = null): mixed
    {
        if ($key === '') {
            return $default;
        }

        $segments = explode('.', $key);
        $value = $array;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    public static function only(array $array, array $keys): array
    {
        $result = [];

        foreach ($keys as $key) {
            if (array_key_exists($key, $array)) {
                $result[$key] = $array[$key];
            }
        }

        return $result;
    }

    public static function except(array $array, array $keys): array
    {
        foreach ($keys as $key) {
            unset($array[$key]);
        }

        return $array;
    }

    public static function pluck(array $items, string $valueKey, ?string $indexKey = null): array
    {
        $result = [];

        foreach ($items as $item) {
            if (!is_array($item) || !array_key_exists($valueKey, $item)) {
                continue;
            }

            if ($indexKey !== null && array_key_exists($indexKey, $item)) {
                $result[(string) $item[$indexKey]] = $item[$valueKey];
                continue;
            }

            $result[] = $item[$valueKey];
        }

        return $result;
    }
}
