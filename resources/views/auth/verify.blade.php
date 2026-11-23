@extends('layouts.auth')

@section('title', __('partner.auth.verify.title'))
@section('heading', __('partner.auth.verify.heading'))
@section('lead', __('partner.auth.verify.lead'))

@section('content')
    @if (session('resent'))
        <div class="ui-notice ui-notice--success" role="status">{{ __('partner.auth.verify.resent') }}</div>
    @endif

    <form class="adm-form" method="POST" action="{{ route('verification.resend') }}">
        @csrf
        <p class="ui-muted">{{ __('partner.auth.verify.notReceived') }}</p>
        <button type="submit" class="ui-btn ui-btn--outline ui-btn--block">{{ __('partner.auth.verify.submit') }}</button>
    </form>
@endsection
