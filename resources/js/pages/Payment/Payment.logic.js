import { markRaw } from "vue";
import { mapMutations, mapState } from "vuex";
import { Icon, OrderSummary } from "../../components";
import { createCardForm, postOrder } from "../../api";
import { cartTotal, formatPrice } from "../../utils";
import {
    CARD_FIELDS,
    CUSTOMER_FIELDS,
    buildOrder,
    cardErrors,
    emptyCustomer,
    generalErrors,
    paymentReceipt,
    pickCustomer,
    testPayments,
    validateCustomer
} from "./Payment.utils.js";

// how long the "Copied" confirmation stays on the button
const COPIED_MS = 2000;

const paymentLogic = {
    name: "Payment",
    components: { Icon, OrderSummary },
    data: () => ({
        customerFields: CUSTOMER_FIELDS,
        cardFields: CARD_FIELDS,
        ...emptyCustomer(),
        error: "",
        // "braintree" | "fake": decided by the server (PAYMENT_DRIVER)
        driver: "",
        // test environment (no real money): the page shows the test cards
        testMode: false,
        // dialog with the test cards, and the key of the card just copied ("accepted" | "declined")
        testOpen: false,
        copied: "",
        hostedFieldInstance: false,
        nonce: "",
        // { field_key: [message] }: from the checks in the browser or from the server
        errors: {},
        sending: false
    }),
    computed: {
        ...mapState(["total", "cart"]),
        // shown on the pay button; the server charges its own total from the db prices
        amount() {
            return formatPrice(cartTotal(this.cart));
        },
        testGuide() {
            return this.testMode ? testPayments(this.driver) : null;
        },
        paymentErrors() {
            return generalErrors(this.errors);
        }
    },
    async mounted() {
        try {
            const { driver, testMode, fields } = await createCardForm();
            this.driver = driver;
            this.testMode = testMode;
            // markRaw: a Braintree instance must not be wrapped in a Vue 3 reactive Proxy
            this.hostedFieldInstance = markRaw(fields);
        } catch (error) {
            console.error(error);
            this.error = "The payment service is not available, please try again later.";
        }
    },
    methods: {
        ...mapMutations(["emptyCart"]),
        formatPrice,
        fieldError(key) {
            return this.errors[key]?.[0] ?? "";
        },
        // focus moves into the dialog and back to the opener, so Esc and Tab work at once
        async openTestCards() {
            this.testOpen = true;
            await this.$nextTick();
            this.$refs.testClose?.focus();
        },
        closeTestCards() {
            this.testOpen = false;
            this.$refs.testOpener?.focus();
        },
        // the hosted fields are iframes: the card number can only be copied, then pasted
        async copyCard(row) {
            try {
                await navigator.clipboard.writeText(row.card.replace(/\s/g, ""));
                this.copied = row.key;
                setTimeout(() => {
                    if (this.copied === row.key) this.copied = "";
                }, COPIED_MS);
            } catch (error) {
                console.error(error);
            }
        },
        // typing in a field removes its error
        clearError(key) {
            if (!this.errors[key]) return;
            const { [key]: _removed, ...rest } = this.errors;
            this.errors = rest;
        },
        // brings the first invalid field into view (the customer may be scrolled down at the button)
        async showErrors(errors) {
            this.errors = errors;
            await this.$nextTick();
            const first = this.$el?.querySelector?.(".field.is-error");
            first?.scrollIntoView?.({ behavior: "smooth", block: "center" });
            first?.querySelector("input")?.focus({ preventScroll: true });
        },
        // customer fields and card are checked together, so every error shows up at once;
        // nothing is sent while the card form is not ready or the previous attempt is running
        async payWithCreditCard() {
            if (!this.hostedFieldInstance || this.sending || !this.cart.length) return;
            this.error = "";
            this.nonce = "";
            const customer = validateCustomer(this.$data);
            let nonce = "";
            let card = {};
            try {
                ({ nonce } = await this.hostedFieldInstance.tokenize());
            } catch (error) {
                card = cardErrors(error);
                // not a field problem (e.g. network): show the message of the card form
                if (!Object.keys(card).length) {
                    console.error(error);
                    this.error = error.message;
                }
            }
            const errors = { ...customer, ...card };
            if (Object.keys(errors).length) {
                await this.showErrors(errors);
                return;
            }
            if (!nonce) return;
            this.nonce = nonce;
            await this.postForm();
        },
        async postForm() {
            this.sending = true;
            const order = buildOrder(this.cart, pickCustomer(this.$data), this.nonce);
            try {
                const response = await postOrder(order);
                if (response.errors) {
                    await this.showErrors(response.errors);
                    return;
                }
                this.errors = {};
                Object.assign(this.$data, emptyCustomer());
                this.emptyCart();
                this.$router.push({ name: "success", query: paymentReceipt(response, this.driver, this.testMode) });
            } catch (error) {
                // network error: cart and page stay, the customer can try again
                console.error(error);
                this.error = "Network error, the order was not sent. Please try again.";
            } finally {
                this.sending = false;
            }
        }
    }
};

export { paymentLogic };
