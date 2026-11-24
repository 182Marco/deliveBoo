const formatPrice = value => value.toFixed(2);

// an average of stars with one decimal: 4.56 -> "4.6" (null or no reviews -> "0.0")
const formatRating = average => (Number(average) || 0).toFixed(1);

export { formatPrice, formatRating };
