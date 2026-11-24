<?php

namespace App\Analytics;

use App\Type;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The shared filters of the platform backoffice (Maps, Charts, Tables): a date range, a city and a
 * cuisine. The partner statistics use the same class with a scope instead of city and cuisine:
 * the restaurants of one owner (ownerId) or a single restaurant (restaurantId), never from the URL. Every value comes from a whitelist or a strict date format: anything else silently falls
 * back to the default (last 12 months, every city, every cuisine), so a URL can never inject SQL.
 */
final class Period
{
    public const MAX_YEARS = 10;
    public const PRESETS = ['30d' => 30, '90d' => 90, '12m' => 365];

    private function __construct(
        public readonly CarbonImmutable $from,
        public readonly CarbonImmutable $to,
        public readonly ?string $city = null,
        public readonly ?int $typeId = null,
        public readonly ?int $ownerId = null,
        public readonly ?int $restaurantId = null,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        [$from, $to] = self::range($request);
        $city = $request->query('city');
        $type = $request->query('type');

        return new self(
            $from,
            $to,
            is_string($city) && in_array($city, self::cities(), true) ? $city : null,
            is_string($type) && ctype_digit($type) && isset(self::types()[(int) $type]) ? (int) $type : null,
        );
    }

    /**
     * Partner statistics: only the dates come from the request; the scope is set by the controller
     * after the ownership check (all the restaurants of $ownerId, or $restaurantId alone).
     */
    public static function forPartner(Request $request, int $ownerId, ?int $restaurantId = null): self
    {
        [$from, $to] = self::range($request);

        return new self($from, $to, null, null, $restaurantId === null ? $ownerId : null, $restaurantId);
    }

    /** whether a scope limits the orders to some restaurants (partner statistics) */
    public function scoped(): bool
    {
        return $this->ownerId !== null || $this->restaurantId !== null;
    }

    /** the date range of the request: default last 12 months, never in the future nor longer than MAX_YEARS */
    private static function range(Request $request): array
    {
        $today = CarbonImmutable::today();
        $to = self::date($request->query('to')) ?? $today;
        $from = self::date($request->query('from')) ?? $to->subYear()->addDay();
        if ($to->greaterThan($today)) {
            $to = $today;
        }
        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }
        if ($from->lessThan($to->subYears(self::MAX_YEARS))) {
            $from = $to->subYears(self::MAX_YEARS);
        }

        return [$from->startOfDay(), $to->endOfDay()];
    }

    public static function make(string $from, string $to, ?string $city = null, ?int $typeId = null): self
    {
        return new self(CarbonImmutable::parse($from)->startOfDay(), CarbonImmutable::parse($to)->endOfDay(), $city, $typeId);
    }

    /** the same number of days right before this period (for "vs previous period") */
    public function previous(): self
    {
        $days = $this->days();

        return new self($this->from->subDays($days), $this->from->subDay()->endOfDay(), $this->city, $this->typeId, $this->ownerId, $this->restaurantId);
    }

    public function days(): int
    {
        return (int) $this->from->diffInDays($this->to->startOfDay()) + 1;
    }

    /** query string of the filters, to keep them in links between tabs and pages */
    public function query(array $extra = []): array
    {
        return array_filter([
            'from' => $this->from->toDateString(),
            'to' => $this->to->toDateString(),
            'city' => $this->city,
            'type' => $this->typeId,
            ...$extra,
        ], fn ($v) => $v !== null && $v !== '');
    }

    /** the cache of a metric: separated by scope (owner, restaurant), metric and filters */
    public function cacheKey(string $name): string
    {
        $prefix = $this->scoped() ? "partner:o{$this->ownerId}:r{$this->restaurantId}" : 'platform';

        return $prefix.':'.$name.':'.md5(json_encode($this->query()));
    }

    /** limits a query on orders to the scope of this period (nothing for the backoffice) */
    public function applyScope(Builder $query, string $restaurantColumn = 'o.restaurant_id'): Builder
    {
        return $query
            ->when($this->restaurantId, fn (Builder $q, int $id) => $q->where($restaurantColumn, $id))
            ->when($this->ownerId, fn (Builder $q, int $owner) => $q->whereIn($restaurantColumn, fn (Builder $s) => $s
                ->select('id')->from('restaurants')->where('user_id', $owner)));
    }

    /** ready made ranges ending today, plus "all" from the first order (of the scope, if any) */
    public static function presets(?self $scope = null): array
    {
        $today = CarbonImmutable::today();
        $presets = [];
        foreach (self::PRESETS as $key => $days) {
            $presets[$key] = ['from' => $today->subDays($days - 1)->toDateString(), 'to' => $today->toDateString()];
        }
        $first = ($scope ? $scope->applyScope(DB::table('orders'), 'restaurant_id') : DB::table('orders'))->min('created_at');
        $presets['all'] = ['from' => CarbonImmutable::parse($first ?? $today)->toDateString(), 'to' => $today->toDateString()];

        return $presets;
    }

    /** cities that have restaurants (the zones of the backoffice are the restaurants' areas) */
    public static function cities(): array
    {
        return Cache::remember('platform:cities', 600, fn () => DB::table('restaurants')
            ->whereNotNull('city')->distinct()->orderBy('city')->pluck('city')->all());
    }

    /** id => name of the cuisines */
    public static function types(): array
    {
        return Cache::remember('platform:types', 600, fn () => Type::orderBy('name')->pluck('name', 'id')->all());
    }

    private static function date(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }
        $date = CarbonImmutable::createFromFormat('!Y-m-d', $value);

        return $date && $date->format('Y-m-d') === $value ? $date : null;
    }
}
