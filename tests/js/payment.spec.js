// Braintree payment flow of resources/js/pages/Payment.vue, fully offline:
// braintree-web and axios are mocked, no request leaves the test and no payment happens.
// Only mountPayment() depends on the Vue version: the assertions must not change in the Vue 3 port.
import { describe, it, expect, beforeEach, vi } from "vitest";
import { shallowMount } from "@vue/test-utils";
import braintree from "braintree-web";
import Payment from "../../resources/js/pages/Payment.vue";
import createStore from "../../resources/js/store.js";

vi.mock("braintree-web", () => ({
    default: { client: { create: vi.fn() }, hostedFields: { create: vi.fn() } }
}));

const flush = () => new Promise(r => setTimeout(r, 0));

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

function mountPayment() {
    return shallowMount(Payment, { global: { plugins: [store], mocks: { $router: router } } });
}

async function mountReady(customer = CUSTOMER) {
    const wrapper = mountPayment();
    await flush();
    await wrapper.setData({ ...customer });
    return wrapper;
}

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

    // Payment.vue uses the global window.axios set by resources/js/vue.js
    window.axios = { post: vi.fn() };
    vi.spyOn(console, "log").mockImplementation(() => {});
    vi.spyOn(console, "error").mockImplementation(() => {});
});

describe("Braintree initialisation", () => {
    it("creates the client with the sandbox key and the three hosted fields", async () => {
        const wrapper = mountPayment();
        await flush();

        expect(braintree.client.create).toHaveBeenCalledTimes(1);
        expect(braintree.client.create.mock.calls[0][0]).toEqual({ authorization: expect.stringMatching(/^sandbox_/) });

        const options = braintree.hostedFields.create.mock.calls[0][0];
        expect(options.client).toBe(clientInstance);
        expect(options.fields.number.selector).toBe("#creditCardNumber");
        expect(options.fields.cvv.selector).toBe("#cvv");
        expect(options.fields.expirationDate.selector).toBe("#expireDate");
        expect(wrapper.vm.hostedFieldInstance).toBe(hostedFieldInstance);
    });

    it("on init failure logs the error and paying does nothing", async () => {
        braintree.client.create.mockRejectedValue(new Error("network down"));
        const wrapper = await mountReady();

        expect(console.error).toHaveBeenCalled();
        expect(wrapper.vm.hostedFieldInstance).toBe(false);

        wrapper.vm.payWithCreditCard();
        await flush();
        expect(hostedFieldInstance.tokenize).not.toHaveBeenCalled();
        expect(window.axios.post).not.toHaveBeenCalled();
    });
});

describe("tokenization", () => {
    it("does not tokenize while a customer field is empty", async () => {
        const wrapper = await mountReady({ ...CUSTOMER, customer_phone: "" });

        wrapper.vm.payWithCreditCard();
        await flush();
        expect(hostedFieldInstance.tokenize).not.toHaveBeenCalled();
        expect(window.axios.post).not.toHaveBeenCalled();
    });

    it("on success stores the nonce and posts the exact order payload", async () => {
        hostedFieldInstance.tokenize.mockResolvedValue({ nonce: "fake-valid-nonce" });
        window.axios.post.mockResolvedValue({ data: {} });
        const wrapper = await mountReady();

        wrapper.vm.payWithCreditCard();
        await flush();

        expect(wrapper.vm.nonce).toBe("fake-valid-nonce");
        expect(window.axios.post).toHaveBeenCalledTimes(1);
        expect(window.axios.post).toHaveBeenCalledWith("/api/orders", {
            restaurant_id: 7,
            price: 6.5,
            ...CUSTOMER,
            plates: [11, 12]
        });
    });

    it("on failure shows the Braintree message and keeps the cart", async () => {
        hostedFieldInstance.tokenize.mockRejectedValue({ message: "All fields are empty." });
        const wrapper = await mountReady();

        wrapper.vm.payWithCreditCard();
        await flush();

        expect(wrapper.vm.error).toBe("All fields are empty.");
        expect(window.axios.post).not.toHaveBeenCalled();
        expect(store.state.cart).toHaveLength(2);
        expect(router.push).not.toHaveBeenCalled();
    });
});

describe("order response", () => {
    it("with validation errors shows them and keeps cart and page", async () => {
        hostedFieldInstance.tokenize.mockResolvedValue({ nonce: "n" });
        const errors = { customer_email: ["The customer email field is required."] };
        window.axios.post.mockResolvedValue({ data: { errors } });
        const wrapper = await mountReady();

        wrapper.vm.payWithCreditCard();
        await flush();

        expect(wrapper.vm.errors).toEqual(errors);
        expect(wrapper.vm.sending).toBe(false);
        expect(store.state.cart).toHaveLength(2);
        expect(router.push).not.toHaveBeenCalled();
    });

    it("without errors completes the order: clears fields and cart, goes to success", async () => {
        hostedFieldInstance.tokenize.mockResolvedValue({ nonce: "n" });
        window.axios.post.mockResolvedValue({ data: { ...CUSTOMER } });
        const wrapper = await mountReady();

        wrapper.vm.payWithCreditCard();
        await flush();

        expect(wrapper.vm.errors).toEqual({});
        expect(wrapper.vm.sending).toBe(false);
        for (const field of Object.keys(CUSTOMER)) {
            expect(wrapper.vm[field]).toBe("");
        }
        expect(store.state.cart).toEqual([]);
        expect(router.push).toHaveBeenCalledWith({ name: "success" });
    });

    it("on network error keeps cart and page (sending stays true: current behaviour)", async () => {
        hostedFieldInstance.tokenize.mockResolvedValue({ nonce: "n" });
        window.axios.post.mockRejectedValue(new Error("Network Error"));
        const wrapper = await mountReady();

        wrapper.vm.payWithCreditCard();
        await flush();

        expect(store.state.cart).toHaveLength(2);
        expect(router.push).not.toHaveBeenCalled();
        expect(wrapper.vm.sending).toBe(true);
    });
});
