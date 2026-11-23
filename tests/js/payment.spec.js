// Payment flow of resources/js/pages/Payment, fully offline:
// braintree-web and axios are mocked, no request leaves the test and no payment happens.
import { describe, it, expect, beforeAll, beforeEach, vi } from "vitest";
import { shallowMount } from "@vue/test-utils";
import axios from "axios";
import braintree from "braintree-web";
import Payment from "../../resources/js/pages/Payment/Payment.vue";
import { tokenizeCard } from "../../resources/js/api/fakeHostedFields.js";
import { receiptFrom } from "../../resources/js/pages/SuccessPage/SuccessPage.utils.js";
import { createAppStore as createStore } from "../../resources/js/store.js";
import { customerFromUser, prefillCustomer } from "../../resources/js/pages/Payment/Payment.utils.js";

vi.mock("axios", () => ({ default: { get: vi.fn(), post: vi.fn() } }));
vi.mock("braintree-web", () => ({
    default: { client: { create: vi.fn() }, hostedFields: { create: vi.fn() } }
}));

const flush = () => new Promise(r => setTimeout(r, 0));

// the Payment page imports api/braintree.js on demand: warm the module cache once,
// so that a single flush() is enough for the (mocked) hosted fields to be created
beforeAll(async () => {
    await import("../../resources/js/api/braintree.js");
});

const CART = [
    { id: 11, price: 4, restaurant_id: 7 },
    { id: 12, price: 2.5, restaurant_id: 7 }
];
const CUSTOMER = {
    customer_name: "Mario",
    customer_lastName: "Rossi",
    customer_email: "mario@example.com",
    customer_phone: "3331234567",
    customer_address: "via Roma 1"
};

let store, router, hostedFieldInstance, clientInstance;

const mountPayment = () => shallowMount(Payment, { global: { plugins: [store], mocks: { $router: router } } });

const mountReady = async (customer = CUSTOMER) => {
    const wrapper = mountPayment();
    await flush();
    await wrapper.setData({ ...customer });
    return wrapper;
};

beforeEach(() => {
    localStorage.clear();
    store = createStore();
    CART.forEach(p => store.commit("addPlate", { ...p }));
    store.commit("changeTotal");
    router = { push: vi.fn() };

    clientInstance = { id: "client" };
    hostedFieldInstance = { tokenize: vi.fn() };
    braintree.client.create.mockReset().mockResolvedValue(clientInstance);
    braintree.hostedFields.create.mockReset().mockResolvedValue(hostedFieldInstance);

    axios.get.mockReset().mockResolvedValue({ data: { driver: "braintree", authorization: "client-token-from-server" } });
    axios.post.mockReset();
    vi.spyOn(console, "log").mockImplementation(() => {});
    vi.spyOn(console, "error").mockImplementation(() => {});
});

describe("card form initialisation", () => {
    it("braintree driver: client created with the server token and the three hosted fields", async () => {
        const wrapper = mountPayment();
        await flush();

        expect(axios.get).toHaveBeenCalledWith("/api/payments/config");
        expect(braintree.client.create).toHaveBeenCalledWith({ authorization: "client-token-from-server" });

        const options = braintree.hostedFields.create.mock.calls[0][0];
        expect(options.client).toBe(clientInstance);
        expect(options.fields.number.selector).toBe("#creditCardNumber");
        expect(options.fields.cvv.selector).toBe("#cvv");
        expect(options.fields.expirationDate.selector).toBe("#expireDate");
        expect(wrapper.vm.driver).toBe("braintree");
        expect(typeof wrapper.vm.hostedFieldInstance.tokenize).toBe("function");
    });

    it("fake driver: no Braintree call, an offline form with the same tokenize contract", async () => {
        axios.get.mockResolvedValue({ data: { driver: "fake", authorization: null } });
        const wrapper = mountPayment();
        await flush();

        expect(braintree.client.create).not.toHaveBeenCalled();
        expect(wrapper.vm.driver).toBe("fake");
        expect(typeof wrapper.vm.hostedFieldInstance.tokenize).toBe("function");
    });

    it("test cards: a bar only in test mode, the dialog shows the decline rule of the active driver", async () => {
        const live = mountPayment();
        await flush();
        expect(live.find(".testbar").exists()).toBe(false);

        axios.get.mockResolvedValue({ data: { driver: "braintree", testMode: true, authorization: "token" } });
        const sandbox = mountPayment();
        await flush();
        expect(sandbox.find(".testmodal").exists()).toBe(false);
        await sandbox.find(".testbar").trigger("click");
        expect(sandbox.find(".testmodal").text()).toContain("4111 1111 1111 1111");
        expect(sandbox.find(".testmodal").text()).toContain("€2,000.00");

        axios.get.mockResolvedValue({ data: { driver: "fake", testMode: true, authorization: null } });
        const offline = mountPayment();
        await flush();
        await offline.find(".testbar").trigger("click");
        expect(offline.find(".testmodal").text()).toContain("4000 0000 0000 0002");
    });

    it("on init failure shows an error and paying does nothing", async () => {
        braintree.client.create.mockRejectedValue(new Error("network down"));
        const wrapper = await mountReady();

        expect(console.error).toHaveBeenCalled();
        expect(wrapper.vm.hostedFieldInstance).toBe(false);
        expect(wrapper.vm.error).not.toBe("");

        wrapper.vm.payWithCreditCard();
        await flush();
        expect(hostedFieldInstance.tokenize).not.toHaveBeenCalled();
        expect(axios.post).not.toHaveBeenCalled();
    });
});

