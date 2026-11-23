import { t } from "../../utils/i18n.js";

// how the success page names who processed the payment (query "via", see Payment.utils.js): keys of t()
const PROVIDERS = {
    "braintree-sandbox": { name: "success.providers.sandboxName", note: "success.providers.sandboxNote" },
    braintree: { name: "success.providers.braintreeName", note: "success.providers.braintreeNote" },
    demo: { name: "success.providers.demoName", note: "success.providers.demoNote" }
};

const providerOf = via => {
    const keys = PROVIDERS[via] ?? PROVIDERS.demo;
    return { name: t(keys.name), note: t(keys.note) };
};

// the receipt from the route query (texts in the current language), or null when the page is opened without one
const receiptFrom = query =>
    query?.order && query?.tx ? { order: query.order, tx: query.tx, provider: providerOf(query.via) } : null;

export { receiptFrom };
