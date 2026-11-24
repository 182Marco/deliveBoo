<?php

namespace App\Analytics;

use Carbon\CarbonImmutable;

/**
 * Presentation of the aggregated metrics shared by the platform Charts tab and the partner
 * statistics: bucket labels in the page language, partial buckets, heatmap levels.
 */
final class ChartFormat
{
    /**
     * the label of every bucket; a week or month cut by the period would look like a collapse, so
     * it is marked as partial with the $partial text (":label" placeholder)
     */
    public static function labels(Period $period, string $unit, array $buckets, string $partialKey): array
    {
        $partial = self::partialBuckets($period, $unit, $buckets);

        return array_map(fn ($bucket) => in_array($bucket, $partial, true)
            ? __($partialKey, ['label' => self::bucketLabel($bucket, $unit)])
            : self::bucketLabel($bucket, $unit), $buckets);
    }

    /** 7×24 cells with a level 0–5 of the one-hue scale (0 = no orders) and the localized weekday */
    public static function heatmap(array $grid): array
    {
        $max = max(1, ...array_map(fn ($row) => max($row), $grid));
        $monday = CarbonImmutable::parse('2030-01-07')->locale(app()->getLocale());
        $rows = [];
        foreach ($grid as $d => $hours) {
            $rows[] = [
                'day' => $monday->addDays($d)->isoFormat('ddd'),
                'cells' => array_map(fn ($n) => ['count' => $n, 'level' => $n ? (int) ceil(5 * $n / $max) : 0], $hours),
            ];
        }

        return ['rows' => $rows, 'max' => $max];
    }

    /** buckets (first and last) that start before or end after the period */
    public static function partialBuckets(Period $period, string $unit, array $buckets): array
    {
        if ($unit === 'day' || $buckets === []) {
            return [];
        }
        $start = fn (string $b) => CarbonImmutable::parse($unit === 'month' ? "{$b}-01" : $b);
        $end = fn (string $b) => $unit === 'month' ? $start($b)->endOfMonth() : $start($b)->addDays(6)->endOfDay();
        $first = $buckets[0];
        $last = $buckets[array_key_last($buckets)];

        return array_values(array_unique(array_filter([
            $start($first)->lessThan($period->from) ? $first : null,
            $end($last)->greaterThan($period->to) ? $last : null,
        ])));
    }

    public static function bucketLabel(string $bucket, string $unit): string
    {
        $date = CarbonImmutable::parse($unit === 'month' ? "{$bucket}-01" : $bucket)->locale(app()->getLocale());

        return $unit === 'month' ? $date->isoFormat('MMM YYYY') : $date->isoFormat('D MMM');
    }
}
