import Chart from "chart.js/auto";

const ordersLastName = relatedOrders.map(e => e.customer_lastName);
const expenses = relatedOrders.map(e => e.price);

const average = (
    expenses.reduce((acc, n) => acc + n, 0) / expenses.length
).toFixed(2);
document.querySelector("#txtAverageEl").innerText = average;

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
                    "#dfdfdf"
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
                    "#000"
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
