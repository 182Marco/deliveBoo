// Chart.js configurations of admin/stats/show.blade.php; `text` holds the labels in the page language
// (partner.stats.chart of resources/js/utils/strings.json, printed by the view)
import { monthsWithIncome } from "./chart.utils.js";

// Foodsy palette (resources/css/tokens.css): brand, saffron, olive, rosemary, tomato, saffron ink, paprika
const PALETTE = [
    "208, 104, 44",
    "229, 161, 58",
    "86, 104, 43",
    "31, 43, 35",
    "196, 55, 44",
    "122, 80, 0",
    "168, 80, 28"
];
const BRAND = "rgb(208, 104, 44)";
const SAFFRON = "rgba(229, 161, 58, 0.55)";
const EXPENSES_COLORS = Array.from({ length: 17 }, (_, i) => PALETTE[i % PALETTE.length]);
const FANS_COLORS = [3, 4, 5, 0, 1, 2].map(e => PALETTE[e]);

const fills = colors => colors.map(e => `rgba(${e}, 0.75)`);
const borders = colors => colors.map(e => `rgb(${e})`);

const chartOptions = (indexAxis, title, plugins = {}) => ({
    indexAxis,
    responsive: true,
    plugins: {
        ...plugins,
        title: { display: true, text: title, align: "start", font: { family: "'Bricolage Grotesque', system-ui, sans-serif", weight: "800", size: 18 }, padding: { bottom: 16 } }
    }
});

// every order plus a last bar with the average
const expensesChart = (orders, average, text) => ({
    type: "bar",
    data: {
        labels: orders.map(e => e.customer_lastName).concat(text.average),
        datasets: [
            {
                label: text.spent,
                data: orders.map(e => e.price).concat(average),
                backgroundColor: fills(EXPENSES_COLORS),
                borderColor: borders(EXPENSES_COLORS),
                borderWidth: 1
            }
        ]
    },
    options: chartOptions("y", text.eachOrder)
});

const fansChart = (spendthrifts, text) => ({
    type: "bar",
    data: {
        labels: spendthrifts.map(e => e.lastName),
        datasets: [
            {
                label: text.spent,
                data: spendthrifts.map(e => e.totalExpenses),
                backgroundColor: fills(FANS_COLORS),
                borderColor: borders(FANS_COLORS),
                borderWidth: 1
            }
        ]
    },
    options: chartOptions("x", text.repeatGuests)
});

// only the months with some income
const monthlyChart = (incomes, text, locale) => {
    const months = monthsWithIncome(incomes, locale);
    return {
        type: "line",
        data: {
            labels: months.map(e => e.month),
            datasets: [{ data: months.map(e => e.income), borderColor: BRAND, backgroundColor: BRAND, tension: 0.35 }]
        },
        options: chartOptions("x", text.byMonth, { legend: { display: false } })
    };
};

const monthVsYearChart = (monthIncome, yearIncome, text) => ({
    type: "doughnut",
    data: {
        labels: [text.thisMonth, text.thisYear],
        datasets: [
            {
                label: text.income,
                data: [monthIncome, yearIncome],
                backgroundColor: [BRAND, SAFFRON],
                borderColor: ["#fff"],
                borderWidth: 1
            }
        ]
    },
    options: chartOptions("x", text.monthVsYear)
});

export { expensesChart, fansChart, monthlyChart, monthVsYearChart };
