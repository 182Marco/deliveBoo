import { t } from "../../utils/i18n.js";

// customer fields of the order form: keys are the names expected by POST /api/orders; labels are keys of t()
const CUSTOMER_FIELDS = [
    { key: "customer_name", label: "checkout.fields.firstName", type: "text", minlength: 3, maxlength: 20, autocomplete: "given-name" },
    { key: "customer_lastName", label: "checkout.fields.lastName", type: "text", minlength: 3, maxlength: 20, autocomplete: "family-name" },
    { key: "customer_email", label: "checkout.fields.email", type: "email", autocomplete: "email" },
    { key: "customer_phone", label: "checkout.fields.phone", type: "tel", minlength: 9, maxlength: 11, autocomplete: "tel" },
    { key: "customer_address", label: "checkout.fields.address", type: "text", minlength: 3, autocomplete: "street-address", wide: true }
];

// containers of the Braintree hosted fields (ids used by api/hostedFields.js); errors live under "card_<key>"
const CARD_FIELDS = [
    { key: "number", id: "creditCardNumber", label: "checkout.fields.cardNumber", invalid: "checkout.errors.cardNumber" },
    { key: "expirationDate", id: "expireDate", label: "checkout.fields.expiry", invalid: "checkout.errors.expiry" },
    { key: "cvv", id: "cvv", label: "checkout.fields.cvv", invalid: "checkout.errors.cvv" }
];

// how to simulate a payment, shown only in test mode (offline driver or Braintree sandbox).
// The offline driver declines a card number; the Braintree sandbox declines by amount
// (2000.00-2999.99, https://developer.paypal.com/braintree/docs/reference/general/testing).
// note, label and hint are keys of t()
const ANY_DATE_CVV = "checkout.test.anyDateCvv";
const TEST_PAYMENTS = {
    fake: {
        note: "checkout.test.fakeNote",
        rows: [
            { key: "accepted", ok: true, label: "checkout.test.accepted", card: "4111 1111 1111 1111", hint: ANY_DATE_CVV },
            { key: "declined", ok: false, label: "checkout.test.declined", card: "4000 0000 0000 0002", hint: ANY_DATE_CVV }
        ]
    },
    braintree: {
        note: "checkout.test.braintreeNote",
        rows: [
            { key: "accepted", ok: true, label: "checkout.test.accepted", card: "4111 1111 1111 1111", hint: ANY_DATE_CVV },
            { key: "declined", ok: false, label: "checkout.test.declined", card: "4111 1111 1111 1111", hint: "checkout.test.declinedHint" }
        ]
    }
};

// the guide of a driver in the current language, or null
const testPayments = driver => {
    const guide = TEST_PAYMENTS[driver];
    return guide
        ? { note: t(guide.note), rows: guide.rows.map(e => ({ ...e, label: t(e.label), hint: t(e.hint) })) }
        : null;
};

// receipt shown by the success page: the order, the transaction id and who processed it
// ("braintree-sandbox" | "braintree" | "demo"), passed in the query so a reload keeps it
const paymentReceipt = (response, driver, testMode) => ({
    order: String(response.id ?? ""),
    tx: response.transaction_id ?? "",
    via: driver === "braintree" ? (testMode ? "braintree-sandbox" : "braintree") : "demo"
});

const REQUIRED = "checkout.errors.required";
const EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

const emptyCustomer = () => Object.fromEntries(CUSTOMER_FIELDS.map(e => [e.key, ""]));

// picks the customer fields out of any object (the component data)
const pickCustomer = source => Object.fromEntries(CUSTOMER_FIELDS.map(e => [e.key, source[e.key]]));

// first problem of one field (in the current language), or "" when it is fine
const fieldError = (field, raw) => {
    const value = String(raw ?? "").trim();
    if (!value) return t(REQUIRED);
    if (field.type === "email" && !EMAIL.test(value)) return t("checkout.errors.email");
    if (field.minlength && value.length < field.minlength) return t("checkout.errors.min", { n: field.minlength });
    if (field.maxlength && value.length > field.maxlength) return t("checkout.errors.max", { n: field.maxlength });
    return "";
};

// { customer_x: [message] } for every invalid customer field (same shape as the server errors)
const validateCustomer = customer =>
    Object.fromEntries(
        CUSTOMER_FIELDS.map(e => [e.key, fieldError(e, customer[e.key])])
            .filter(([, message]) => message)
            .map(([key, message]) => [key, [message]])
    );

// customer fields known from the logged-in account (GET /api/me); the address is the one of the last order.
// The phone loses its spaces ("079 123 45 67" fits the 11 characters of the field)
const customerFromUser = user =>
    Object.fromEntries(
        Object.entries({
            customer_name: user?.name,
            customer_lastName: user?.last_name,
            customer_email: user?.email,
            customer_phone: user?.phone && String(user.phone).replace(/\s+/g, ""),
            customer_address: user?.last_address
        }).filter(([, value]) => value)
    );

// fills only the empty fields: what the customer already typed always wins
const prefillCustomer = (current, user) =>
    Object.fromEntries(Object.entries(customerFromUser(user)).filter(([key]) => !String(current[key] ?? "").trim()));

// tokenize() error of the hosted fields (real or offline) -> { card_x: [message] }
const cardErrors = error => {
    const keys =
        error?.code === "HOSTED_FIELDS_FIELDS_EMPTY" ? CARD_FIELDS.map(e => e.key) : error?.details?.invalidFieldKeys ?? [];
    const empty = error?.details?.emptyFieldKeys ?? (error?.code === "HOSTED_FIELDS_FIELDS_EMPTY" ? keys : []);
    return Object.fromEntries(
        CARD_FIELDS.filter(e => keys.includes(e.key)).map(e => [
            `card_${e.key}`,
            [t(empty.includes(e.key) ? REQUIRED : e.invalid)]
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
    customerFromUser,
    emptyCustomer,
    generalErrors,
    paymentReceipt,
    pickCustomer,
    prefillCustomer,
    testPayments,
    validateCustomer
};
