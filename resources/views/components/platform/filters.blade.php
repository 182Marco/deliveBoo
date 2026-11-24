{{-- Shared filters of Maps, Charts and Tables (App\Analytics\Period): a plain GET form, so every
     filtered view has its own URL. Values outside the whitelists are ignored by the server.
     :places="false" (partner statistics) keeps only the dates: the scope is the partner's restaurants. --}}
@props(['period', 'keep' => [], 'places' => true])
<form class="plat-filters" method="GET" action="{{ url()->current() }}" aria-label="{{ __('platform.filters.label') }}">
    @foreach ($keep as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endforeach
    <div class="plat-filters-fields">
        <div class="ui-field">
            <label class="ui-label" for="f-from">{{ __('platform.filters.from') }}</label>
            <input class="ui-input" id="f-from" type="date" name="from" value="{{ $period->from->toDateString() }}" max="{{ now()->toDateString() }}">
        </div>
        <div class="ui-field">
            <label class="ui-label" for="f-to">{{ __('platform.filters.to') }}</label>
            <input class="ui-input" id="f-to" type="date" name="to" value="{{ $period->to->toDateString() }}" max="{{ now()->toDateString() }}">
        </div>
        @if ($places)
        <div class="ui-field">
            <label class="ui-label" for="f-city">{{ __('platform.filters.city') }}</label>
            <select class="ui-input" id="f-city" name="city">
                <option value="">{{ __('platform.filters.allCities') }}</option>
                @foreach (\App\Analytics\Period::cities() as $city)
                    <option value="{{ $city }}" @selected($period->city === $city)>{{ $city }}</option>
                @endforeach
            </select>
        </div>
        <div class="ui-field">
            <label class="ui-label" for="f-type">{{ __('platform.filters.cuisine') }}</label>
            <select class="ui-input" id="f-type" name="type">
                <option value="">{{ __('platform.filters.allCuisines') }}</option>
                @foreach (\App\Analytics\Period::types() as $id => $name)
                    <option value="{{ $id }}" @selected($period->typeId === $id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="plat-filters-actions">
            <button type="submit" class="ui-btn ui-btn--primary">{{ __('platform.filters.apply') }}</button>
            <a class="ui-btn ui-btn--ghost" href="{{ url()->current().($keep ? '?'.http_build_query($keep) : '') }}">{{ __('platform.filters.reset') }}</a>
        </div>
    </div>
    <div class="plat-presets">
        <span class="plat-presets-label">{{ __('platform.filters.presets') }}</span>
        @foreach (\App\Analytics\Period::presets($period->scoped() ? $period : null) as $key => $range)
            @php($current = $period->from->toDateString() === $range['from'] && $period->to->toDateString() === $range['to'])
            <a
                @class(['ui-chip', 'plat-preset', 'ui-chip--active' => $current])
                href="{{ url()->current().'?'.http_build_query([...$keep, ...$period->query(), ...$range]) }}"
                @if ($current) aria-current="true" @endif
            >{{ __("platform.filters.preset.{$key}") }}</a>
        @endforeach
    </div>
</form>
