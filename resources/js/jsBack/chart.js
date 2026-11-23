// Statistics page of a restaurant: texts and charts from the orders of the view.
/* global relatedOrders, chartText */ // consts of the inline script in admin/stats/show.blade.php
import Chart from "chart.js/auto";
import { expensesChart, fansChart, monthlyChart, monthVsYearChart } from "./chart.config.js";
import {
    averageExpense,
    biggestSpendthrift,
    currentMonthIncome,
    incomeByMonth,
    incomeShare,
    spendthriftsOf,
    sum
} from "./chart.utils.js";

// type and colours of the partner area (resources/css/tokens.css)
Chart.defaults.font.family = "'Figtree', system-ui, sans-serif";
Chart.defaults.color = "#4a453e";
Chart.defaults.borderColor = "#e8e1d6";

const setText = (selector, text) => {
    document.querySelector(selector).textContent = text;
};

const drawChart = (selector, config) => new Chart(document.querySelector(selector).getContext("2d"), config);

const orders = relatedOrders;
// month names follow the page language (<html lang>, set by app/Http/Middleware/SetLocale.php)
const locale = document.documentElement.lang || "en";

// every order and the average
const average = averageExpense(orders);
setText("#txtAverageEl", `€${average}`);
drawChart("#expensessChart", expensesChart(orders, average, chartText));

// customers who bought more than once
const spendthrifts = spendthriftsOf(orders);
const champion = biggestSpendthrift(spendthrifts);
setText("#biggestFan", `${champion.name} ${champion.lastName}`);
setText("#purchaseTimes", `${champion.purchaseTimes}`);
setText("#biggestFanExpense", `€${champion.totalExpenses.toFixed(2)}`);
drawChart("#fansChart", fansChart(spendthrifts, chartText));

// income month by month and current month against the whole year
const incomes = incomeByMonth(orders);
drawChart("#incomeByMonthPoints", monthlyChart(incomes, chartText, locale));

const monthIncome = currentMonthIncome(incomes, new Date().getMonth());
const yearIncome = sum(incomes);
setText("#monthIncomeEL", `€${monthIncome.toFixed(2)}`);
setText("#monthYearEL", `€${yearIncome.toFixed(2)}`);
setText("#incomePercEl", `${incomeShare(monthIncome, yearIncome)}%`);
drawChart("#incomeMonth", monthVsYearChart(monthIncome, yearIncome, chartText));
