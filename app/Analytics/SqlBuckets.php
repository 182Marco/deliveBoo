<?php

namespace App\Analytics;

use Illuminate\Support\Facades\DB;

/**
 * Date and time expressions for the aggregations of the backoffice, for the two databases of the
 * project: MySQL (development, production) and SQLite (tests). Only fixed SQL is returned: the
 * column is always one of ours, never user input.
 */
final class SqlBuckets
{
    private static function sqlite(): bool
    {
        return DB::connection()->getDriverName() === 'sqlite';
    }

    /** 'day' → 2026-03-14, 'week' → the Monday of the week, 'month' → 2026-03 */
    public static function period(string $granularity, string $column): string
    {
        return match ([$granularity, self::sqlite()]) {
            ['day', true] => "strftime('%Y-%m-%d', {$column})",
            ['day', false] => "DATE_FORMAT({$column}, '%Y-%m-%d')",
            ['week', true] => "date({$column}, '-' || ((CAST(strftime('%w', {$column}) AS INTEGER) + 6) % 7) || ' days')",
            ['week', false] => "DATE_FORMAT(DATE_SUB({$column}, INTERVAL WEEKDAY({$column}) DAY), '%Y-%m-%d')",
            ['month', true] => "strftime('%Y-%m', {$column})",
            ['month', false] => "DATE_FORMAT({$column}, '%Y-%m')",
            default => throw new \InvalidArgumentException("Unknown granularity {$granularity}"),
        };
    }

    /** 0 = Monday … 6 = Sunday */
    public static function weekday(string $column): string
    {
        return self::sqlite() ? "((CAST(strftime('%w', {$column}) AS INTEGER) + 6) % 7)" : "WEEKDAY({$column})";
    }

    /** 0 … 23 */
    public static function hour(string $column): string
    {
        return self::sqlite() ? "CAST(strftime('%H', {$column}) AS INTEGER)" : "HOUR({$column})";
    }

    /** minutes between two timestamps */
    public static function minutesBetween(string $from, string $to): string
    {
        return self::sqlite()
            ? "CAST(ROUND((julianday({$to}) - julianday({$from})) * 1440) AS INTEGER)"
            : "TIMESTAMPDIFF(MINUTE, {$from}, {$to})";
    }

    /** the granularity that keeps a chart readable: days up to 2 months, weeks up to ~6 months, then months */
    public static function granularityFor(Period $period): string
    {
        return match (true) {
            $period->days() <= 62 => 'day',
            $period->days() <= 190 => 'week',
            default => 'month',
        };
    }
}
