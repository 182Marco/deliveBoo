@extends('layouts.platform')

@section('title', __('platform.tabs.users'))
@section('heading', __('platform.users.title'))
@section('lead', __('platform.users.lead'))

@php
    $u = fn (string $key, array $r = []) => __("platform.users.{$key}", $r);
    $n = fn (string $role, string $col = 'n') => (int) ($counts[$role]->{$col} ?? 0);
    $roleBadge = ['customer' => '', 'restaurateur' => 'ui-badge--brand', 'admin' => 'ui-badge--basil'];
@endphp

@section('content')
    <div class="plat-toolbar plat-toolbar--between">
        <p class="plat-note">{{ $u('summary', ['customers' => $n('customer'), 'restaurateurs' => $n('restaurateur'), 'admins' => $n('admin'), 'banned' => $n('customer', 'banned') + $n('restaurateur', 'banned') + $n('admin', 'banned')]) }}</p>
        @can('create', \App\User::class)
            <a class="ui-btn ui-btn--primary" href="{{ route('platform.users.create') }}"><x-icon name="plus" size="18" /> {{ $u('new') }}</a>
        @endcan
    </div>

    <form class="plat-toolbar plat-toolbar--card" method="GET" action="{{ route('platform.users.index') }}">
        <div class="ui-field plat-toolbar-search">
            <label class="ui-label" for="u-q">{{ $u('search') }}</label>
            <input class="ui-input" id="u-q" type="search" name="q" value="{{ $q }}" maxlength="100" placeholder="{{ $u('placeholder') }}">
        </div>
        <div class="ui-field">
            <label class="ui-label" for="u-role">{{ $u('filterRole') }}</label>
            <select class="ui-input" id="u-role" name="role">
                <option value="">{{ $u('allRoles') }}</option>
                @foreach (\App\User::ROLES as $r)
                    <option value="{{ $r }}" @selected($role === $r)>{{ $u("role.{$r}") }}</option>
                @endforeach
            </select>
        </div>
        <div class="ui-field">
            <label class="ui-label" for="u-status">{{ $u('filterStatus') }}</label>
            <select class="ui-input" id="u-status" name="status">
                <option value="">{{ $u('allStatuses') }}</option>
                @foreach (\App\Http\Controllers\Platform\UserController::STATUSES as $s)
                    <option value="{{ $s }}" @selected($status === $s)>{{ $u("status.{$s}") }}</option>
                @endforeach
            </select>
        </div>
        <input type="hidden" name="sort" value="{{ $sort }}">
        <input type="hidden" name="dir" value="{{ $dir }}">
        <div class="plat-filters-actions">
            <button type="submit" class="ui-btn ui-btn--outline">{{ $u('apply') }}</button>
            @if ($q !== '' || $role || $status)
                <a class="ui-btn ui-btn--ghost" href="{{ route('platform.users.index') }}">{{ $u('clear') }}</a>
            @endif
        </div>
    </form>

    <p class="plat-note" role="status">{{ trans_choice('platform.tables.results', $users->total(), ['count' => number_format($users->total())]) }}</p>

    @if ($users->total() === 0)
        <div class="plat-empty">{{ $u('none') }}</div>
    @else
        <div class="ui-table-wrap">
            <table class="ui-table">
                <thead><tr>
                    <x-platform.th key="name" :label="$u('col.name')" :sort="$sort" :dir="$dir" />
                    <x-platform.th key="email" :label="$u('col.email')" :sort="$sort" :dir="$dir" />
                    <x-platform.th key="role" :label="$u('col.role')" :sort="$sort" :dir="$dir" />
                    <th class="ui-th" scope="col">{{ $u('col.status') }}</th>
                    <th class="ui-th ui-th--num" scope="col">{{ $u('col.restaurants') }}</th>
                    <x-platform.th key="created_at" :label="$u('col.created')" :sort="$sort" :dir="$dir" />
                </tr></thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td class="ui-td ui-td--strong">
                                <a class="ui-link" href="{{ route('platform.users.show', $user) }}">{{ $user->name }} {{ $user->last_name }}</a>
                                @if ($user->company_name)
                                    <span class="plat-cell-note">{{ $user->company_name }}</span>
                                @endif
                            </td>
                            <td class="ui-td">{{ $user->email }}</td>
                            <td class="ui-td"><span class="ui-badge {{ $roleBadge[$user->role] ?? '' }}">{{ $u("role.{$user->role}") }}</span></td>
                            <td class="ui-td">
                                @if ($user->isBanned())
                                    <span class="ui-badge ui-badge--tomato"><x-icon name="ban" size="14" /> {{ $u('status.banned') }}</span>
                                @else
                                    {{ $u('status.active') }}
                                @endif
                            </td>
                            <td class="ui-td ui-td--num">{{ $user->restaurants_count ?: '–' }}</td>
                            <td class="ui-td">{{ $user->created_at?->isoFormat('D MMM YYYY') ?? '–' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $users->links() }}
    @endif

    <section class="plat-panel">
        <h2 class="plat-panel-title">{{ $u('audit.recent') }}</h2>
        @include('platform.users.partials.audit', ['entries' => $recent, 'showTarget' => true])
    </section>
@endsection
