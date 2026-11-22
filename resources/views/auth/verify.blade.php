@extends('layouts.auth')

@section('title', 'Verify your email')
@section('heading', 'Check your inbox')
@section('lead', 'Before going on, open the verification link we sent to your email address.')

@section('content')
    @if (session('resent'))
        <div class="ui-notice ui-notice--success" role="status">A fresh verification link has been sent to your email address.</div>
    @endif

    <form class="adm-form" method="POST" action="{{ route('verification.resend') }}">
        @csrf
        <p class="ui-muted">Did not receive the email?</p>
        <button type="submit" class="ui-btn ui-btn--outline ui-btn--block">Send another link</button>
    </form>
@endsection
