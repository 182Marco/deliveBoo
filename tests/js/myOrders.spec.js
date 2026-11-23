// "My orders" (dev/PIANO_AUTH_CLIENTI.md, step 7.2): helpers, page states and the local pagination.
// axios is mocked: no request leaves the test.
import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";
import { mount, flushPromises, RouterLinkStub } from "@vue/test-utils";
import axios from "axios";
import { MyOrders } from "../../resources/js/pages";
import { PaginateEl } from "../../resources/js/components";
import { orderState, itemCount, toOrderView } from "../../resources/js/pages/MyOrders/MyOrders.utils.js";
import { pageWindow } from "../../resources/js/utils";
import { createAppStore as createStore } from "../../resources/js/store.js";

vi.mock("axios", () => ({ default: { get: vi.fn(), post: vi.fn() } }));

const ORDER = {
    id: 42,
    placed_at: "2026-11-20T19:30:00+01:00",
    delivered_at: "2026-11-20T20:05:00+01:00",
    price: 12.5,
    status: "paid",
    fulfillment_status: "delivered",
    address: "Langstrasse 1",
    restaurant: { name: "Sushi Kreis 4", slug: "sushi-kreis-4", img: null },
    lines: [
        { name: "Maki", quantity: 2, unit_price: 4 },
        { name: "Miso", quantity: 1, unit_price: 4.5 }
    ]
};
const PAGE = (data, current = 1, last = 1) => ({ data, current_page: current, last_page: last, total: data.length });
const unauthorized = () => Object.assign(new Error("401"), { response: { status: 401 } });

let store, wrapper;

const mountPage = async () => {
    wrapper = mount(MyOrders, { global: { plugins: [store], stubs: { RouterLink: RouterLinkStub } } });
    await flushPromises();
    return wrapper;
};

beforeEach(() => {
    localStorage.clear();
    store = createStore();
    axios.get.mockReset();
    window.scrollTo = vi.fn();
    vi.spyOn(console, "error").mockImplementation(() => {});
});
afterEach(() => {
    wrapper?.unmount();
    vi.restoreAllMocks();
});

describe("order helpers", () => {
    it("gives one state from payment and delivery", () => {
        expect(orderState({ status: "failed", fulfillment_status: null })).toBe("failed");
        expect(orderState({ status: "pending", fulfillment_status: null })).toBe("pending");
        expect(orderState({ status: "paid", fulfillment_status: "delivered" })).toBe("delivered");
        expect(orderState({ status: "paid", fulfillment_status: "cancelled" })).toBe("cancelled");
        expect(orderState({ status: "paid", fulfillment_status: "preparing" })).toBe("in_progress");
        expect(orderState({ status: "paid", fulfillment_status: "received" })).toBe("in_progress");
    });

    it("counts portions and prepares the view", () => {
        expect(itemCount(ORDER.lines)).toBe(3);
        const view = toOrderView(ORDER, "en");
        expect(view).toMatchObject({ state: "delivered", tone: "ui-badge--basil", items: 3 });
        expect(view.placed).toMatch(/2026/);
        expect(view.delivered).toMatch(/2026/);
        // a delivery date only for delivered orders
        expect(toOrderView({ ...ORDER, fulfillment_status: "preparing" }, "en").delivered).toBe("");
    });

    it("keeps long paginations on one row", () => {
        expect(pageWindow(1, 5)).toEqual([1, 2, 3, 4, 5]);
        expect(pageWindow(1, 50)).toEqual([1, 2, null, 50]);
        expect(pageWindow(25, 50)).toEqual([1, null, 24, 25, 26, null, 50]);
        expect(pageWindow(50, 50)).toEqual([1, null, 49, 50]);
        expect(pageWindow(3, 50)).toEqual([1, 2, 3, 4, null, 50]);
    });
});

