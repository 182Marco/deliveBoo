{{-- KPI tile of the backoffice: label, value, change vs the previous period, definition.
     $change is a ratio (0.08 = +8%); $upIsGood decides the colour of the change (with an arrow, never colour alone). --}}
@props(['label', 'value', 'hint' => null, 'change' => null, 'upIsGood' => true, 'estimate' => false, 'compare' => null])
<article class="plat-kpi">
    <p class="plat-kpi-label">
        {{ $label }}
        @if ($estimate)
            <span class="ui-badge ui-badge--brand">{{ __('platform.charts.estimate') }}</span>
        @endif
    </p>
    <p class="plat-kpi-value ui-price">{{ $value }}</p>
    @if ($change !== null)
        @php($good = $change == 0 ? null : (($change > 0) === $upIsGood))
        <p @class(['plat-delta', 'plat-delta--good' => $good === true, 'plat-delta--bad' => $good === false])>
            <span aria-hidden="true">{{ $change > 0 ? '▲' : ($change < 0 ? '▼' : '■') }}</span>
            {{ ($change > 0 ? '+' : '').number_format($change * 100, 1) }}%
            <span class="ui-sr-only">{{ $compare }}</span>
        </p>
    @elseif ($compare)
        <p class="plat-delta"><span class="plat-delta-vs">{{ __('platform.charts.noComparison') }}</span></p>
    @endif
    @if ($hint)
        <p class="plat-kpi-hint">{{ $hint }}</p>
    @endif
</article>
