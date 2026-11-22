// Chart.js configurations of admin/stats/show.blade.php
import { monthsWithIncome } from "./chart.utils.js";

const PALETTE = [
    "255, 99, 132",
    "255, 159, 64",
    "255, 205, 86",
    "75, 192, 192",
    "54, 162, 235",
    "153, 102, 255",
    "201, 203, 207"
];
const EXPENSES_COLORS = Array.from({ length: 17 }, (_, i) => PALETTE[i % PALETTE.length]);
const FANS_COLORS = [3, 4, 5, 0, 1, 2].map(e => PALETTE[e]);

const fills = colors => colors.map(e => `rgba(${e}, 0.2)`);
const borders = colors => colors.map(e => `rgb(${e})`);

const chartOptions = (indexAxis, title, plugins = {}) => ({
    indexAxis,
    responsive: true,
    plugins: {
        ...plugins,
        title: { display: true, text: title, font: { weight: "bold", size: "30" } }
    }
});

// every order plus a last bar with the average
const expensesChart = (orders, average) => ({
    type: "bar",
    data: {
        labels: orders.map(e => e.customer_lastName).concat("AVERAGE"),
        datasets: [
            {
                label: "expense",
                data: orders.map(e => e.price).concat(average),
                backgroundColor: fills(EXPENSES_COLORS),
                borderColor: borders(EXPENSES_COLORS),
                borderWidth: 1
            }
        ]
    },
    options: chartOptions("y", "Expenses of single orders in €")
});

const fansChart = spendthrifts => ({
    type: "bar",
    data: {
        labels: spendthrifts.map(e => e.lastName),
        datasets: [
            {
                label: "expense",
                data: spendthrifts.map(e => e.totalExpenses),
                backgroundColor: fills(FANS_COLORS),
                borderColor: borders(FANS_COLORS),
                borderWidth: 1
            }
        ]
    },
    options: chartOptions("x", "People who bought more than once shown by total expenses")
});

// only the months with some income
const monthlyChart = incomes => {
    const months = monthsWithIncome(incomes);
    return {
        type: "line",
        data: {
            labels: months.map(e => e.month),
            datasets: [{ data: months.map(e => e.income), borderColor: ["#198dcc"] }]
        },
        options: chartOptions("x", "Incomes month by month in €", { legend: { display: false } })
    };
};

const monthVsYearChart = (monthIncome, yearIncome) => ({
    type: "doughnut",
    data: {
        labels: ["current month income", "current year income"],
        datasets: [
            {
                label: "month income",
                data: [monthIncome, yearIncome],
                backgroundColor: ["#198dcc", "#F1C40F96"],
                borderColor: ["#fff"],
                borderWidth: 1
            }
        ]
    },
    options: chartOptions("x", "Current month income compared to annual income")
});

export { expensesChart, fansChart, monthlyChart, monthVsYearChart };
