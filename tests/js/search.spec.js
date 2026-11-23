// Header search (components/RestaurantSearch): debounced requests, stale answers dropped,
// suggestions that open the restaurant page, keyboard navigation.
import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";
import { mount, flushPromises } from "@vue/test-utils";
import axios from "axios";
import { createRouter, createMemoryHistory } from "vue-router";
import { RestaurantSearch } from "../../resources/js/components";
import { highlightParts, resultMeta } from "../../resources/js/components/RestaurantSearch/RestaurantSearch.utils.js";

vi.mock("axios", () => ({ default: { get: vi.fn() } }));

const PIZZA = { id: 1, name: "Pizzeria Napoli", slug: "pizzeria-napoli", city: "Milano", img: null, price_range: 2, average: 4.3, types: ["Pizzeria"] };
const SUSHI = { id: 2, name: "Napa Sushi", slug: "napa-sushi", city: "Torino", img: null, price_range: 3, average: 0, types: ["Sushi"] };

const Blank = { template: "<div />" };
let router;
let wrapper;

const searchCalls = () => axios.get.mock.calls.filter(([url]) => url === "/api/restaurantsSearch");

const type = async value => {
    const input = wrapper.find("#restaurant-search-input");
    await input.setValue(value);
};

beforeEach(async () => {
    vi.useFakeTimers();
    // not implemented by jsdom
    Element.prototype.scrollIntoView = vi.fn();
    axios.get.mockReset();
    axios.get.mockImplementation((url, { params }) =>
        Promise.resolve({ data: [PIZZA, SUSHI].filter(e => e.name.toLowerCase().includes(params.q.toLowerCase())) })
    );
    router = createRouter({
        history: createMemoryHistory(),
        routes: [
            { path: "/", name: "home", component: Blank },
            { path: "/r/:slug", name: "restMenu", component: Blank }
        ]
    });
    await router.push("/");
    wrapper = mount(RestaurantSearch, { global: { plugins: [router] }, attachTo: document.body });
    await wrapper.find(".search-toggle").trigger("click");
});

afterEach(() => {
    wrapper.unmount();
    vi.useRealTimers();
});

describe("RestaurantSearch", () => {
    it("opens the field from the lens and focuses it", () => {
        const input = wrapper.find("#restaurant-search-input");
        expect(input.exists()).toBe(true);
        expect(input.attributes("placeholder")).toBe("Search by restaurant");
        expect(document.activeElement).toBe(input.element);
    });

    it("waits for a pause in typing and sends one request with the last text", async () => {
        await type("n");
        await type("na");
        await type("nap");
        expect(searchCalls()).toHaveLength(0);

        vi.advanceTimersByTime(300);
        await flushPromises();

        expect(searchCalls()).toHaveLength(1);
        expect(searchCalls()[0][1].params).toEqual({ q: "nap" });
        expect(wrapper.findAll(".search-option")).toHaveLength(2);
        expect(wrapper.find(".search-mark").text()).toBe("Nap");
    });

    it("never asks for one character", async () => {
        await type("n");
        vi.advanceTimersByTime(1000);
        await flushPromises();
        expect(searchCalls()).toHaveLength(0);
        expect(wrapper.find(".search-dropdown").exists()).toBe(false);
    });

    it("drops the answer of a query made obsolete by typing", async () => {
        let resolveOld;
        axios.get.mockImplementationOnce(() => new Promise(r => (resolveOld = r)));
        await type("na");
        vi.advanceTimersByTime(300);
        await type("sushi");
        vi.advanceTimersByTime(300);
        await flushPromises();

        // the first request was aborted, its late answer changes nothing
        expect(searchCalls()[0][1].signal.aborted).toBe(true);
        resolveOld({ data: [PIZZA] });
        await flushPromises();
        expect(wrapper.findAll(".search-name").map(e => e.text())).toEqual(["Napa Sushi"]);
    });

    it("shows the empty state", async () => {
        await type("zzz");
        vi.advanceTimersByTime(300);
        await flushPromises();
        expect(wrapper.find(".search-empty-title").text()).toContain("No restaurants match “zzz”");
    });

    it("opens the restaurant page on click and closes", async () => {
        await type("napoli");
        vi.advanceTimersByTime(300);
        await flushPromises();
        await wrapper.find(".search-option").trigger("click");
        await flushPromises();
        expect(router.currentRoute.value.params.slug).toBe("pizzeria-napoli");
        expect(wrapper.find("#restaurant-search-input").exists()).toBe(false);
    });

    it("moves with the arrows and opens with Enter", async () => {
        await type("nap");
        vi.advanceTimersByTime(300);
        await flushPromises();
        const input = wrapper.find("#restaurant-search-input");
        await input.trigger("keydown", { key: "ArrowDown" });
        await input.trigger("keydown", { key: "ArrowDown" });
        expect(input.attributes("aria-activedescendant")).toBe("restaurant-search-option-1");
        await input.trigger("keydown", { key: "Enter" });
        await flushPromises();
        expect(router.currentRoute.value.params.slug).toBe("napa-sushi");
    });

    it("closes with Esc and gives the focus back to the lens", async () => {
        await wrapper.find("#restaurant-search-input").trigger("keydown", { key: "Escape" });
        await flushPromises();
        expect(wrapper.find("#restaurant-search-input").exists()).toBe(false);
        expect(document.activeElement).toBe(wrapper.find(".search-toggle").element);
    });
});

describe("RestaurantSearch utils", () => {
    it("splits the name around the match, whatever the case", () => {
        expect(highlightParts("Pizzeria Napoli", " NAP ")).toEqual([
            { text: "Pizzeria ", match: false },
            { text: "Nap", match: true },
            { text: "oli", match: false }
        ]);
        expect(highlightParts("Sushi", "xyz")).toEqual([{ text: "Sushi", match: false }]);
    });

    it("describes a suggestion with cuisines, city and price level", () => {
        expect(resultMeta(PIZZA)).toBe("Pizzeria · Milano · €€");
        expect(resultMeta({ city: "Roma" })).toBe("Roma");
    });
});
