// Characterization of the cart as the customer sees it (dev/PIANO_CARRELLO.md, step 0):
// add, remove, one restaurant per order, totals in header/summary/bag, order payload.
// The helpers at the top are the only place that knows how the cart is stored:
// later steps change them, the expectations below must keep passing.
import { describe, it, expect, beforeEach, vi } from "vitest";
import { mount, shallowMount } from "@vue/test-utils";
import Plate from "../../resources/js/components/Plate/Plate.vue";
import Warning from "../../resources/js/components/Warning/Warning.vue";
import OrderSummary from "../../resources/js/components/OrderSummary/OrderSummary.vue";
import Header from "../../resources/js/components/Header/Header.vue";
import Cart from "../../resources/js/pages/Cart/Cart.vue";
import { createAppStore as createStore } from "../../resources/js/store.js";
import { buildOrder } from "../../resources/js/pages/Payment/Payment.utils.js";

vi.mock("axios", () => ({ default: { get: vi.fn(() => Promise.resolve({ data: {} })), post: vi.fn() } }));

const FRIES = { id: 11, name: "Fries", price: 2.5, restaurant_id: 7, restaurant_slug: "mc-donalds", orderable: true };
const BURGER = { id: 12, name: "Burger", price: 8, restaurant_id: 7, restaurant_slug: "mc-donalds", orderable: true };
const SUSHI = { id: 30, name: "Sushi", price: 12, restaurant_id: 9, restaurant_slug: "sushi-bar", orderable: true };

// --- storage helpers (the only cart-shape-aware code of this file) ---
const seed = (store, plate, portions = 1) => {
    for (let i = 0; i < portions; i++) store.commit("addPlate", { ...plate });
};
// { plateId: portions } in cart order
const portions = store => store.state.cart.reduce((acc, e) => ({ ...acc, [e.id]: (acc[e.id] ?? 0) + 1 }), {});

const RouterLink = { template: "<a><slot /></a>" };
const options = store => ({ global: { plugins: [store], stubs: { "router-link": RouterLink }, mocks: { $router: { push: vi.fn() } } } });
// RestMenu mounts the warning only while it is open: same here, so it is unmounted when it closes
const WarningHost = { components: { Warning }, computed: { warn() { return this.$store.state.warn; } }, template: '<Warning v-if="warn" />' };
const stepper = wrapper => wrapper.findComponent({ name: "QuantityStepper" });

let store;
beforeEach(() => {
    localStorage.clear();
    window.scrollTo = vi.fn();
    store = createStore();
});

describe("adding and removing from a menu dish", () => {
    it("the add button puts one portion in the bag, the stepper adds and removes", async () => {
        const wrapper = shallowMount(Plate, { props: { plate: FRIES }, ...options(store) });
        await wrapper.find("button.dish-add").trigger("click");
        expect(portions(store)).toEqual({ 11: 1 });

        await stepper(wrapper).vm.$emit("increment");
        expect(portions(store)).toEqual({ 11: 2 });
        expect(stepper(wrapper).props("count")).toBe(2);

        await stepper(wrapper).vm.$emit("decrement");
        await stepper(wrapper).vm.$emit("decrement");
        expect(portions(store)).toEqual({});
        expect(wrapper.find("button.dish-add").exists()).toBe(true);
    });

    it("a dish that cannot be ordered has no add button", () => {
        const wrapper = shallowMount(Plate, { props: { plate: { ...FRIES, orderable: false } }, ...options(store) });
        expect(wrapper.find("button.dish-add").exists()).toBe(false);
    });

    it("a dish of another restaurant opens the warning and leaves the bag as it is", async () => {
        seed(store, FRIES);
        const wrapper = shallowMount(Plate, { props: { plate: SUSHI }, ...options(store) });
        await wrapper.find("button.dish-add").trigger("click");
        expect(store.state.warn).toBe(true);
        expect(portions(store)).toEqual({ 11: 1 });
    });
});

describe("warning: one restaurant per order", () => {
    it("'start a new order' empties the bag and closes the dialog", async () => {
        seed(store, FRIES, 2);
        store.commit("changeWarn");
        const wrapper = mount(WarningHost, options(store));
        await wrapper.find("button.ui-btn--primary").trigger("click");
        expect(wrapper.findComponent(Warning).exists()).toBe(false);
        expect(portions(store)).toEqual({});
        expect(store.state.warn).toBe(false);
    });

    it("'back' points to the restaurant of the bag", () => {
        seed(store, FRIES);
        const wrapper = shallowMount(Warning, options(store));
        expect(wrapper.vm.cartSlug).toBe("mc-donalds");
    });
});

describe("summary, bag page and header", () => {
    beforeEach(() => {
        seed(store, FRIES, 2);
        seed(store, BURGER);
    });

    it("the summary has one line per dish with its portions and the total", () => {
        const wrapper = shallowMount(OrderSummary, options(store));
        expect(wrapper.findAll(".summary-name").map(e => e.text())).toEqual(["Fries", "Burger"]);
        expect(wrapper.findAllComponents({ name: "QuantityStepper" }).map(e => e.props("count"))).toEqual([2, 1]);
        expect(wrapper.find(".summary-amount").text()).toBe("€13.00");
    });

    it("the summary steppers change the portions of their line", async () => {
        const wrapper = shallowMount(OrderSummary, options(store));
        const [fries, burger] = wrapper.findAllComponents({ name: "QuantityStepper" });
        await fries.vm.$emit("decrement");
        await burger.vm.$emit("increment");
        expect(portions(store)).toEqual({ 11: 1, 12: 2 });
    });

    it("the bag page lists the dishes, shows the total and can be emptied", async () => {
        const wrapper = shallowMount(Cart, options(store));
        expect(wrapper.findAll(".bag-line")).toHaveLength(2);
        expect(wrapper.text()).toContain("13.00");
        await wrapper.find("button.bag-clear").trigger("click");
        expect(portions(store)).toEqual({});
    });

    it("the header shows the number of portions and the amount", () => {
        const wrapper = shallowMount(Header, options(store));
        expect(wrapper.find(".header-bag-count").text()).toBe("3");
        expect(wrapper.find(".header-bag-amount").text()).toBe("€13.00");
    });
});

describe("bag page states", () => {
    it("before the first answer it says loading, not empty; then empty", async () => {
        const wrapper = shallowMount(Cart, options(store));
        expect(wrapper.find(".bag-loading").exists()).toBe(true);
        expect(wrapper.find(".ui-empty").exists()).toBe(false);

        store.commit("setCartMode", "local");
        await wrapper.vm.$nextTick();
        expect(wrapper.find(".bag-loading").exists()).toBe(false);
        expect(wrapper.find(".ui-empty").exists()).toBe(true);
    });

    it("a dish no longer orderable is marked and blocks the checkout", () => {
        seed(store, FRIES);
        seed(store, { ...BURGER, orderable: false });
        const wrapper = shallowMount(Cart, options(store));
        expect(wrapper.findAll(".bag-unavailable")).toHaveLength(1);
        expect(wrapper.find("button.ui-btn--primary").attributes("disabled")).toBeDefined();
        expect(wrapper.find(".bag-blocked").exists()).toBe(true);
    });
});

describe("order payload", () => {
    it("one plate id per portion and the restaurant of the bag; no price is sent", () => {
        seed(store, FRIES, 2);
        seed(store, BURGER);
        const order = buildOrder(store.getters.cartLines, { customer_name: "Mario" }, "nonce");
        expect(order).toEqual({ restaurant_id: 7, customer_name: "Mario", plates: [11, 11, 12], payment_nonce: "nonce" });
    });
});
