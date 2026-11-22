// the API sends the average as a decimal string: stars want an integer, rounded up
const withStarsAverage = restaurants =>
    restaurants.map(e => ({ ...e, average: Math.ceil(parseFloat(e.average)) }));

// position in the ranking as a two-digit label: 0 -> "01"
const rankLabel = index => String(index + 1).padStart(2, "0");

export { rankLabel, withStarsAverage };
