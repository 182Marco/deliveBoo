// Look of the backoffice charts (resources/js/jsBack/platform/chartTheme.js): formats and the
// mark rules of the dataviz skill (thin bars, 2px lines, legend only from two series, fixed colours).
import { describe, it, expect } from "vitest";
import { SERIES, MUTED, chartConfig, formatMoney, formatTick, withAlpha } from "../../resources/js/jsBack/platform/chartTheme.js";

const line = (datasets, extra = {}) => ({ type: "line", labels: ["a", "b"], datasets, ...extra });

describe("formats", () => {
    it("money like the site (€12.00) and a dash for no value", () => {
        expect(formatMoney(203739)).toBe("€203,739.00");
        expect(formatMoney(2.5)).toBe("€2.50");
        expect(formatMoney(null)).toBe("–");
    });

    it("axis ticks in full under 10,000, compact above", () => {
        expect(formatTick(2500, true)).toBe("€2,500");
        expect(formatTick(12000, true)).toBe("€12K");
        expect(formatTick(15500, false)).toBe("15.5K");
    });

    it("hex to rgba", () => {
        expect(withAlpha("#d0682c", 0.1)).toBe("rgba(208, 104, 44, 0.1)");
    });
});

describe("chartConfig", () => {
    it("a single line: no legend box, 2px line, 10% area wash in the first colour", () => {
        const config = chartConfig(line([{ label: "Gross", data: [1, 2] }], { money: true }));
        const ds = config.data.datasets[0];
        expect(config.options.plugins.legend.display).toBe(false);
        expect(ds.borderWidth).toBe(2);
        expect(ds.borderColor).toBe(SERIES[0]);
        expect(ds.fill).toBe(true);
        expect(ds.backgroundColor).toBe(withAlpha(SERIES[0], 0.1));
        expect(config.options.scales.y.ticks.callback(12000)).toBe("€12K");
    });

    it("two series: legend, fixed order, the previous period in grey", () => {
        const config = chartConfig(line([{ label: "Now", data: [1] }, { label: "Before", data: [2], muted: true }]));
        expect(config.options.plugins.legend.display).toBe(true);
        expect(config.data.datasets.map(d => d.borderColor)).toEqual([SERIES[0], MUTED]);
    });

    it("bars: at most 24px, rounded data end, one value axis from zero", () => {
        const config = chartConfig({ type: "bar", horizontal: true, labels: ["x"], datasets: [{ label: "Gross", data: [5] }] });
        const ds = config.data.datasets[0];
        expect(ds.maxBarThickness).toBe(24);
        expect(ds.borderRadius).toBe(4);
        expect(config.options.indexAxis).toBe("y");
        expect(config.options.scales.x.beginAtZero).toBe(true);
        expect(Object.keys(config.options.scales)).toEqual(["x", "y"]);
    });

    it("stacked bars get the 2px surface gap and keep the colour of each series", () => {
        const config = chartConfig({ type: "bar", stacked: true, labels: ["x"], datasets: [{ label: "New", data: [1] }, { label: "Returning", data: [2] }] });
        expect(config.data.datasets.map(d => d.backgroundColor)).toEqual([SERIES[0], SERIES[1]]);
        expect(config.data.datasets[0].borderWidth).toEqual({ top: 2 });
        expect(config.options.scales.y.stacked).toBe(true);
    });

    it("the tooltip puts the value first", () => {
        const config = chartConfig(line([{ label: "Gross", data: [1] }], { money: true }));
        expect(config.options.plugins.tooltip.callbacks.label({ raw: 1234.5, dataset: { label: "Gross" } })).toBe(" €1,234.50  Gross");
    });
});
