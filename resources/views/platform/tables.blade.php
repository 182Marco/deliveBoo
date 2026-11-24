@extends('layouts.platform')

@section('title', __('platform.tabs.tables').' · '.__("platform.tables.sections.{$section}"))
@section('heading', __('platform.tables.title'))
@section('lead', __('platform.tables.lead'))

@php
    $t = fn (string $key, array $r = []) => __("platform.tables.{$key}", $r);
    $money = fn ($v) => $v === null ? '–' : '€'.number_format($v, 2);
    $pct = fn ($v) => $v === null ? '–' : number_format($v * 100, 1).'%';
    $date = fn ($v) => $v ? \Carbon\Carbon::parse($v)->isoFormat('D MMM YYYY, HH:mm') : '–';
    $day = fn ($v) => $v ? \Carbon\Carbon::parse($v)->isoFormat('D MMM YYYY') : '–';
    // what the shared filter form must keep when the period changes
    $keep = array_filter(request()->only(['q', 'sort', 'dir', 'state', 'by', 'unit', 'stars', 'visibility', 'text']), fn ($v) => is_string($v) && $v !== '');
    $stateBadge = ['completed' => 'ui-badge--basil', 'in_progress' => '', 'cancelled' => 'ui-badge--tomato', 'failed' => 'ui-badge--tomato'];
    $rate = number_format(config('platform.commission_rate') * 100, 0).'%';
@endphp

