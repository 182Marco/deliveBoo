// Offline stand-in of the Braintree hosted fields (PAYMENT_DRIVER=fake): same containers,
// same tokenize() contract. The card data never leaves the browser: it becomes one of the
// Braintree sandbox test nonces, which the server FakeGateway understands.
import { HOSTED_FIELDS_OPTIONS } from "./hostedFields.js";

const VALID_NONCE = "fake-valid-nonce";
const DECLINED_NONCE = "fake-processor-declined-visa-nonce";
// the Braintree sandbox card that is always declined by the processor
const DECLINED_CARD = "4000000000000002";

const INPUT_STYLE = "width:100%;border:0;outline:0;background:transparent;padding:0;font-size:16px;color:inherit";

const digits = value => value.replace(/\D/g, "");

const isLuhnValid = number =>
    [...number]
        .reverse()
        .map(Number)
        .reduce((acc, cur, i) => acc + (i % 2 ? (cur * 2 > 9 ? cur * 2 - 9 : cur * 2) : cur), 0) %
        10 ===
    0;

// "MM/YY" or "MM/YYYY", valid until the end of that month
const isExpiryValid = (value, now = new Date()) => {
    const match = value.replace(/\s/g, "").match(/^(\d{1,2})\/(\d{2}|\d{4})$/);
    if (!match) return false;
    const month = Number(match[1]);
    const year = Number(match[2].length === 2 ? `20${match[2]}` : match[2]);
    return month >= 1 && month <= 12 && new Date(year, month) > now;
};

const CHECKS = {
    number: value => {
        const pan = digits(value);
        return pan.length >= 12 && pan.length <= 19 && isLuhnValid(pan);
    },
    expirationDate: value => isExpiryValid(value),
    cvv: value => /^\d{3,4}$/.test(value.trim())
};

// pure: card values -> nonce, or the invalid (and empty) field keys, like Braintree
const tokenizeCard = card => {
    const invalid = Object.keys(CHECKS).filter(key => !CHECKS[key](card[key] ?? ""));
    const empty = invalid.filter(key => !(card[key] ?? "").trim());
    return invalid.length
        ? { invalid, empty }
        : { nonce: digits(card.number) === DECLINED_CARD ? DECLINED_NONCE : VALID_NONCE };
};

// same error shape of braintree-web hosted fields tokenize()
const tokenizeError = ({ invalid, empty }) =>
    invalid.length === empty.length && empty.length === Object.keys(CHECKS).length
        ? Object.assign(new Error("All fields are empty."), { code: "HOSTED_FIELDS_FIELDS_EMPTY" })
        : Object.assign(new Error("Some payment input fields are invalid."), {
              code: "HOSTED_FIELDS_FIELDS_INVALID",
              details: { invalidFieldKeys: invalid, emptyFieldKeys: empty }
          });

const mountInput = ({ selector, placeholder }) => {
    const input = document.createElement("input");
    input.placeholder = placeholder;
    input.setAttribute("style", INPUT_STYLE);
    input.autocomplete = "off";
    document.querySelector(selector)?.replaceChildren(input);
    return input;
};

const createFakeHostedFields = async () => {
    const inputs = Object.fromEntries(
        Object.entries(HOSTED_FIELDS_OPTIONS.fields).map(([key, field]) => [key, mountInput(field)])
    );
    const tokenize = async () => {
        const result = tokenizeCard(Object.fromEntries(Object.entries(inputs).map(([key, e]) => [key, e.value])));
        if (result.invalid) throw tokenizeError(result);
        return { nonce: result.nonce };
    };
    return { tokenize };
};

export { createFakeHostedFields, tokenizeCard };
