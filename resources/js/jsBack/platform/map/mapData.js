// pure helpers of the Maps tab: sizes, colours and popup rows (tested without Leaflet)

const COLORS = {
    restaurant: "#d0682c", // Foodsy orange (validated series 1)
    zone: "#2f6f9f", // slate blue (validated series 2)
    none: "#6e675e" // --ink-muted: no orders in the period
};

// zurich, when there is nothing to fit
const DEFAULT_VIEW = { center: [47.3769, 8.5417], zoom: 10 };

// area proportional to the value (radius ∝ √value) between min and max pixels
const radiusFor = (value, max, min = 3, top = 14) => (value > 0 && max > 0 ? min + (top - min) * Math.sqrt(value / max) : 2.5);

const maxOf = (items, key) => items.reduce((m, item) => Math.max(m, item[key] || 0), 0);

const money = value => (value === null || value === undefined ? "–" : `€${new Intl.NumberFormat("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value)}`);

// label/value rows of the popup of a restaurant or a zone
const popupRows = (item, text) => [
    [text.gross, money(item.gross_value)],
    [text.orders, String(item.completed_orders ?? 0)],
    ...(item.average_order !== undefined ? [[text.average, money(item.average_order)]] : []),
    [text.revenue, money(item.revenue)],
    ...(item.rating !== undefined ? [[text.rating, item.rating === null ? "–" : String(item.rating)]] : [])
];

// [[south, west], [north, east]] of the points, null without points
const boundsOf = points => {
    if (!points.length) return null;
    const lats = points.map(p => p.lat);
    const lngs = points.map(p => p.lng);
    return [
        [Math.min(...lats), Math.min(...lngs)],
        [Math.max(...lats), Math.max(...lngs)]
    ];
};

export { COLORS, DEFAULT_VIEW, boundsOf, maxOf, money, popupRows, radiusFor };
