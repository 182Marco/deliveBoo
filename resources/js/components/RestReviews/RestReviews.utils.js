// pure helpers of the reviews of the restaurant page

// how many reviews per star, 5 to 1, with the share of the longest bar (0-1)
const distributionRows = (distribution = {}) => {
    const rows = [5, 4, 3, 2, 1].map(stars => ({ stars, count: Number(distribution[stars] ?? 0) }));
    const max = Math.max(1, ...rows.map(e => e.count));
    return rows.map(e => ({ ...e, share: e.count / max }));
};

// "23 Nov 2026" in the language of the site; "" without a date
const formatDay = (iso, locale) => (iso ? new Intl.DateTimeFormat(locale, { dateStyle: "medium" }).format(new Date(iso)) : "");

export { distributionRows, formatDay };
