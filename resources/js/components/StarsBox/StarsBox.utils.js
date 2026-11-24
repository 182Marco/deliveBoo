// pure helpers of the read-only stars

const MAX_STARS = 5;

// rounded to half a star: 3.74 -> 3.5, 3.76 -> 4 (never up to the next whole star)
const starStates = average => {
    const half = Math.round((Number(average) || 0) * 2) / 2;
    return Array.from({ length: MAX_STARS }, (_, i) => (half >= i + 1 ? "full" : half >= i + 0.5 ? "half" : "empty"));
};

export { starStates };
