{{-- label + input (or textarea) + validation message of the field `name` --}}
@props(['name', 'label', 'type' => 'text', 'value' => null, 'as' => 'input', 'star' => false, 'hint' => null, 'wide' => false])
@php($id = $attributes->get('id', $name))
@php($error = $errors->first($name))
<div class="ui-field {{ $wide ? 'adm-wide' : '' }}">
    <label class="ui-label" for="{{ $id }}">
        {{ $label }}@if ($star) <span class="ui-required" aria-hidden="true">*</span>@endif
    </label>
    @if ($as === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" rows="4" @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif {{ $attributes->except('id')->merge(['class' => 'ui-input ui-textarea' . ($error ? ' ui-input--error' : '')]) }}>{{ $value }}</textarea>
    @else
        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ $value }}" @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif {{ $attributes->except('id')->merge(['class' => 'ui-input' . ($error ? ' ui-input--error' : '')]) }}>
    @endif
    @if ($hint)
        <p class="ui-hint">{{ $hint }}</p>
    @endif
    @if ($error)
        <p id="{{ $id }}-error" class="ui-field-error" role="alert">{{ $error }}</p>
    @endif
</div>
