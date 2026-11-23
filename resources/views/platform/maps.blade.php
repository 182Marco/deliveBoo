@extends('layouts.platform')

@section('title', __('platform.tabs.maps'))
@section('heading', __('platform.maps.title'))
@section('lead', __('platform.maps.lead'))

@section('content')
    <x-platform.filters :period="$period" />
    <p class="plat-note">{{ __('platform.filters.zoneNote') }}</p>
    <div class="plat-empty">{{ __('platform.common.comingSoon') }}</div>
@endsection
