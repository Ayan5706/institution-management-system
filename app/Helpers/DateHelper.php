<?php

declare(strict_types=1);

namespace App\Helpers;

use DateTimeImmutable;
use DateTimeZone;

final class DateHelper
{
    public static function now(?string $timezone = null): DateTimeImmutable
    {
        $tz = $timezone !== null ? new DateTimeZone($timezone) : null;
        return new DateTimeImmutable('now', $tz);
    }

    public static function format(string $datetime, string $format = 'Y-m-d H:i:s', ?string $timezone = null): string
    {
        $tz = $timezone !== null ? new DateTimeZone($timezone) : null;
        $date = new DateTimeImmutable($datetime, $tz);

        return $date->format($format);
    }

    public static function today(string $format = 'Y-m-d', ?string $timezone = null): string
    {
        return self::now($timezone)->format($format);
    }
}
