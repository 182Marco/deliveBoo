{{-- every role: the frame of the user's area (AvatarController picks it) --}}
@extends($layout)

@section('title', __('partner.avatar.title'))

@php
    // JSON for data attributes: Blade's {{ }} escapes it for the attribute; the HEX flags keep it inert anywhere else
    $json = fn ($data) => json_encode($data, JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
@endphp

@section('content')
<div class="ui-container">
    {{-- under 600px the form below is a full-screen sheet over all of this (header included) --}}
    <a class="adm-back" href="{{ $returnUrl }}" data-avatar-behind><x-icon name="arrow-left" size="18" /> {{ __('partner.avatar.cancel') }}</a>
    <div class="adm-page-head" data-avatar-behind>
        <div class="adm-page-intro">
            <p class="ui-eyebrow">{{ __('partner.header.customizeAvatar') }}</p>
            <h1 class="ui-title">{{ __('partner.avatar.title') }}</h1>
            <p class="ui-muted">{{ __('partner.avatar.intro') }}</p>
        </div>
    </div>

    <form class="adm-avatar-form" method="POST" action="{{ route('account.avatar.update') }}" data-avatar-form @if ($errors->any()) data-avatar-unsaved @endif>
        @csrf
        @method('PUT')
        {{-- phones only: the bar of the sheet (the page heading and the site header stay behind it) --}}
        <div class="adm-avatar-bar">
            <p class="ui-eyebrow">{{ __('partner.header.customizeAvatar') }}</p>
            <a class="ui-btn ui-btn--ghost ui-btn--icon" href="{{ $returnUrl }}" aria-label="{{ __('partner.avatar.cancel') }}"><x-icon name="close" size="20" /></a>
        </div>
        @if ($errors->any())
            <div class="ui-notice ui-notice--error adm-flash" role="alert">{{ __('partner.avatar.invalid') }}</div>
        @endif
        <input type="hidden" name="seed" value="{{ $value['seed'] }}" data-avatar-seed-input>
        <input type="hidden" name="overrides" value="{{ $json($value['overrides']) }}" data-avatar-overrides-input>

        <div class="adm-avatar-editor ui-card ui-card--pad" data-avatar-editor data-value="{{ $json($value) }}" data-labels="{{ $json($labels) }}">
            <noscript><p class="ui-muted">{{ __('partner.avatar.noscript') }}</p></noscript>
        </div>

        <div class="adm-form-foot adm-avatar-foot">
            <button class="ui-btn ui-btn--primary" type="submit" data-avatar-save disabled>{{ __('partner.avatar.save') }}</button>
            <a class="ui-btn ui-btn--ghost" href="{{ $returnUrl }}">{{ __('partner.avatar.cancel') }}</a>
        </div>
    </form>
</div>
@vite('resources/js/jsBack/avatarEditor.js')
@endsection
