// Statistics page of a restaurant: texts and charts from the orders of the view.
/* global relatedOrders */ // const of the inline script in admin/stats/show.blade.php
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

const setText = (selector, text) => {
    document.querySelector(selector).textContent = text;
};

const drawChart = (selector, config) => new Chart(document.querySelector(selector).getContext("2d"), config);

const orders = relatedOrders;

// every order and the average
const average = averageExpense(orders);
setText("#txtAverageEl", `${average} €`);
drawChart("#expensessChart", expensesChart(orders, average));

// customers who bought more than once
const spendthrifts = spendthriftsOf(orders);
const champion = biggestSpendthrift(spendthrifts);
setText("#biggestFan", `${champion.name} ${champion.lastName}`);
setText("#purchaseTimes", `${champion.purchaseTimes}`);
setText("#biggestFanExpense", `${champion.totalExpenses.toFixed(2)} €`);
drawChart("#fansChart", fansChart(spendthrifts));

// income month by month and current month against the whole year
const incomes = incomeByMonth(orders);
drawChart("#incomeByMonthPoints", monthlyChart(incomes));

const monthIncome = currentMonthIncome(incomes, new Date().getMonth());
const yearIncome = sum(incomes);
setText("#monthIncomeEL", `${monthIncome.toFixed(2)} €`);
setText("#monthYearEL", `${yearIncome.toFixed(2)} €`);
setText("#incomePercEl", `${incomeShare(monthIncome, yearIncome)} %`);
drawChart("#incomeMonth", monthVsYearChart(monthIncome, yearIncome));
