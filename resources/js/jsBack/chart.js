import Chart, { LogarithmicScale } from "chart.js/auto";

const ordersLastName = relatedOrders.map(e => e.customer_lastName);
const expenses = relatedOrders.map(e => e.price);

const average = (
    expenses.reduce((acc, n) => acc + n, 0) / expenses.length
).toFixed(2);
document.querySelector("#txtAverageEl").innerText = `${average} €`;

const expensessChart = document
    .querySelector("#expensessChart")
    .getContext("2d");

// ADDING THE AVERAGE AS LABEL IN THE ARRAY OF NAMES
const pricesData = expenses.concat(average);
// ADDING THE AVERAGE AS NUMBER IN THE ARRAY OF NUMBERS
const labelsName = ordersLastName.concat("AVERAGE");

const pricesChart = new Chart(expensessChart, {
    type: "bar", //bar, horizontalBar, pie, line, doughtnut, radar, polarArea
    data: {
        labels: labelsName,
        datasets: [
            {
                label: "expense",
                data: pricesData,
                backgroundColor: [
                    "rgba(255, 99, 132, 0.2)",
                    "rgba(255, 159, 64, 0.2)",
                    "rgba(255, 205, 86, 0.2)",
                    "rgba(75, 192, 192, 0.2)",
                    "rgba(54, 162, 235, 0.2)",
                    "rgba(153, 102, 255, 0.2)",
                    "rgba(201, 203, 207, 0.2)",
                    "rgba(255, 99, 132, 0.2)",
                    "rgba(255, 159, 64, 0.2)",
                    "rgba(255, 205, 86, 0.2)",
                    "rgba(75, 192, 192, 0.2)",
                    "rgba(54, 162, 235, 0.2)",
                    "rgba(153, 102, 255, 0.2)",
                    "rgba(201, 203, 207, 0.2)",
                    "rgba(255, 99, 132, 0.2)",
                    "rgba(255, 159, 64, 0.2)",
                    "rgba(255, 205, 86, 0.2)",
                    
                ],
                borderColor: [
                    "rgb(255, 99, 132)",
                    "rgb(255, 159, 64)",
                    "rgb(255, 205, 86)",
                    "rgb(75, 192, 192)",
                    "rgb(54, 162, 235)",
                    "rgb(153, 102, 255)",
                    "rgb(201, 203, 207)",
                    "rgb(255, 99, 132)",
                    "rgb(255, 159, 64)",
                    "rgb(255, 205, 86)",
                    "rgb(75, 192, 192)",
                    "rgb(54, 162, 235)",
                    "rgb(153, 102, 255)",
                    "rgb(201, 203, 207)",
                    "rgb(255, 99, 132)",
                    "rgb(255, 159, 64)",
                    "rgb(255, 205, 86)",
                    
                ],
                borderWidth: 1
            }
        ]
    },
    options: {
        indexAxis: "y",
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: "Expenses of single orders in €",
                font: {
                    weight: "bold",
                    size: "30"
                }
            }
        }
    }
});

// getting data for nexts graphs
const onlyPhonesInOrders = relatedOrders.map(e => e.customer_phone);
// get duplicate strating from something that is unique in population -> phone number
const onlyDuplicatePhones = onlyPhonesInOrders.filter(
    (e, i, a) => a.indexOf(e) !== i
);
// have phones just once
const arDuplicatePhonesOnce = onlyDuplicatePhones.filter(
    (e, i, a) => a.indexOf(e) === i
);

// expense amount of people that has purchase more than once
const arOfExpenseMorePurchases = [];

arDuplicatePhonesOnce.forEach(n => {
    if (relatedOrders.filter(e => e.customer_phone == n).length) {
        const duplicateOrdersAr = relatedOrders.filter(
            e => e.customer_phone == n
        );
        arOfExpenseMorePurchases.push(arrraySum(duplicateOrdersAr));
    }
});

function arrraySum(a) {
    let sum = 0;
    a.forEach(e => (sum += e.price));
    return sum;
}
// ****************************************
// get array other array for purchase time
const spendthriftsPurchasesTimes = [];

arDuplicatePhonesOnce.forEach(e => {
    let count = 0;
    relatedOrders.forEach(obj => (obj.customer_phone == e ? count++ : null));
    spendthriftsPurchasesTimes.push(count);
});
// *****************************

// spendthrifts names
let names = [];
arDuplicatePhonesOnce.forEach(e => {
    relatedOrders.forEach(el =>
        e == el.customer_phone ? names.push(el.customer_name) : null
    );
});
// get the name once
names = [...names.filter((e, i, a) => a.indexOf(e) === i)];

