@extends('layouts.platform')

{{-- new account (customer or restaurateur, never admin) or edit of the personal data --}}
@php
    $u = fn (string $key, array $r = []) => __("platform.users.{$key}", $r);
    $editing = $user !== null;
    $role = $editing ? $user->role : old('role', \App\User::CUSTOMER);
@endphp

@section('title', $editing ? $u('form.editTitle', ['name' => $user->name.' '.$user->last_name]) : $u('form.createTitle'))
@section('heading', $editing ? $u('form.editTitle', ['name' => $user->name.' '.$user->last_name]) : $u('form.createTitle'))
@section('lead', $editing ? $u('form.editLead') : $u('form.createLead'))

@section('content')
    <a class="adm-back" href="{{ $editing ? route('platform.users.show', $user) : route('platform.users.index') }}"><x-icon name="arrow-left" size="18" /> {{ $u('back') }}</a>

    <form class="plat-panel adm-form plat-form" method="POST" action="{{ $editing ? route('platform.users.update', $user) : route('platform.users.store') }}">
        @csrf
        @if ($editing)
            @method('PUT')
        @else
            <fieldset class="plat-fieldset">
                <legend class="ui-label">{{ $u('form.accountType') }}</legend>
                <div class="plat-choice">
                    @foreach (\App\User::SIGNUP_ROLES as $r)
                        <label class="ui-check">
                            <input class="ui-check-input" type="radio" name="role" value="{{ $r }}" @checked($role === $r)>
                            {{ $u("role.{$r}") }}
                        </label>
                    @endforeach
                </div>
                @error('role') <p class="ui-field-error" role="alert">{{ $message }}</p> @enderror
            </fieldset>
        @endif

        <div class="adm-form-grid">
            <x-field name="name" :label="__('partner.auth.register.firstName')" :value="old('name', $user?->name)" required autocomplete="off" />
            <x-field name="last_name" :label="__('partner.auth.register.lastName')" :value="old('last_name', $user?->last_name)" required autocomplete="off" />
            <x-field name="email" type="email" :label="__('partner.auth.email')" :value="old('email', $user?->email)" required autocomplete="off" wide />
            <x-field name="phone" :label="$u('fact.phone')" :value="old('phone', $user?->phone)" autocomplete="off" />
            <x-field name="company_name" :label="$u('fact.company')" :value="old('company_name', $user?->company_name)" autocomplete="off" />
            @if (! $editing || $user->role === \App\User::RESTAURATEUR)
                <x-field name="vat_number" :label="__('partner.auth.register.vat')" :value="old('vat_number', $user?->vat_number)" :hint="$editing ? null : $u('form.vatHint')" inputmode="numeric" wide />
            @endif
            @unless ($editing)
                <x-field name="password" type="password" :label="__('partner.auth.password')" required autocomplete="new-password" />
                <x-field name="password_confirmation" id="password-confirm" type="password" :label="__('partner.auth.register.confirmPassword')" required autocomplete="new-password" />
            @endunless
        </div>

        <div class="plat-filters-actions">
            <button type="submit" class="ui-btn ui-btn--primary">{{ $editing ? $u('form.save') : $u('form.create') }}</button>
            <a class="ui-btn ui-btn--ghost" href="{{ $editing ? route('platform.users.show', $user) : route('platform.users.index') }}">{{ $u('form.cancel') }}</a>
        </div>
    </form>
@endsection
