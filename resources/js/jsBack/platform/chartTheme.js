// Look of the backoffice charts (skill dataviz): thin marks, recessive grid, one axis, legend from
// two series. Colours from resources/css/tokens.css; the categorical order was checked with the
// dataviz validator (light surface, CVD separation): Foodsy orange, slate blue, olive.
const SERIES = ["#d0682c", "#2f6f9f", "#6b8e23"];
const MUTED = "#6e675e"; // --ink-muted: the previous period, de-emphasised
const INK = "#1c1a17";
const INK_SOFT = "#4a453e";
const GRID = "#e8e1d6"; // --line
const SURFACE = "#ffffff";

const moneyFormat = new Intl.NumberFormat("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const numberFormat = new Intl.NumberFormat("en-US");

// €12.00 like formatPrice on the site; null = no value (an empty bucket of a ratio)
const formatMoney = value => (value === null || value === undefined ? "–" : `€${moneyFormat.format(value)}`);

// axis ticks: in full under 10,000, compact above (€12K)
const formatTick = (value, money) => {
    const abs = Math.abs(value);
    const text = abs >= 10000 ? `${numberFormat.format(Math.round(value / 100) / 10)}K` : numberFormat.format(value);
    return money ? `€${text}` : text;
};

const withAlpha = (hex, alpha) => {
    const n = parseInt(hex.slice(1), 16);
    return `rgba(${(n >> 16) & 255}, ${(n >> 8) & 255}, ${n & 255}, ${alpha})`;
};

// colour by position in the chart (fixed order, never cycled), the "muted" one is grey
const seriesColor = (dataset, index) => (dataset.muted ? MUTED : SERIES[index] ?? MUTED);

const dataset = (spec, ds, index) => {
    const color = seriesColor(ds, index);
    const single = spec.datasets.length === 1;
    if (spec.type === "line") {
        return {
            label: ds.label,
            data: ds.data,
            borderColor: color,
            backgroundColor: single ? withAlpha(color, 0.1) : color,
            fill: single,
            borderWidth: 2,
            tension: 0.25,
            pointRadius: 0,
            pointHoverRadius: 5,
            pointHoverBorderWidth: 2,
            pointHoverBorderColor: SURFACE,
            pointHoverBackgroundColor: color,
            spanGaps: true
        };
    }
    return {
        label: ds.label,
        data: ds.data,
        backgroundColor: color,
        hoverBackgroundColor: withAlpha(color, 0.8),
        maxBarThickness: 24,
        borderRadius: spec.stacked ? 0 : 4,
        borderSkipped: "start",
        // the 2px surface gap between stacked segments
        borderColor: SURFACE,
        borderWidth: spec.stacked ? { top: 2 } : 0
    };
};

const chartConfig = spec => {
    const horizontal = !!spec.horizontal;
    const valueAxis = horizontal ? "x" : "y";
    const categoryAxis = horizontal ? "y" : "x";
    const format = value => (spec.money ? formatMoney(value) : value === null ? "–" : numberFormat.format(value));
    return {
        type: spec.type,
        data: { labels: spec.labels, datasets: spec.datasets.map((ds, i) => dataset(spec, ds, i)) },
        options: {
            indexAxis: horizontal ? "y" : "x",
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            // the whole column / the nearest date answers, not only the painted pixels
            interaction: { mode: "index", intersect: false, axis: horizontal ? "y" : "x" },
            scales: {
                [valueAxis]: {
                    beginAtZero: true,
                    stacked: !!spec.stacked,
                    grid: { color: GRID, drawBorder: false, drawTicks: false },
                    ticks: { color: INK_SOFT, padding: 8, maxTicksLimit: 6, callback: value => formatTick(value, spec.money) }
                },
                [categoryAxis]: {
                    stacked: !!spec.stacked,
                    grid: { display: false, drawBorder: false },
                    ticks: { color: INK_SOFT, autoSkip: true, maxRotation: 0, autoSkipPadding: 12 }
                }
            },
            plugins: {
                // a legend from two series; one series is named by the card title
                legend: {
                    display: spec.datasets.length > 1,
                    position: "top",
                    align: "start",
                    labels: { color: INK, boxWidth: 14, boxHeight: spec.type === "line" ? 2 : 10, padding: 16 }
                },
                tooltip: {
                    backgroundColor: INK,
                    padding: 10,
                    cornerRadius: 8,
                    titleColor: "#ffffff",
                    bodyColor: "#ffffff",
                    displayColors: true,
                    boxWidth: 10,
                    boxHeight: spec.type === "line" ? 2 : 10,
                    callbacks: {
                        // values lead, labels follow
                        label: item => ` ${format(item.raw)}  ${item.dataset.label}`
                    }
                }
            }
        }
    };
};

export { SERIES, MUTED, chartConfig, formatMoney, formatTick, seriesColor, withAlpha };