@section('content')
    <nav class="plat-subtabs" aria-label="{{ __('platform.tabs.tables') }}">
        @foreach ($sections as $item)
            <a
                @class(['plat-subtab', 'plat-subtab--active' => $item === $section])
                href="{{ route('platform.tables', ['section' => $item, ...$period->query()]) }}"
                @if ($item === $section) aria-current="page" @endif
            >{{ __("platform.tables.sections.{$item}") }}</a>
        @endforeach
    </nav>

    <x-platform.filters :period="$period" :keep="$keep" />

    {{-- tools of the section: search and its own filters (the period filters ride along as hidden fields) --}}
    <form class="plat-toolbar" method="GET" action="{{ url()->current() }}">
        @foreach ($period->query() as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
        @if (in_array($section, ['restaurants', 'orders', 'customers', 'reviews'], true))
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="dir" value="{{ $dir }}">
            <div class="ui-field plat-toolbar-search">
                <label class="ui-label" for="t-q">{{ $t('search') }}</label>
                <input class="ui-input" id="t-q" type="search" name="q" value="{{ $q }}" maxlength="100" placeholder="{{ $t("placeholder.{$section}") }}">
            </div>
        @endif
        @if ($section === 'orders')
            <div class="ui-field">
                <label class="ui-label" for="t-state">{{ $t('col.status') }}</label>
                <select class="ui-input" id="t-state" name="state">
                    <option value="">{{ $t('state.all') }}</option>
                    @foreach (\App\Analytics\Tables::ORDER_STATES as $s)
                        <option value="{{ $s }}" @selected($state === $s)>{{ $t("state.{$s}") }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        @if ($section === 'reviews')
            <div class="ui-field">
                <label class="ui-label" for="t-stars">{{ __('platform.reviews.stars') }}</label>
                <select class="ui-input" id="t-stars" name="stars">
                    <option value="">{{ __('platform.reviews.allStars') }}</option>
                    @foreach (range(5, 1) as $n)
                        <option value="{{ $n }}" @selected($stars === $n)>{{ trans_choice('platform.reviews.starsCount', $n, ['count' => $n]) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ui-field">
                <label class="ui-label" for="t-visibility">{{ __('platform.reviews.visibility') }}</label>
                <select class="ui-input" id="t-visibility" name="visibility">
                    <option value="">{{ __('platform.reviews.allVisibility') }}</option>
                    @foreach (\App\Analytics\Tables::REVIEW_VISIBILITY as $v)
                        <option value="{{ $v }}" @selected($visibility === $v)>{{ __("platform.reviews.{$v}") }}</option>
                    @endforeach
                </select>
            </div>
            <label class="ui-check">
                <input class="ui-check-input" type="checkbox" name="text" value="1" @checked($withText)>
                {{ __('platform.reviews.withText') }}
            </label>
        @endif
        @if ($section === 'zones')
            <div class="ui-field">
                <label class="ui-label" for="t-by">{{ $t('by.label') }}</label>
                <select class="ui-input" id="t-by" name="by">
                    <option value="city" @selected($by === 'city')>{{ $t('by.city') }}</option>
                    <option value="postal_code" @selected($by === 'postal_code')>{{ $t('by.postal_code') }}</option>
                </select>
            </div>
        @endif
        @if ($section === 'periods')
            <div class="ui-field">
                <label class="ui-label" for="t-unit">{{ $t('unit.label') }}</label>
                <select class="ui-input" id="t-unit" name="unit">
                    @foreach (\App\Analytics\Tables::PERIOD_UNITS as $u)
                        <option value="{{ $u }}" @selected($unit === $u)>{{ __("platform.charts.unit.{$u}") }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="plat-filters-actions">
            <button type="submit" class="ui-btn ui-btn--outline">{{ $t('search') }}</button>
            @if ($q !== '' || ($state ?? null) || ($stars ?? null) || ($visibility ?? null) || ($withText ?? false))
                <a class="ui-btn ui-btn--ghost" href="{{ url()->current().'?'.http_build_query($period->query()) }}">{{ $t('clear') }}</a>
            @endif
        </div>
    </form>

    @php($count = $rows instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator ? $rows->total() : count($rows))
    <p class="plat-note" role="status">{{ trans_choice('platform.tables.results', $count, ['count' => number_format($count)]) }}@if ($section === 'reviews') · {{ __('platform.reviews.note') }}@else · {{ $t('estimateNote', ['rate' => $rate]) }}@endif</p>

    @if ($count === 0)
        <div class="plat-empty">{{ $t('none') }}</div>
    @else
        <div class="ui-table-wrap">
            <table class="ui-table">
                @switch($section)
                    @case('restaurants')
                        <thead><tr>
                            <x-platform.th key="name" :label="$t('col.restaurant')" :sort="$sort" :dir="$dir" />
                            <x-platform.th key="city" :label="$t('col.city')" :sort="$sort" :dir="$dir" />
                            <x-platform.th key="gross_value" :label="$t('col.gross')" :sort="$sort" :dir="$dir" num />
                            <x-platform.th key="completed_orders" :label="$t('col.orders')" :sort="$sort" :dir="$dir" num />
                            <x-platform.th key="average_order" :label="$t('col.average')" :sort="$sort" :dir="$dir" num />
                            <th class="ui-th ui-th--num" scope="col">{{ $t('col.revenue') }}</th>
                            <x-platform.th key="cancel_rate" :label="$t('col.cancelRate')" :sort="$sort" :dir="$dir" num />
                            <x-platform.th key="rating" :label="$t('col.rating')" :sort="$sort" :dir="$dir" num />
                        </tr></thead>
                        <tbody>
                            @foreach ($rows as $r)
                                <tr>
                                    <td class="ui-td ui-td--strong"><a class="ui-link" href="{{ route('platform.tables.restaurant', ['restaurant' => $r['id'], ...$period->query()]) }}">{{ $r['name'] }}</a></td>
                                    <td class="ui-td">{{ $r['city'] }}</td>
                                    <td class="ui-td ui-td--num">{{ $money($r['gross_value']) }}</td>
                                    <td class="ui-td ui-td--num">{{ number_format($r['completed_orders']) }}</td>
                                    <td class="ui-td ui-td--num">{{ $money($r['average_order']) }}</td>
                                    <td class="ui-td ui-td--num">{{ $money($r['revenue']) }}</td>
                                    <td class="ui-td ui-td--num">{{ $pct($r['cancel_rate']) }}</td>
                                    <td class="ui-td ui-td--num">{{ $r['rating'] === null ? '–' : number_format($r['rating'], 1) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        @break

                    @case('orders')
                        <thead><tr>
                            <th class="ui-th" scope="col">{{ $t('col.order') }}</th>
                            <x-platform.th key="created_at" :label="$t('col.date')" :sort="$sort" :dir="$dir" />
                            <th class="ui-th" scope="col">{{ $t('col.restaurant') }}</th>
                            <th class="ui-th" scope="col">{{ $t('col.customer') }}</th>
                            <th class="ui-th" scope="col">{{ $t('col.status') }}</th>
                            <x-platform.th key="price" :label="$t('col.amount')" :sort="$sort" :dir="$dir" num />
                        </tr></thead>
                        <tbody>
                            @foreach ($rows as $o)
                                @php($st = \App\Analytics\Tables::stateOf($o))
                                <tr>
                                    <td class="ui-td ui-td--strong"><a class="ui-link" href="{{ route('platform.tables.order', ['order' => $o->id, ...$period->query()]) }}">#{{ $o->id }}</a></td>
                                    <td class="ui-td">{{ $date($o->created_at) }}</td>
                                    <td class="ui-td">{{ $o->restaurant_name ?? '–' }}</td>
                                    <td class="ui-td">
                                        <a class="ui-link" href="{{ route('platform.tables.customer', ['email' => $o->customer_email, ...$period->query()]) }}">{{ $o->customer_name }} {{ $o->customer_lastName }}</a>
                                        <span class="plat-cell-note">{{ $o->customer_email }}</span>
                                    </td>
                                    <td class="ui-td"><span class="ui-badge {{ $stateBadge[$st] }}">{{ $t("state.{$st}") }}</span></td>
                                    <td class="ui-td ui-td--num">{{ $money((float) $o->price) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        @break

                    @case('customers')
                        <thead><tr>
                            <x-platform.th key="name" :label="$t('col.customer')" :sort="$sort" :dir="$dir" />
                            <th class="ui-th" scope="col">{{ $t('col.account') }}</th>
                            <x-platform.th key="gross_value" :label="$t('col.gross')" :sort="$sort" :dir="$dir" num />
                            <x-platform.th key="completed_orders" :label="$t('col.orders')" :sort="$sort" :dir="$dir" num />
                            <th class="ui-th ui-th--num" scope="col">{{ $t('col.average') }}</th>
                            <x-platform.th key="first_order" :label="$t('col.firstOrder')" :sort="$sort" :dir="$dir" />
                            <x-platform.th key="last_order" :label="$t('col.lastOrder')" :sort="$sort" :dir="$dir" />
                        </tr></thead>
                        <tbody>
                            @foreach ($rows as $c)
                                <tr>
                                    <td class="ui-td">
                                        <a class="ui-link" href="{{ route('platform.tables.customer', ['email' => $c['email'], ...$period->query()]) }}">{{ $c['name'] }}</a>
                                        <span class="plat-cell-note">{{ $c['email'] }}</span>
                                    </td>
                                    <td class="ui-td"><span @class(['ui-badge', 'ui-badge--basil' => $c['user_id']])>{{ $c['user_id'] ? $t('registered') : $t('guest') }}</span></td>
                                    <td class="ui-td ui-td--num">{{ $money($c['gross_value']) }}</td>
                                    <td class="ui-td ui-td--num">{{ number_format($c['completed_orders']) }}</td>
                                    <td class="ui-td ui-td--num">{{ $money($c['average_order']) }}</td>
                                    <td class="ui-td">{{ $day($c['first_order']) }}</td>
                                    <td class="ui-td">{{ $day($c['last_order']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        @break

                    @case('zones')
                        <thead><tr>
                            <x-platform.th key="zone" :label="$t('col.zone')" :sort="$sort" :dir="$dir" />
                            <x-platform.th key="restaurants" :label="$t('col.restaurants')" :sort="$sort" :dir="$dir" num />
                            <x-platform.th key="gross_value" :label="$t('col.gross')" :sort="$sort" :dir="$dir" num />
                            <th class="ui-th ui-th--num" scope="col">{{ $t('col.share') }}</th>
                            <x-platform.th key="completed_orders" :label="$t('col.orders')" :sort="$sort" :dir="$dir" num />
                            <x-platform.th key="average_order" :label="$t('col.average')" :sort="$sort" :dir="$dir" num />
                            <x-platform.th key="revenue" :label="$t('col.revenue')" :sort="$sort" :dir="$dir" num />
                            <th class="ui-th ui-th--num" scope="col">{{ $t('col.cancelRate') }}</th>
                        </tr></thead>
                        <tbody>
                            @foreach ($rows as $z)
                                <tr>
                                    <td class="ui-td ui-td--strong">
                                        @if ($by === 'city')
                                            <a class="ui-link" href="{{ route('platform.tables', ['section' => 'restaurants', ...$period->query(['city' => $z['zone']])]) }}">{{ $z['zone'] }}</a>
                                        @else
                                            {{ $z['zone'] }}
                                        @endif
                                    </td>
                                    <td class="ui-td ui-td--num">{{ $z['restaurants'] }}</td>
                                    <td class="ui-td ui-td--num">{{ $money($z['gross_value']) }}</td>
                                    <td class="ui-td ui-td--num">{{ $pct($z['share']) }}</td>
                                    <td class="ui-td ui-td--num">{{ number_format($z['completed_orders']) }}</td>
                                    <td class="ui-td ui-td--num">{{ $money($z['average_order']) }}</td>
                                    <td class="ui-td ui-td--num">{{ $money($z['revenue']) }}</td>
                                    <td class="ui-td ui-td--num">{{ $pct($z['cancel_rate']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        @break

                    @case('periods')
                        <thead><tr>
                            <th class="ui-th" scope="col">{{ $t('col.period') }}</th>
                            <th class="ui-th ui-th--num" scope="col">{{ $t('col.gross') }}</th>
                            <th class="ui-th ui-th--num" scope="col">{{ $t('col.change') }}</th>
                            <th class="ui-th ui-th--num" scope="col">{{ $t('col.orders') }}</th>
                            <th class="ui-th ui-th--num" scope="col">{{ $t('col.average') }}</th>
                            <th class="ui-th ui-th--num" scope="col">{{ $t('col.revenue') }}</th>
                            <th class="ui-th ui-th--num" scope="col">{{ $t('col.customers') }}</th>
                            <th class="ui-th ui-th--num" scope="col">{{ $t('col.newCustomers') }}</th>
                            <th class="ui-th ui-th--num" scope="col">{{ $t('col.cancelled') }}</th>
                            <th class="ui-th ui-th--num" scope="col">{{ $t('col.failed') }}</th>
                        </tr></thead>
                        <tbody>
                            @foreach ($rows as $p)
                                <tr>
                                    <td class="ui-td ui-td--strong">{{ \App\Http\Controllers\Platform\ChartController::bucketLabel($p['bucket'], $unit) }}</td>
                                    <td class="ui-td ui-td--num">{{ $money($p['gross_value']) }}</td>
                                    <td class="ui-td ui-td--num">
                                        @if ($p['gross_change'] !== null)
                                            <span @class(['plat-delta', 'plat-delta--good' => $p['gross_change'] > 0, 'plat-delta--bad' => $p['gross_change'] < 0])>{{ $p['gross_change'] > 0 ? '▲ +' : ($p['gross_change'] < 0 ? '▼ ' : '') }}{{ number_format($p['gross_change'] * 100, 1) }}%</span>
                                        @else
                                            –
                                        @endif
                                    </td>
                                    <td class="ui-td ui-td--num">{{ number_format($p['completed_orders']) }}</td>
                                    <td class="ui-td ui-td--num">{{ $money($p['average_order']) }}</td>
                                    <td class="ui-td ui-td--num">{{ $money($p['revenue']) }}</td>
                                    <td class="ui-td ui-td--num">{{ number_format($p['customers']) }}</td>
                                    <td class="ui-td ui-td--num">{{ number_format($p['new_customers']) }}</td>
                                    <td class="ui-td ui-td--num">{{ $p['cancelled_orders'] }}</td>
                                    <td class="ui-td ui-td--num">{{ $p['failed_orders'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        @break

                    @case('reviews')
                        <thead><tr>
                            <x-platform.th key="created_at" :label="$t('col.date')" :sort="$sort" :dir="$dir" />
                            <th class="ui-th" scope="col">{{ $t('col.restaurant') }}</th>
                            <th class="ui-th" scope="col">{{ __('platform.reviews.author') }}</th>
                            <x-platform.th key="rating" :label="__('platform.reviews.stars')" :sort="$sort" :dir="$dir" num />
                            <th class="ui-th" scope="col">{{ __('platform.reviews.text') }}</th>
                            <th class="ui-th" scope="col">{{ $t('col.status') }}</th>
                            <th class="ui-th ui-td--actions" scope="col"><span class="ui-sr-only">{{ __('platform.reviews.actions') }}</span></th>
                        </tr></thead>
                        <tbody>
                            @foreach ($rows as $v)
                                <tr id="review-{{ $v->id }}">
                                    <td class="ui-td">{{ $date($v->created_at) }}</td>
                                    <td class="ui-td ui-td--strong">
                                        @if ($v->restaurant_id)
                                            <a class="ui-link" href="{{ route('platform.tables.restaurant', ['restaurant' => $v->restaurant_id, ...$period->query()]) }}">{{ $v->restaurant_name }}</a>
                                        @else
                                            –
                                        @endif
                                    </td>
                                    <td class="ui-td">
                                        {{ $v->author_name ? $v->author_name.' '.$v->author_last_name : __('platform.reviews.anonymous') }}
                                        @if ($v->order_id)
                                            <a class="plat-cell-note ui-link" href="{{ route('platform.tables.order', ['order' => $v->order_id, ...$period->query()]) }}">#{{ $v->order_id }}</a>
                                        @endif
                                    </td>
                                    <td class="ui-td ui-td--num"><x-stars :value="$v->rating" /></td>
                                    <td class="ui-td plat-review-text">
                                        @if ($v->comment)
                                            {{ $v->comment }}
                                        @else
                                            <span class="plat-cell-note">{{ __('platform.reviews.noText') }}</span>
                                        @endif
                                        @if ($v->reply)
                                            <span class="plat-cell-note">{{ __('platform.reviews.replied') }}</span>
                                        @endif
                                    </td>
                                    <td class="ui-td">
                                        @if ($v->hidden_at)
                                            <span class="ui-badge ui-badge--tomato">{{ __('platform.reviews.hidden') }}</span>
                                            <span class="plat-cell-note">{{ __('platform.reviews.hiddenBy', ['admin' => trim($v->hidden_by_name.' '.$v->hidden_by_last_name) ?: '–', 'date' => $day($v->hidden_at)]) }}</span>
                                        @else
                                            <span class="ui-badge ui-badge--basil">{{ __('platform.reviews.visible') }}</span>
                                        @endif
                                    </td>
                                    <td class="ui-td ui-td--actions">
                                        <form method="POST" action="{{ route($v->hidden_at ? 'platform.reviews.unhide' : 'platform.reviews.hide', $v->id) }}">
                                            @csrf
                                            <button type="submit" @class(['ui-btn', 'ui-btn--sm', 'ui-btn--danger' => ! $v->hidden_at, 'ui-btn--outline' => $v->hidden_at])>
                                                <x-icon :name="$v->hidden_at ? 'eye' : 'ban'" size="16" />
                                                {{ $v->hidden_at ? __('platform.reviews.unhide') : __('platform.reviews.hide') }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        @break
                @endswitch
            </table>
        </div>
        @if ($rows instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
            {{ $rows->links() }}
        @endif
    @endif
@endsection
