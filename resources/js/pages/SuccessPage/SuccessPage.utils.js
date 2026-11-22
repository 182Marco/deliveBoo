// how the success page names who processed the payment (query "via", see Payment.utils.js)
const PROVIDERS = {
    "braintree-sandbox": {
        name: "Braintree sandbox",
        note: "A real transaction processed by Braintree in its test environment: no money moved."
    },
    braintree: { name: "Braintree", note: "Charged securely by Braintree, a PayPal service." },
    demo: { name: "Demo payment", note: "Simulated offline for this demo: no money moved." }
};

// the receipt from the route query, or null when the page is opened without one
const receiptFrom = query =>
    query?.order && query?.tx
        ? { order: query.order, tx: query.tx, provider: PROVIDERS[query.via] ?? PROVIDERS.demo }
        : null;

export { receiptFrom };