// spendthrifts lastNames
let lastNames = [];
arDuplicatePhonesOnce.forEach(e => {
    relatedOrders.forEach(el =>
        e == el.customer_phone ? lastNames.push(el.customer_lastName) : null
    );
});
// get the lastNames once
lastNames = [...lastNames.filter((e, i, a) => a.indexOf(e) === i)];

//  Array of obj of who has purchase more than once
// {name: Lorem , expense: number, timeOfPurchase:number}
const spendthrifts = arDuplicatePhonesOnce.map((e, i) => ({
    name: names[i],
    lastName: lastNames[i],
    puchaseTimes: spendthriftsPurchasesTimes[i],
    totalExpenses: arOfExpenseMorePurchases[i]
}));

// get the spendthrift champion
let biggestSpendthrift = spendthrifts[1];

spendthrifts.forEach(e =>
    e.totalExpenses > biggestSpendthrift.totalExpenses
        ? (biggestSpendthrift = e)
        : null
);

// references to display written data
const biggestFanEl = document.querySelector("#biggestFan");
const purchaseTimesEl = document.querySelector("#purchaseTimes");
const biggestFanExpenseEl = document.querySelector("#biggestFanExpense");

biggestFanEl.innerHTML = `${biggestSpendthrift.name} ${biggestSpendthrift.lastName}`;
purchaseTimesEl.innerHTML = `${biggestSpendthrift.puchaseTimes}`;
biggestFanExpenseEl.innerHTML = `${biggestSpendthrift.totalExpenses.toFixed(
    2
)} €`;

// CHART
const fansChartEl = document.querySelector("#fansChart").getContext("2d");

// spendthrifts.lastName from array of obj to array
const spendthriftsLastName = spendthrifts.map(e => e.lastName);
// spendthriftsTotalExpenses from array of obj to array
const spendthriftsTotalExpenses = spendthrifts.map(e => e.totalExpenses);

const companyFansChart = new Chart(fansChartEl, {
    type: "bar", //bar, horizontalBar, pie, line, doughtnut, radar, polarArea
    data: {
        labels: spendthriftsLastName,
        datasets: [
            {
                label: "expense",
                data: spendthriftsTotalExpenses,
                backgroundColor: [
                    "rgba(75, 192, 192, 0.2)",
                    "rgba(54, 162, 235, 0.2)",
                    "rgba(153, 102, 255, 0.2)",
                    "rgba(255, 99, 132, 0.2)",
                    "rgba(255, 159, 64, 0.2)",
                    "rgba(255, 205, 86, 0.2)"
                ],
                borderColor: [
                    "rgb(75, 192, 192)",
                    "rgb(54, 162, 235)",
                    "rgb(153, 102, 255)",
                    "rgb(255, 99, 132)",
                    "rgb(255, 159, 64)",
                    "rgb(255, 205, 86)"
                ],
                borderWidth: 1
            }
        ]
    },
    options: {
        indexAxis: "x",
        responsive: true,
        plugins: {
            title: {
                display: true,
                text:
                    "People who bought more than once shown by total expenses",
                font: {
                    weight: "bold",
                    size: "30"
                }
            }
        }
    }
});

// devide all orders in array by mounth
let gen = [];
let feb = [];
let mar = [];
let apri = [];
let may = [];
let june = [];
let july = [];
let aug = [];
let set = [];
let ott = [];
let nov = [];
let dec = [];

relatedOrders.forEach(e =>
    new Date(e.created_at).getMonth() ? null : gen.push(e.price)
);
relatedOrders.forEach(e =>
    new Date(e.created_at).getMonth() == 1 ? feb.push(e.price) : null
);
relatedOrders.forEach(e =>
    new Date(e.created_at).getMonth() == 2 ? mar.push(e.price) : null
);
relatedOrders.forEach(e =>
    new Date(e.created_at).getMonth() == 3 ? apri.push(e.price) : null
);
relatedOrders.forEach(e =>
    new Date(e.created_at).getMonth() == 4 ? may.push(e.price) : null
);
relatedOrders.forEach(e =>
    new Date(e.created_at).getMonth() == 5 ? june.push(e.price) : null
);
relatedOrders.forEach(e =>
    new Date(e.created_at).getMonth() == 6 ? july.push(e.price) : null
);
relatedOrders.forEach(e =>
    new Date(e.created_at).getMonth() == 7 ? aug.push(e.price) : null
);
relatedOrders.forEach(e =>
    new Date(e.created_at).getMonth() == 8 ? set.push(e.price) : null
);
relatedOrders.forEach(e =>
    new Date(e.created_at).getMonth() == 9 ? ott.push(e.price) : null
);
relatedOrders.forEach(e =>
    new Date(e.created_at).getMonth() == 10 ? nov.push(e.price) : null
);
relatedOrders.forEach(e =>
    new Date(e.created_at).getMonth() == 11 ? dec.push(e.price) : null
);