describe("offline tokenization (fake driver)", () => {
    const card = { number: "4111 1111 1111 1111", expirationDate: "12/40", cvv: "123" };

    it("maps the sandbox test cards to the Braintree test nonces", () => {
        expect(tokenizeCard(card)).toEqual({ nonce: "fake-valid-nonce" });
        expect(tokenizeCard({ ...card, number: "4000 0000 0000 0002" })).toEqual({
            nonce: "fake-processor-declined-visa-nonce"
        });
    });

    it("lists every invalid field and which of them are empty", () => {
        expect(tokenizeCard({ ...card, number: "4111 1111 1111 1112" })).toEqual({ invalid: ["number"], empty: [] });
        expect(tokenizeCard({ ...card, expirationDate: "01/20", cvv: "" })).toEqual({
            invalid: ["expirationDate", "cvv"],
            empty: ["cvv"]
        });
    });
});

describe("tokenization", () => {
    it("with empty or invalid customer fields shows one error per field and sends nothing", async () => {
        hostedFieldInstance.tokenize.mockResolvedValue({ nonce: "n" });
        const scrolled = [];
        Element.prototype.scrollIntoView = function () {
            scrolled.push(this);
        };
        const wrapper = await mountReady({ ...CUSTOMER, customer_phone: "", customer_email: "mario" });

        wrapper.vm.payWithCreditCard();
        await flush();

        expect(wrapper.vm.errors).toEqual({
            customer_email: ["Enter a valid email address."],
            customer_phone: ["This field is required."]
        });
        expect(wrapper.find(".field.is-error #customer_phone").exists()).toBe(true);
        expect(wrapper.find(".field-error").text()).toBe("Enter a valid email address.");
        // scrolled back up to the first wrong field (the email comes before the phone)
        expect(scrolled).toHaveLength(1);
        expect(scrolled[0].querySelector("#customer_email")).not.toBeNull();
        expect(axios.post).not.toHaveBeenCalled();
        delete Element.prototype.scrollIntoView;
    });

    it("card errors of the hosted fields go under each card field, together with the customer ones", async () => {
        hostedFieldInstance.tokenize.mockRejectedValue({
            code: "HOSTED_FIELDS_FIELDS_INVALID",
            details: { invalidFieldKeys: ["number", "cvv"] }
        });
        hostedFieldInstance.getState = () => ({
            fields: { number: { isEmpty: false }, cvv: { isEmpty: true }, expirationDate: { isEmpty: false } }
        });
        const wrapper = await mountReady({ ...CUSTOMER, customer_name: "" });

        wrapper.vm.payWithCreditCard();
        await flush();

        expect(wrapper.vm.errors).toEqual({
            customer_name: ["This field is required."],
            card_number: ["Enter a valid card number."],
            card_cvv: ["This field is required."]
        });
        expect(wrapper.vm.error).toBe("");
        expect(axios.post).not.toHaveBeenCalled();
    });

    it("typing in a field removes its error", async () => {
        const wrapper = await mountReady();
        await wrapper.setData({ errors: { customer_name: ["x"], customer_phone: ["y"] } });

        await wrapper.find("#customer_name").trigger("input");

        expect(wrapper.vm.errors).toEqual({ customer_phone: ["y"] });
    });

    it("on success posts the order with the nonce and without any price", async () => {
        hostedFieldInstance.tokenize.mockResolvedValue({ nonce: "fake-valid-nonce" });
        axios.post.mockResolvedValue({ data: {} });
        const wrapper = await mountReady();

        wrapper.vm.payWithCreditCard();
        await flush();

        expect(wrapper.vm.nonce).toBe("fake-valid-nonce");
        expect(axios.post).toHaveBeenCalledTimes(1);
        expect(axios.post).toHaveBeenCalledWith("/api/orders", {
            restaurant_id: 7,
            ...CUSTOMER,
            plates: [11, 12],
            payment_nonce: "fake-valid-nonce"
        });
    });

    it("on a non-field failure shows the card form message and keeps the cart", async () => {
        hostedFieldInstance.tokenize.mockRejectedValue({ message: "All fields are empty." });
        const wrapper = await mountReady();

        wrapper.vm.payWithCreditCard();
        await flush();

        expect(wrapper.vm.error).toBe("All fields are empty.");
        expect(axios.post).not.toHaveBeenCalled();
        expect(store.state.cart).toHaveLength(2);
        expect(router.push).not.toHaveBeenCalled();
    });
});

