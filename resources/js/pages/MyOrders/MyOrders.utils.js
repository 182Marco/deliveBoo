// pure helpers of the "My orders" page

// badge modifier of each state (no modifier = neutral oat badge)
const STATE_TONES = {
    in_progress: "ui-badge--brand",
    delivered: "ui-badge--basil",
    cancelled: "",
    failed: "ui-badge--tomato",
    pending: ""
};

// one state for the customer from the two columns: payment first, then delivery progress
const orderState = ({ status, fulfillment_status: fulfillment }) => {
    if (status === "failed" || status === "pending") return status;
    if (fulfillment === "delivered" || fulfillment === "cancelled") return fulfillment;
    return "in_progress";
};

const stateTone = state => STATE_TONES[state] ?? "";

// portions in the order (the badge count of the cart works the same way)
const itemCount = lines => lines.reduce((acc, cur) => acc + cur.quantity, 0);

// "23 Nov 2026, 19:30" in the language of the site; "" without a date
const formatDateTime = (iso, locale) =>
    iso ? new Intl.DateTimeFormat(locale, { dateStyle: "medium", timeStyle: "short" }).format(new Date(iso)) : "";

// an order of GET /api/me/orders, ready for the template
const toOrderView = (order, locale) => {
    const state = orderState(order);
    return {
        ...order,
        state,
        tone: stateTone(state),
        items: itemCount(order.lines),
        placed: formatDateTime(order.placed_at, locale),
        delivered: state === "delivered" ? formatDateTime(order.delivered_at, locale) : ""
    };
};

export { formatDateTime, itemCount, orderState, stateTone, toOrderView };
