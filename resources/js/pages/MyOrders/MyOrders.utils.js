// pure helpers of the "My orders" page

// badge modifier of each state (no modifier = neutral oat badge)
const STATE_TONES = {
    in_progress: "ui-badge--brand",
    delivered: "ui-badge--basil",
    cancelled: "",
    failed: "ui-badge--tomato",
    pending: ""
};

// days after the delivery to write or edit a review (same value as config/reviews.php window_days)
const REVIEW_WINDOW_DAYS = 30;

// what the review button of an order does: "write", "edit" or "none" (the server decides can_review)
const reviewAction = ({ can_review: can, review }) => (can ? (review ? "edit" : "write") : "none");

// the order after a save: the new review and whether it can still be edited
const withReview = (order, { review, can_review: can }) => ({ ...order, review, can_review: can });

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
        action: reviewAction(order),
        placed: formatDateTime(order.placed_at, locale),
        delivered: state === "delivered" ? formatDateTime(order.delivered_at, locale) : ""
    };
};

export { formatDateTime, itemCount, orderState, REVIEW_WINDOW_DAYS, reviewAction, stateTone, toOrderView, withReview };
