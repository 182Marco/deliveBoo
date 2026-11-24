// Characterization of resources/js/store.js (Vuex): mutations, getters, actions.
import { describe, it, expect, beforeEach, vi } from "vitest";
import axios from "axios";
import { createAppStore as createStore } from "../../resources/js/store.js";

vi.mock("axios", () => ({ default: { get: vi.fn() } }));

const flush = () => new Promise(r => setTimeout(r, 0));

let store;
beforeEach(() => {
    localStorage.clear();
    axios.get.mockReset();
    store = createStore();
});

describe("mutations and getters", () => {
    it("adds and pulls selected types", () => {
        store.commit("addType", 1);
        store.commit("addType", 3);
        expect(store.state.typesSelected).toEqual([1, 3]);
        expect(store.getters.selectedTypesLenght).toBe(2);

        store.commit("pullType", 1);
        expect(store.state.typesSelected).toEqual([3]);

        store.commit("cleanSelectedTypes");
        expect(store.state.typesSelected).toEqual([]);
    });

    it("adds and removes plates and recomputes the total", () => {
        const a = { id: 1, price: 4.5, restaurant_id: 7 };
        const b = { id: 2, price: 3, restaurant_id: 7 };
        store.commit("addPlate", a);
        store.commit("addPlate", b);
        store.commit("addPlate", a);
        store.commit("changeTotal");
        expect(store.getters.cartLenght).toBe(3);
        expect(store.state.total).toBe(12);

        store.commit("removePlate", a);
        store.commit("changeTotal");
        expect(store.state.cart).toEqual([a, b]);
        expect(store.state.total).toBe(7.5);

        // a plate that is not in the cart is ignored
        store.commit("removePlate", { id: 99, price: 1 });
        expect(store.state.cart).toHaveLength(2);

        store.commit("emptyCart");
        expect(store.state.cart).toEqual([]);
    });

    it("toggles warn and mobile menu", () => {
        store.commit("changeWarn");
        store.commit("toggleMenuMobile");
        expect(store.state.warn).toBe(true);
        expect(store.state.menuMobile).toBe(true);
    });

    it("persists the state in localStorage under the 'vuex' key", () => {
        store.commit("addPlate", { id: 5, price: 2 });
        const saved = JSON.parse(localStorage.getItem("vuex"));
        expect(saved.cart).toEqual([{ id: 5, price: 2 }]);

        // a new store (page reload) restores the cart
        expect(createStore().state.cart).toEqual([{ id: 5, price: 2 }]);
    });
});

describe("actions", () => {
    it("getTypes fills alltypes from /api/types", async () => {
        axios.get.mockResolvedValue({ data: { types: [{ id: 1, name: "Vegan" }] } });
        store.dispatch("getTypes");
        await flush();
        expect(axios.get).toHaveBeenCalledWith("/api/types");
        expect(store.state.alltypes).toEqual([{ id: 1, name: "Vegan" }]);
    });

    it("getRestaurants does nothing without selected types", () => {
        store.dispatch("getRestaurants", 1);
        expect(axios.get).not.toHaveBeenCalled();
    });

    it("getRestaurants calls /api/restaurants/<ids>?page=<n> and stores the pagination", async () => {
        axios.get.mockResolvedValue({ data: { data: [{ id: 9 }], current_page: 2, last_page: 3 } });
        store.commit("addType", 1);
        store.commit("addType", 2);
        store.dispatch("getRestaurants", 2);
        await flush();
        expect(axios.get).toHaveBeenCalledWith("/api/restaurants/1,2?page=2");
        expect(store.state.restByTypes).toEqual([{ id: 9 }]);
        expect(store.state.restPagination).toEqual({ current: 2, last: 3 });
    });

    it("getMenuAndDetails calls /api/restaurantsMenu/<slug>?page=<n>", async () => {
        const data = { rating: { average: 4, count: 3 }, location: { slug: "mc" }, types: [], menu: { current_page: 1, last_page: 2 } };
        axios.get.mockResolvedValue({ data });
        store.dispatch("getMenuAndDetails", { slug: "mc", page: 1 });
        await flush();
        expect(axios.get).toHaveBeenCalledWith("/api/restaurantsMenu/mc?page=1");
        expect(store.state.restaurant).toEqual(data);
        expect(store.state.menuPagination).toEqual({ current: 1, last: 2 });
    });
});
