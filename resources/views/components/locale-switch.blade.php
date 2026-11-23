{{-- language menu of the partner area: a native <details> (closed by resources/js/app.js on Esc or a click
     outside), each language is a link to the "locale" route. Same classes as the Vue LocaleSwitch. --}}
@php($locales = \App\I18n\Strings::locales())
@php($current = app()->getLocale())
<details {{ $attributes->merge(['class' => 'ui-lang']) }}>
    <summary class="ui-lang-trigger" aria-label="{{ __('site.locale.current', ['name' => $locales[$current]['name']]) }}">
        <x-flag :code="$current" />
        <span>{{ $locales[$current]['short'] }}</span>
        <x-icon class="ui-lang-chevron" name="chevron-down" size="14" />
    </summary>
    <div class="ui-lang-panel">
        <p class="ui-lang-title">{{ __('site.locale.label') }}</p>
        <ul class="ui-lang-list">
            @foreach ($locales as $code => $locale)
                <li>
                    <a class="ui-lang-option {{ $code === $current ? 'ui-lang-option--current' : '' }}" href="{{ route('locale', $code) }}" hreflang="{{ $code }}" lang="{{ $code }}" @if ($code === $current) aria-current="true" @endif>
                        <x-flag :code="$code" :size="24" />
                        <span>{{ $locale['name'] }}</span>
                        <span class="ui-lang-short">{{ $locale['short'] }}</span>
                        @if ($code === $current)
                            <x-icon class="ui-lang-check" name="check" size="16" />
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</details>
