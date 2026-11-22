// the API sends the average as a decimal string: stars want an integer, rounded up
const withStarsAverage = restaurants =>
    restaurants.map(e => ({ ...e, average: Math.ceil(parseFloat(e.average)) }));

export { withStarsAverage };
