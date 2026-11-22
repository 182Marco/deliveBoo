// customer fields of the order form: keys are the names expected by POST /api/orders
const CUSTOMER_FIELDS = [
    { key: "customer_name", label: "First name", type: "text", minlength: 3, maxlength: 20, autocomplete: "given-name" },
    { key: "customer_lastName", label: "Last name", type: "text", minlength: 3, maxlength: 20, autocomplete: "family-name" },
    { key: "customer_email", label: "Email", type: "email", autocomplete: "email" },
    { key: "customer_phone", label: "Phone", type: "tel", minlength: 9, maxlength: 11, autocomplete: "tel" },
    { key: "customer_address", label: "Delivery address", type: "text", minlength: 3, autocomplete: "street-address", wide: true }
];

// containers of the Braintree hosted fields (ids used by api/hostedFields.js); errors live under "card_<key>"
const CARD_FIELDS = [
    { key: "number", id: "creditCardNumber", label: "Card number", invalid: "Enter a valid card number." },
    { key: "expirationDate", id: "expireDate", label: "Expiry date", invalid: "Enter a future date (MM/YY)." },
    { key: "cvv", id: "cvv", label: "CVV", invalid: "Enter 3 or 4 digits." }
];

// how to simulate a payment, shown only in test mode (offline driver or Braintree sandbox).
// The offline driver declines a card number; the Braintree sandbox declines by amount
// (2000.00-2999.99, https://developer.paypal.com/braintree/docs/reference/general/testing).
const ANY_DATE_CVV = "Any future expiry date and any CVV.";
const TEST_PAYMENTS = {
    fake: {
        note: "Demo payments: nothing is charged, the card never leaves your browser.",
        rows: [
            { key: "accepted", ok: true, label: "Payment accepted", card: "4111 1111 1111 1111", hint: ANY_DATE_CVV },
            { key: "declined", ok: false, label: "Payment declined", card: "4000 0000 0000 0002", hint: ANY_DATE_CVV }
        ]
    },
    braintree: {
        note: "Braintree sandbox: use test cards only, nothing is charged.",
        rows: [
            { key: "accepted", ok: true, label: "Payment accepted", card: "4111 1111 1111 1111", hint: ANY_DATE_CVV },
            {
                key: "declined",
                ok: false,
                label: "Payment declined",
                card: "4111 1111 1111 1111",
                hint: "Same card, with a total between €2,000.00 and €2,999.99 (e.g. 12 × Dom Pérignon at Peri's)."
            }
        ]
    }
};

const testPayments = driver => TEST_PAYMENTS[driver] ?? null;

// receipt shown by the success page: the order, the transaction id and who processed it
// ("braintree-sandbox" | "braintree" | "demo"), passed in the query so a reload keeps it
const paymentReceipt = (response, driver, testMode) => ({
    order: String(response.id ?? ""),
    tx: response.transaction_id ?? "",
    via: driver === "braintree" ? (testMode ? "braintree-sandbox" : "braintree") : "demo"
});

const REQUIRED = "This field is required.";
const EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

const emptyCustomer = () => Object.fromEntries(CUSTOMER_FIELDS.map(e => [e.key, ""]));

// picks the customer fields out of any object (the component data)
const pickCustomer = source => Object.fromEntries(CUSTOMER_FIELDS.map(e => [e.key, source[e.key]]));

// first problem of one field, or "" when it is fine
const fieldError = (field, raw) => {
    const value = String(raw ?? "").trim();
    if (!value) return REQUIRED;
    if (field.type === "email" && !EMAIL.test(value)) return "Enter a valid email address.";
    if (field.minlength && value.length < field.minlength) return `At least ${field.minlength} characters.`;
    if (field.maxlength && value.length > field.maxlength) return `At most ${field.maxlength} characters.`;
    return "";
};

// { customer_x: [message] } for every invalid customer field (same shape as the server errors)
const validateCustomer = customer =>
    Object.fromEntries(
        CUSTOMER_FIELDS.map(e => [e.key, fieldError(e, customer[e.key])])
            .filter(([, message]) => message)
            .map(([key, message]) => [key, [message]])
    );

// tokenize() error of the hosted fields (real or offline) -> { card_x: [message] }
const cardErrors = error => {
    const keys =
        error?.code === "HOSTED_FIELDS_FIELDS_EMPTY" ? CARD_FIELDS.map(e => e.key) : error?.details?.invalidFieldKeys ?? [];
    const empty = error?.details?.emptyFieldKeys ?? (error?.code === "HOSTED_FIELDS_FIELDS_EMPTY" ? keys : []);
    return Object.fromEntries(
        CARD_FIELDS.filter(e => keys.includes(e.key)).map(e => [
            `card_${e.key}`,
            [empty.includes(e.key) ? REQUIRED : e.invalid]
        ])
    );
};

// the cart holds plates of one restaurant only: the first one gives the restaurant.
// No price: the server computes the amount from the plates (one id per portion).
const buildOrder = (cart, customer, nonce) => ({
    restaurant_id: cart[0].restaurant_id,
    ...customer,
    plates: cart.map(e => e.id),
    payment_nonce: nonce
});

// errors not tied to a form field (payment declined, plates not on the menu)
const generalErrors = errors => [...(errors.payment ?? []), ...(errors.plates ?? []), ...(errors.payment_nonce ?? [])];

export {
    buildOrder,
    CARD_FIELDS,
    cardErrors,
    CUSTOMER_FIELDS,
    emptyCustomer,
    generalErrors,
    paymentReceipt,
    pickCustomer,
    testPayments,
    validateCustomer
};
