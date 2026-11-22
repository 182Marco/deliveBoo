// Containers and styles of the card fields, shared by the Braintree hosted fields and their
// offline stand-in: kept apart from braintree.js so that braintree-web is only downloaded
// when the server driver is "braintree" (see payments.js).
const HOSTED_FIELDS_OPTIONS = {
    // the fields live in iframes: only system fonts are available there
    styles: {
        input: {
            "font-size": "16px",
            "font-family": "system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif",
            color: "#1c1a17"
        },
        "::placeholder": { color: "#6e675e" },
        ".invalid": { color: "#c4372c" }
    },
    fields: {
        number: { selector: "#creditCardNumber", placeholder: "1234 1234 1234 1234" },
        cvv: { selector: "#cvv", placeholder: "123" },
        expirationDate: { selector: "#expireDate", placeholder: "MM / YY" }
    }
};

export { HOSTED_FIELDS_OPTIONS };