gen = gen.reduce((acc, curr) => acc + curr, 0);
feb = feb.reduce((acc, curr) => acc + curr, 0);
mar = mar.reduce((acc, curr) => acc + curr, 0);
apri = apri.reduce((acc, curr) => acc + curr, 0);
may = may.reduce((acc, curr) => acc + curr, 0);
june = june.reduce((acc, curr) => acc + curr, 0);
july = july.reduce((acc, curr) => acc + curr, 0);
aug = aug.reduce((acc, curr) => acc + curr, 0);
set = set.reduce((acc, curr) => acc + curr, 0);
ott = ott.reduce((acc, curr) => acc + curr, 0);
nov = nov.reduce((acc, curr) => acc + curr, 0);
dec = dec.reduce((acc, curr) => acc + curr, 0);

let incomeMonth = [];
let month = [];

if (gen > 0) {
    incomeMonth.push(gen);
    month.push("January");
}
if (feb > 0) {
    incomeMonth.push(feb);
    month.push("February");
}
if (mar > 0) {
    incomeMonth.push(mar);
    month.push("March");
}
if (apri > 0) {
    incomeMonth.push(apri);
    month.push("April");
}
if (may > 0) {
    incomeMonth.push(may);
    month.push("May");
}
if (june > 0) {
    incomeMonth.push(june);
    month.push("June");
}
if (july > 0) {
    incomeMonth.push(july);
    month.push("July");
}
if (aug > 0) {
    incomeMonth.push(aug);
    month.push("August");
}
if (set > 0) {
    incomeMonth.push(set);
    month.push("September");
}
if (ott > 0) {
    incomeMonth.push(ott);
    month.push("October");
}
if (nov > 0) {
    incomeMonth.push(nov);
    month.push("November");
}
if (dec > 0) {
    incomeMonth.push(dec);
    month.push("December");
}

// CHART montly income by points
const incomeByMonthPointsEl = document
    .querySelector("#incomeByMonthPoints")
    .getContext("2d");

const incomeByMonthWithPoints = new Chart(incomeByMonthPointsEl, {
    type: "line", //bar, horizontalBar, pie, line, doughtnut, radar, polarArea
    data: {
        labels: month,
        datasets: [
            {
                data: incomeMonth,
                borderColor: ["#198dcc"]
            }
        ]
    },
    options: {
        indexAxis: "x",
        responsive: true,
        plugins: {
            legend: {
                display: false
            },
            title: {
                display: true,
                text: "Incomes month by month in €",
                font: {
                    weight: "bold",
                    size: "30"
                }
            }
        }
    }
});

// const newDate = ;
let currentMonth = new Date().getMonth();
currentMonth++;

let currentMonthIncome = 0;

switch (currentMonth) {
    case 1:
        currentMonthIncome = gen;
        break;
    case 2:
        currentMonthIncome = feb;
        break;
    case 3:
        currentMonthIncome = mar;
        break;
    case 4:
        currentMonthIncome = apri;
        break;
    case 5:
        currentMonthIncome = may;
        break;
    case 6:
        currentMonthIncome = june;
        break;
    case 7:
        currentMonthIncome = july;
    case 8:
        currentMonthIncome = aug;
    case 9:
        currentMonthIncome = set;
    case 10:
        currentMonthIncome = ott;
    case 11:
        currentMonthIncome = nov;
    case 12:
        currentMonthIncome = dec;
}

const annualIncome =
    gen + feb + mar + apri + may + june + july + aug + set + ott + nov + dec;

// resul of proportion month income year income in percentage
const x = ((currentMonthIncome * 100) / annualIncome).toFixed(2);

// references to display written data
const monthIncomeEL = document.querySelector("#monthIncomeEL");
const monthYearEL = document.querySelector("#monthYearEL");
const incomePercEl = document.querySelector("#incomePercEl");

monthIncomeEL.innerText = `${currentMonthIncome.toFixed(2)} €`;
monthYearEL.innerText = `${annualIncome.toFixed(2)} €`;
incomePercEl.innerText = `${x} %`;

// CHART
const incomeMonthEl = document.querySelector("#incomeMonth").getContext("2d");

const incomeByMonth = new Chart(incomeMonthEl, {
    type: "doughnut", //bar, horizontalBar, pie, line, doughtnut, radar, polarArea
    data: {
        labels: ["current month income", "current year income"],
        datasets: [
            {
                label: "month income",
                data: [currentMonthIncome, annualIncome],
                backgroundColor: ["#198dcc", "#F1C40F96"],
                borderColor: ["#fff"],
                borderWidth: 1
            }
        ]
    },
    options: {
        indexAxis: "x",
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: "Current month income compared to annual income",
                font: {
                    weight: "bold",
                    size: "30"
                }
            }
        }
    }
});
