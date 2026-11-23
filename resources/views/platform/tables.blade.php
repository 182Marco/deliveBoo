@extends('layouts.platform')

@section('title', __('platform.tabs.tables'))
@section('heading', __('platform.tables.title'))
@section('lead', __('platform.tables.lead'))

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
    <x-platform.filters :period="$period" />
    <div class="plat-empty">{{ __('platform.common.comingSoon') }}</div>
@endsection
