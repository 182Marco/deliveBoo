// Pure statistics on the orders of a restaurant (admin/stats/show.blade.php).
// Quirks of the first version are kept on purpose and marked "as before".
const MONTHS = 12;

// "January".."December" in the language of the page (Intl, no strings to translate)
const monthName = (index, locale) => {
    const name = new Intl.DateTimeFormat(locale, { month: "long" }).format(new Date(2000, index, 1));
    return name.charAt(0).toUpperCase() + name.slice(1);
};

const sum = list => list.reduce((acc, cur) => acc + cur, 0);
const unique = list => list.filter((e, i, a) => a.indexOf(e) === i);
const sumPrices = orders => sum(orders.map(e => e.price));

const averageExpense = orders => (sumPrices(orders) / orders.length).toFixed(2);

// the phone number identifies a customer
const ordersOf = (orders, phone) => orders.filter(e => e.customer_phone == phone);

const repeatedPhones = orders =>
    unique(orders.map(e => e.customer_phone).filter((e, i, a) => a.indexOf(e) !== i));

// customers with more than one order. As before, names and last names are deduplicated
// by value and paired by position: two customers with the same name shift the pairs.
const spendthriftsOf = orders => {
    const phones = repeatedPhones(orders);
    const valuesOnce = field => unique(phones.flatMap(phone => ordersOf(orders, phone).map(e => e[field])));
    const names = valuesOnce("customer_name");
    const lastNames = valuesOnce("customer_lastName");
    return phones.map((phone, i) => ({
        name: names[i],
        lastName: lastNames[i],
        purchaseTimes: ordersOf(orders, phone).length,
        totalExpenses: sumPrices(ordersOf(orders, phone))
    }));
};

// as before the search starts from the second customer:
// with fewer than two it throws and the rest of the page is not drawn
const biggestSpendthrift = spendthrifts =>
    spendthrifts.reduce((acc, cur) => (cur.totalExpenses > acc.totalExpenses ? cur : acc), spendthrifts[1]);

// as before invalid dates (NaN) count as January; months of different years are summed together
const monthOf = order => new Date(order.created_at).getMonth() || 0;

const incomeByMonth = orders => Array.from({ length: MONTHS }, (_, i) => sumPrices(orders.filter(e => monthOf(e) === i)));

const monthsWithIncome = (incomes, locale = "en") =>
    incomes.map((e, i) => ({ month: monthName(i, locale), income: e })).filter(e => e.income > 0);

// as before (a switch without `break` from July on): from July to December it is December's income
const currentMonthIncome = (incomes, monthIndex) => incomes[monthIndex < 6 ? monthIndex : 11];

const incomeShare = (part, whole) => ((part * 100) / whole).toFixed(2);

export {
    sum,
    averageExpense,
    spendthriftsOf,
    biggestSpendthrift,
    incomeByMonth,
    monthsWithIncome,
    currentMonthIncome,
    incomeShare
};
