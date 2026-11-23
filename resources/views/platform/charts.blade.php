@extends('layouts.platform')

@section('title', __('platform.tabs.charts'))
@section('heading', __('platform.charts.title'))
@section('lead', __('platform.charts.lead'))

@section('content')
    <x-platform.filters :period="$period" />
    <div class="plat-empty">{{ __('platform.common.comingSoon') }}</div>
@endsection
