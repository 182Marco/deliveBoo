{{-- Orders by weekday and hour (App\Analytics\ChartFormat::heatmap): one-hue levels with the counts
     as a table. Used by the platform Charts tab and the partner statistics. --}}
@props(['heatmap', 'title', 'sub'])
<section class="plat-chart plat-chart--wide" aria-labelledby="chart-heatmap-title">
    <header class="plat-chart-head">
        <h2 id="chart-heatmap-title" class="plat-panel-title">{{ $title }}</h2>
        <p class="plat-chart-sub">{{ $sub }}</p>
    </header>
    <div class="plat-heat-scroll">
        <div class="plat-heat-grid" role="img" aria-label="{{ $sub }}">
            <span class="plat-heat-corner"></span>
            @for ($h = 0; $h < 24; $h++)
                <span class="plat-heat-hour">{{ $h % 3 === 0 ? str_pad($h, 2, '0', STR_PAD_LEFT) : '' }}</span>
            @endfor
            @foreach ($heatmap['rows'] as $row)
                <span class="plat-heat-day">{{ $row['day'] }}</span>
                @foreach ($row['cells'] as $h => $cell)
                    <span class="plat-heat plat-heat--{{ $cell['level'] }}" title="{{ __('platform.charts.heatmap.cell', ['day' => $row['day'], 'hour' => str_pad($h, 2, '0', STR_PAD_LEFT), 'count' => $cell['count']]) }}"></span>
                @endforeach
            @endforeach
        </div>
    </div>
    <div class="plat-heat-legend" aria-hidden="true">
        {{ __('platform.charts.heatmap.less') }}
        @for ($l = 0; $l <= 5; $l++)
            <span class="plat-heat plat-heat-key plat-heat--{{ $l }}"></span>
        @endfor
        {{ __('platform.charts.heatmap.more') }}
    </div>
    <details class="plat-data">
        <summary class="plat-data-summary">{{ __('platform.charts.table') }}</summary>
        <div class="ui-table-wrap plat-data-table">
            <table class="ui-table">
                <thead><tr><th class="ui-th"></th>@for ($h = 0; $h < 24; $h++)<th class="ui-th ui-th--num">{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}</th>@endfor</tr></thead>
                <tbody>
                    @foreach ($heatmap['rows'] as $row)
                        <tr><th class="ui-th" scope="row">{{ $row['day'] }}</th>@foreach ($row['cells'] as $cell)<td class="ui-td ui-td--num">{{ $cell['count'] }}</td>@endforeach</tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </details>
</section>
