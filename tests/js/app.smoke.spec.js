// Smoke test of the whole SPA: real router + real store, API answered by fixtures.
// Every route must render its page without Vue warnings or errors.
import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";
import { mount, flushPromises } from "@vue/test-utils";
import axios from "axios";
import App from "../../resources/js/App.vue";
import { router } from "../../resources/js/routes.js";
import { createAppStore as createStore } from "../../resources/js/store.js";

vi.mock("axios", () => ({ default: { get: vi.fn(), post: vi.fn() } }));
vi.mock("braintree-web", () => ({
    default: {
        client: { create: vi.fn(() => Promise.resolve({})) },
        hostedFields: { create: vi.fn(() => Promise.resolve({ tokenize: vi.fn() })) }
    }
}));

const REST = { id: 7, name: "Mc Donald's", slug: "mc-donalds", city: "Modena", address: "via Moro 52", phone: "1", img: "https://example.com/r.jpg", average: 4.2, count: 12 };
const PLATE = { id: 11, name: "Fries", price: 2.5, restaurant_id: 7, description: "d", ingredients: "i", img: "https://example.com/p.jpg", visible: 1 };
const ORDER = { id: 42, placed_at: "2026-11-20T19:30:00+01:00", delivered_at: "2026-11-20T20:05:00+01:00", price: 7.5, status: "paid", fulfillment_status: "delivered", address: "Langstrasse 1", restaurant: { name: "Mc Donald's", slug: "mc-donalds", img: null }, lines: [{ name: "Fries", quantity: 3, unit_price: 2.5 }], review: null, can_review: true };
const REVIEWS = { summary: { average: 4.2, count: 2, distribution: { 5: 1, 4: 1, 3: 0, 2: 0, 1: 0 } }, reviews: { data: [{ id: 1, rating: 5, comment: "Hot food, quick rider.", created_at: "2026-11-20T21:00:00+01:00", author: { name: "Anna B.", avatar_seed: "user-1", avatar_overrides: {} }, reply: "Thank you!", replied_at: null }], current_page: 1, last_page: 1, per_page: 4, total: 1 } };
const PAGE = (data) => ({ data, current_page: 1, last_page: 2, per_page: 4, total: 5 });

function api(url) {
    if (url === "/api/types") return { data: { types: [{ id: 1, name: "Fast Food", img: "https://example.com/t.jpg" }] } };
    if (url === "/api/restaurantsTopRated") return { data: [REST] };
    if (url.startsWith("/api/restaurantsMenu/")) return { data: { rating: { average: 4.2, count: 12 }, location: REST, types: ["Fast Food"], menu: PAGE([PLATE]) } };
    if (/^\/api\/restaurants\/[^/]+\/reviews$/.test(url)) return { data: REVIEWS };
    if (url.startsWith("/api/restaurants/")) return { data: PAGE([REST]) };
    if (url === "/api/me") return { data: { user: null } };
    if (url === "/api/me/orders") return { data: PAGE([ORDER]) };
    if (url === "/api/payments/config") return { data: { driver: "fake", authorization: null } };
    // server cart switched off (CART_SERVER=false): the site keeps the local cart
    if (url === "/api/cart") throw Object.assign(new Error("Not Found"), { response: { status: 404, data: {} } });
    throw new Error("unexpected GET " + url);
}

let warnings;
beforeEach(() => {
    localStorage.clear();
    axios.get.mockImplementation(url => Promise.resolve(api(url)));
    window.scrollTo = vi.fn();
    warnings = [];
    vi.spyOn(console, "warn").mockImplementation((...a) => warnings.push(a.join(" ")));
    vi.spyOn(console, "error").mockImplementation((...a) => warnings.push(a.join(" ")));
    vi.spyOn(console, "log").mockImplementation(() => {});
});
afterEach(() => vi.restoreAllMocks());

async function render(path, prepare = () => {}) {
    const store = createStore();
    prepare(store);
    await router.push(path);
    await router.isReady();
    const wrapper = mount(App, { global: { plugins: [store, router] }, attachTo: document.body });
    await flushPromises();
    await flushPromises();
    return wrapper;
}

const withCart = store => {
    store.commit("addPlate", { ...PLATE });
};

describe("every SPA route renders without Vue warnings", () => {
    const cases = [
        ["/", "home"],
        ["/chosen-restaurant-menu-and-details/mc-donalds", "restMenu"],
        ["/Restaurants-matching-the-single-type-you-choose/1", "singleType"],
        ["/your-cart", "cart", withCart],
        ["/payment", "payment", withCart],
        ["/well-done!Successfull-payment!Your-order-has-been-confirmed", "success"],
        ["/my-orders", "myOrders"],
        ["/this/page/does/not/exist", undefined]
    ];

    for (const [path, name, prepare] of cases) {
        it(path, async () => {
            const wrapper = await render(path, prepare);
            expect(router.currentRoute.value.name).toBe(name);
            // the page component of the route is really rendered inside <router-view>
            const page = router.currentRoute.value.matched[0].components.default;
            expect(wrapper.findComponent(page).exists()).toBe(true);
            expect(wrapper.html().length).toBeGreaterThan(100);
            expect(warnings).toEqual([]);
            wrapper.unmount();
        });
    }

    it("home shows the top rated restaurants from the API", async () => {
        const wrapper = await render("/");
        expect(axios.get).toHaveBeenCalledWith("/api/types");
        expect(axios.get).toHaveBeenCalledWith("/api/restaurantsTopRated");
        expect(wrapper.text()).toContain("Mc Donald's");
        wrapper.unmount();
    });

    it("a cuisine page opened from its address lists the restaurants of that cuisine", async () => {
        // a stale selection from a previous visit must not win over the address
        const wrapper = await render("/Restaurants-matching-the-single-type-you-choose/1", store => store.commit("addType", 3));
        expect(axios.get).toHaveBeenCalledWith(expect.stringMatching(/^\/api\/restaurants\/1(\?|$)/));
        expect(wrapper.text()).toContain("Mc Donald's");
        expect(wrapper.text()).not.toContain("Choose a cuisine to start");
        wrapper.unmount();
    });

    it("restaurant menu shows its plates", async () => {
        const wrapper = await render("/chosen-restaurant-menu-and-details/mc-donalds");
        expect(axios.get).toHaveBeenCalledWith("/api/restaurantsMenu/mc-donalds?page=1");
        expect(wrapper.text()).toContain("Fries");
        wrapper.unmount();
    });
});
