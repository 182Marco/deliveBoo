{{-- One chart of the Charts tab: title, definition, the canvas drawn by jsBack/platform/charts.js
     and the same numbers as a table (accessible without the chart, without hovering). --}}
@props(['chart', 'title', 'sub' => null, 'wide' => false, 'tall' => false])
<section @class(['plat-chart', 'plat-chart--wide' => $wide]) aria-labelledby="chart-{{ $chart }}-title">
    <header class="plat-chart-head">
        <h2 id="chart-{{ $chart }}-title" class="plat-panel-title">{{ $title }}</h2>
        @if ($sub)
            <p class="plat-chart-sub">{{ $sub }}</p>
        @endif
    </header>
    <div @class(['plat-chart-canvas', 'plat-chart-canvas--tall' => $tall])>
        <canvas data-chart="{{ $chart }}" role="img" aria-label="{{ $title }}"></canvas>
    </div>
    @isset($table)
        <details class="plat-data">
            <summary class="plat-data-summary">{{ __('platform.charts.table') }}</summary>
            <div class="ui-table-wrap plat-data-table">
                {{ $table }}
            </div>
        </details>
    @endisset
</section>
