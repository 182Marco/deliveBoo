// Charts tab of the platform backoffice: draws every <canvas data-chart="key"> from the aggregated
// series printed by platform/charts.blade.php (#platform-charts). Loaded only by that tab.
import Chart from "chart.js/auto";
import { chartConfig } from "./chartTheme.js";

Chart.defaults.font.family = "'Figtree', system-ui, sans-serif";
Chart.defaults.font.size = 12;

const source = document.getElementById("platform-charts");
const specs = source ? JSON.parse(source.textContent) : {};

document.querySelectorAll("canvas[data-chart]").forEach(canvas => {
    const spec = specs[canvas.dataset.chart];
    if (spec) new Chart(canvas, chartConfig(spec));
});
