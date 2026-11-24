// pure helpers of the star picker (a radio group of 5 stars)

const MAX_STARS = 5;

// keyboard of a radio group: arrows move by one star, Home / End jump; null = not a key of the group
const nextValue = (key, current) =>
    ({
        ArrowRight: Math.min(MAX_STARS, current + 1),
        ArrowUp: Math.min(MAX_STARS, current + 1),
        ArrowLeft: Math.max(1, current - 1),
        ArrowDown: Math.max(1, current - 1),
        Home: 1,
        End: MAX_STARS
    })[key] ?? null;

// roving tabindex: only the chosen star (or the first one, before a choice) is reached with Tab
const tabIndexFor = (star, value) => (star === (value || 1) ? 0 : -1);

export { MAX_STARS, nextValue, tabIndexFor };