describe("My orders page", () => {
    it("lists the orders with state, lines, total and a link to order again", async () => {
        axios.get.mockResolvedValue({ data: PAGE([ORDER]) });
        await mountPage();

        expect(axios.get).toHaveBeenCalledWith("/api/me/orders", { params: { page: 1 } });
        const card = wrapper.find(".orders-card");
        expect(card.text()).toContain("Sushi Kreis 4");
        expect(card.text()).toContain("Order #42");
        expect(card.find(".ui-badge").text()).toBe("Delivered");
        expect(card.findAll(".orders-line")).toHaveLength(2);
        expect(card.find(".orders-line").text()).toContain("€8.00");
        expect(card.find(".orders-amount").text()).toBe("€12.50");
        expect(card.text()).toContain("3 items");
        const links = card.findAllComponents(RouterLinkStub);
        expect(links.at(-1).props("to")).toEqual({ name: "restMenu", params: { slug: "sushi-kreis-4" } });
    });

    it("keeps an order of a restaurant that left Foodsy, without links", async () => {
        axios.get.mockResolvedValue({ data: PAGE([{ ...ORDER, restaurant: null, status: "failed" }]) });
        await mountPage();

        expect(wrapper.text()).toContain("Restaurant no longer on Foodsy");
        expect(wrapper.find(".ui-badge").text()).toBe("Payment failed");
        expect(wrapper.findAllComponents(RouterLinkStub)).toHaveLength(0);
    });

    it("asks a guest to log in", async () => {
        axios.get.mockRejectedValue(unauthorized());
        await mountPage();

        expect(wrapper.text()).toContain("Log in to see your orders");
        expect(wrapper.find('a[href="/login"]').exists()).toBe(true);
        expect(console.error).not.toHaveBeenCalled();
    });

    it("loads again when the guest logs in", async () => {
        axios.get.mockRejectedValueOnce(unauthorized()).mockResolvedValue({ data: PAGE([ORDER]) });
        await mountPage();
        store.commit("setUser", { id: 5, role: "customer" });
        await flushPromises();

        expect(axios.get).toHaveBeenCalledTimes(2);
        expect(wrapper.findAll(".orders-card")).toHaveLength(1);
    });

    it("does not load twice when /api/me answers after the mount", async () => {
        axios.get.mockResolvedValue({ data: PAGE([ORDER]) });
        await mountPage();
        store.commit("setUser", { id: 5, role: "customer" });
        await flushPromises();

        expect(axios.get).toHaveBeenCalledTimes(1);
    });

    it("shows the empty state", async () => {
        axios.get.mockResolvedValue({ data: PAGE([]) });
        await mountPage();

        expect(wrapper.text()).toContain("No orders yet");
    });

    it("on a server error offers to try again", async () => {
        axios.get.mockRejectedValueOnce(new Error("500")).mockResolvedValue({ data: PAGE([ORDER]) });
        await mountPage();
        expect(wrapper.find('[role="alert"]').text()).toContain("We could not load your orders.");

        await wrapper.find('[role="alert"] button').trigger("click");
        await flushPromises();
        expect(wrapper.findAll(".orders-card")).toHaveLength(1);
    });

    it("loads the page chosen in the pagination and goes to the top", async () => {
        axios.get.mockImplementation((url, { params }) => Promise.resolve({ data: PAGE([{ ...ORDER, id: params.page }], params.page, 3) }));
        await mountPage();

        wrapper.findComponent(PaginateEl).vm.$emit("go", 3);
        await flushPromises();

        expect(axios.get).toHaveBeenLastCalledWith("/api/me/orders", { params: { page: 3 } });
        expect(wrapper.text()).toContain("Order #3");
        expect(window.scrollTo).toHaveBeenCalled();
    });
});

describe("PaginateEl in local mode", () => {
    it("emits the page instead of calling the store", async () => {
        const dispatch = vi.spyOn(store, "dispatch");
        const pager = mount(PaginateEl, { props: { local: true, paginate: { current: 1, last: 20 } }, global: { plugins: [store] } });

        expect(pager.findAll(".ui-page--disabled").some(e => e.text() === "…")).toBe(true);
        await pager.findAll("button").find(e => e.text() === "20").trigger("click");

        expect(pager.emitted("go")).toEqual([[20]]);
        expect(dispatch).not.toHaveBeenCalled();
        pager.unmount();
    });
});