describe("order response", () => {
    it("with validation errors shows them and keeps cart and page", async () => {
        hostedFieldInstance.tokenize.mockResolvedValue({ nonce: "n" });
        const errors = { customer_email: ["The customer email field is required."] };
        axios.post.mockResolvedValue({ data: { errors } });
        const wrapper = await mountReady();

        wrapper.vm.payWithCreditCard();
        await flush();

        expect(wrapper.vm.errors).toEqual(errors);
        expect(wrapper.vm.sending).toBe(false);
        expect(store.state.cart).toHaveLength(2);
        expect(router.push).not.toHaveBeenCalled();
    });

    it("with a declined payment shows the gateway message and keeps the cart", async () => {
        hostedFieldInstance.tokenize.mockResolvedValue({ nonce: "n" });
        axios.post.mockResolvedValue({ data: { errors: { payment: ["Payment declined: Do Not Honor"] } } });
        const wrapper = await mountReady();

        wrapper.vm.payWithCreditCard();
        await flush();

        expect(wrapper.vm.paymentErrors).toEqual(["Payment declined: Do Not Honor"]);
        expect(store.state.cart).toHaveLength(2);
        expect(router.push).not.toHaveBeenCalled();
    });

    it("without errors completes the order: clears fields and cart, goes to success", async () => {
        hostedFieldInstance.tokenize.mockResolvedValue({ nonce: "n" });
        axios.post.mockResolvedValue({ data: { ...CUSTOMER, id: 79, status: "paid", transaction_id: "abc123" } });
        const wrapper = await mountReady();

        wrapper.vm.payWithCreditCard();
        await flush();

        expect(wrapper.vm.errors).toEqual({});
        expect(wrapper.vm.sending).toBe(false);
        Object.keys(CUSTOMER).forEach(e => expect(wrapper.vm[e]).toBe(""));
        expect(store.state.cart).toEqual([]);
        // driver "braintree" from the server config, testMode missing -> treated as live
        expect(router.push).toHaveBeenCalledWith({
            name: "success",
            query: { order: "79", tx: "abc123", via: "braintree" }
        });
    });

    it("on network error keeps cart and page and lets the customer retry", async () => {
        hostedFieldInstance.tokenize.mockResolvedValue({ nonce: "n" });
        axios.post.mockRejectedValue(new Error("Network Error"));
        const wrapper = await mountReady();

        wrapper.vm.payWithCreditCard();
        await flush();

        expect(store.state.cart).toHaveLength(2);
        expect(router.push).not.toHaveBeenCalled();
        expect(wrapper.vm.sending).toBe(false);
        expect(wrapper.vm.error).not.toBe("");
    });
});

describe("logged-in customer (step 7.1)", () => {
    const MARIO = { name: "Mario", last_name: "Rossi", email: "customer@foodsy.test", phone: "079 123 45 67", role: "customer" };

    it("maps the account to the customer fields, never the address, phone without spaces", () => {
        expect(customerFromUser(MARIO)).toEqual({
            customer_name: "Mario",
            customer_lastName: "Rossi",
            customer_email: "customer@foodsy.test",
            customer_phone: "0791234567"
        });
        expect(customerFromUser({ name: "Ada", last_name: null, email: "ada@example.com", phone: null })).toEqual({
            customer_name: "Ada",
            customer_email: "ada@example.com"
        });
        expect(customerFromUser(null)).toEqual({});
    });

    it("fills only the empty fields: what was typed wins", () => {
        expect(prefillCustomer({ customer_name: "Marco", customer_email: " " }, MARIO)).toEqual({
            customer_lastName: "Rossi",
            customer_email: "customer@foodsy.test",
            customer_phone: "0791234567"
        });
    });

    it("prefills the form from the account, also when /api/me answers after the mount", async () => {
        store.commit("setUser", MARIO);
        const wrapper = mountPayment();
        await flush();
        expect(wrapper.vm.customer_email).toBe("customer@foodsy.test");
        expect(wrapper.vm.customer_address).toBe("");

        store.commit("setUser", null);
        const late = mountPayment();
        await flush();
        expect(late.vm.customer_name).toBe("");
        await late.setData({ customer_name: "Marco" });
        store.commit("setUser", MARIO);
        await flush();
        expect(late.vm.customer_name).toBe("Marco");
        expect(late.vm.customer_lastName).toBe("Rossi");
    });

    it("partners and admins are not prefilled (they order as guests)", async () => {
        store.commit("setUser", { ...MARIO, role: "restaurateur" });
        const wrapper = mountPayment();
        await flush();
        expect(wrapper.vm.customer_email).toBe("");
    });
});

describe("payment receipt of the success page", () => {
    it("names the provider from the query, nothing without order and transaction", () => {
        expect(receiptFrom({ order: "79", tx: "abc123", via: "braintree-sandbox" }).provider.name).toBe("Braintree sandbox");
        expect(receiptFrom({ order: "79", tx: "fake_x", via: "demo" }).provider.name).toBe("Demo payment");
        expect(receiptFrom({})).toBe(null);
    });
});
