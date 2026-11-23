@extends('layouts.platform')

@php
    $u = fn (string $key, array $r = []) => __("platform.users.{$key}", $r);
    $me = auth()->user();
    // why an action is not available (the server refuses it anyway through UserPolicy)
    $protection = match (true) {
        $me->is($user) => $u('protected.self'),
        $user->isAdmin() => $u('protected.admin'),
        $user->isDemoAccount() => $u('protected.demo'),
        default => null,
    };
@endphp

@section('title', $user->name.' '.$user->last_name)
@section('heading', $user->name.' '.$user->last_name)
@section('lead', $user->email)

@section('content')
    <a class="adm-back" href="{{ route('platform.users.index') }}"><x-icon name="arrow-left" size="18" /> {{ $u('back') }}</a>

    @if ($user->isBanned())
        <div class="ui-notice ui-notice--error" role="status">
            <x-icon name="ban" size="18" />
            <div>
                <strong>{{ $u('bannedOn', ['date' => $user->banned_at->isoFormat('D MMM YYYY, HH:mm'), 'admin' => $user->bannedBy ? $user->bannedBy->name.' '.$user->bannedBy->last_name : '–']) }}</strong><br>
                {{ $u('reason', ['reason' => $user->ban_reason]) }}
            </div>
        </div>
    @endif

    <div class="plat-detail">
        <div class="plat-side">
            <section class="plat-panel">
                <div class="plat-panel-head">
                    <span @class(['ui-badge', 'ui-badge--brand' => $user->role === 'restaurateur', 'ui-badge--basil' => $user->role === 'admin'])>{{ $u("role.{$user->role}") }}</span>
                    @can('update', $user)
                        <a class="ui-btn ui-btn--outline ui-btn--sm" href="{{ route('platform.users.edit', $user) }}"><x-icon name="edit" size="16" /> {{ $u('edit') }}</a>
                    @endcan
                </div>
                <dl class="plat-facts">
                    <dt class="plat-fact-label">{{ $u('col.email') }}</dt><dd class="plat-fact-value">{{ $user->email }}</dd>
                    <dt class="plat-fact-label">{{ $u('fact.phone') }}</dt><dd class="plat-fact-value">{{ $user->phone ?? '–' }}</dd>
                    @if ($user->role === 'restaurateur')
                        <dt class="plat-fact-label">{{ $u('fact.company') }}</dt><dd class="plat-fact-value">{{ $user->company_name ?? '–' }}</dd>
                        <dt class="plat-fact-label">{{ $u('fact.vat') }}</dt><dd class="plat-fact-value">{{ $user->vat_number ?? '–' }}</dd>
                    @endif
                    <dt class="plat-fact-label">{{ $u('col.status') }}</dt><dd class="plat-fact-value">{{ $user->isBanned() ? $u('status.banned') : $u('status.active') }}</dd>
                    <dt class="plat-fact-label">{{ $u('fact.created') }}</dt><dd class="plat-fact-value">{{ $user->created_at?->isoFormat('D MMM YYYY') ?? '–' }}</dd>
                    <dt class="plat-fact-label">{{ $u('fact.verified') }}</dt><dd class="plat-fact-value">{{ $user->email_verified_at ? $u('fact.yes') : $u('fact.no') }}</dd>
                    <dt class="plat-fact-label">{{ $u('fact.google') }}</dt><dd class="plat-fact-value">{{ $user->google_id ? $u('fact.linked') : $u('fact.notLinked') }}</dd>
                    <dt class="plat-fact-label">{{ $u('fact.orders') }}</dt>
                    <dd class="plat-fact-value">
                        @if ($orders->n)
                            <a class="ui-link" href="{{ route('platform.tables.customer', ['email' => $user->email, ...\App\Analytics\Period::presets()['all']]) }}">{{ $u('fact.ordersValue', ['count' => $orders->n, 'value' => '€'.number_format($orders->gross, 2)]) }}</a>
                        @else
                            –
                        @endif
                    </dd>
                </dl>
                @if ($protection)
                    <p class="plat-note"><x-icon name="shield" size="16" /> {{ $protection }}</p>
                @endif
            </section>

            @can('ban', $user)
                <section class="plat-panel plat-danger">
                    <h2 class="plat-panel-title">{{ $u('ban') }}</h2>
                    <p class="plat-note">{{ $u('banEffect') }}</p>
                    <form method="POST" action="{{ route('platform.users.ban', $user) }}" class="adm-form">
                        @csrf
                        <x-field name="reason" :label="$u('banReason')" as="textarea" :value="old('reason')" :hint="$u('banReasonHint')" required maxlength="500" />
                        <button type="submit" class="ui-btn ui-btn--danger"><x-icon name="ban" size="18" /> {{ $u('ban') }}</button>
                    </form>
                </section>
            @endcan
            @can('unban', $user)
                <section class="plat-panel">
                    <h2 class="plat-panel-title">{{ $u('unban') }}</h2>
                    <p class="plat-note">{{ $u('unbanEffect') }}</p>
                    <form method="POST" action="{{ route('platform.users.unban', $user) }}">
                        @csrf
                        <button type="submit" class="ui-btn ui-btn--outline">{{ $u('unban') }}</button>
                    </form>
                </section>
            @endcan
        </div>

        <div class="plat-side">
            @if ($user->role === 'restaurateur')
                <section class="plat-panel">
                    <h2 class="plat-panel-title">{{ $u('restaurantsTitle') }} ({{ $user->restaurants_count }})</h2>
                    @if ($restaurants->isEmpty())
                        <p class="plat-note">{{ $u('noRestaurants') }}</p>
                    @else
                        <ul class="plat-missing">
                            @foreach ($restaurants as $r)
                                <li><a class="ui-link" href="{{ route('platform.tables.restaurant', $r->id) }}">{{ $r->name }}</a> · {{ $r->city }}</li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endif
            <section class="plat-panel">
                <h2 class="plat-panel-title">{{ $u('audit.title') }}</h2>
                @include('platform.users.partials.audit', ['entries' => $audit])
            </section>
        </div>
    </div>
@endsection
