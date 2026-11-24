// Server cart on the site (dev/PIANO_CARRELLO.md, step 6): sync queue, optimistic changes,
// answers out of order, errors, import of the old localStorage cart, expired session.
// axios is mocked: no request leaves the test.
import { describe, it, expect, beforeEach, vi } from "vitest";
import { shallowMount } from "@vue/test-utils";
import axios from "axios";
import { createAppStore as createStore } from "../../resources/js/store.js";
import { createCartQueue, isNewerSnapshot, withOverrides } from "../../resources/js/utils/cartSync.js";
import CartNotice from "../../resources/js/components/CartNotice/CartNotice.vue";

vi.mock("axios", () => ({ default: { get: vi.fn(), put: vi.fn(), delete: vi.fn(), post: vi.fn() } }));

const FRIES = { id: 11, name: "Fries", price: 2.5, restaurant_id: 7, restaurant_slug: "mc-donalds", orderable: true };
const BURGER = { id: 12, name: "Burger", price: 8, restaurant_id: 7, restaurant_slug: "mc-donalds", orderable: true };
const SUSHI = { id: 30, name: "Sushi", price: 12, restaurant_id: 9, restaurant_slug: "sushi-bar", orderable: true };

// an answer of /api/cart with these [plate, quantity] lines
const answer = (lines, { version = 1, owner = "guest", notices = [] } = {}) => ({
    data: {
        cart: {
            owner,
            version,
            restaurant: lines.length ? { id: lines[0][0].restaurant_id, slug: lines[0][0].restaurant_slug, name: "R" } : null,
            items: lines.map(([plate, quantity]) => ({ plate_id: plate.id, quantity, line_total: quantity * plate.price, plate })),
            count: lines.reduce((acc, [, q]) => acc + q, 0),
            total: lines.reduce((acc, [p, q]) => acc + p.price * q, 0)
        },
        notices
    }
});
const httpError = (status, data = {}) => Object.assign(new Error(`HTTP ${status}`), { response: { status, data } });
const deferred = () => {
    let resolve, reject;
    const promise = new Promise((res, rej) => ((resolve = res), (reject = rej)));
    return { promise, resolve, reject };
};
const lines = store => store.getters.cartLines.map(l => [l.plate.id, l.quantity]);
const flush = () => new Promise(r => setTimeout(r, 0));

let store;
const serverStore = async (initial = answer([])) => {
    axios.get.mockResolvedValueOnce(initial);
    await store.dispatch("loadCart");
    return store;
};

beforeEach(() => {
    localStorage.clear();
    vi.restoreAllMocks();
    axios.get.mockReset();
    axios.put.mockReset();
    axios.delete.mockReset();
    axios.post.mockReset();
    vi.spyOn(console, "error").mockImplementation(() => {});
    store = createStore();
});

describe("sync queue and pure helpers", () => {
    it("runs jobs one at a time; a waiting job of the same key is replaced; a reset drops the waiting ones", async () => {
        const queue = createCartQueue();
        const calls = [];
        const first = deferred();
        queue.push("a", async () => {
            calls.push("a1");
            await first.promise;
        });
        queue.push("a", async () => calls.push("a2"));
        queue.push("a", async () => calls.push("a3"));
        queue.push("b", async () => calls.push("b"));
        expect(queue.busy()).toBe(true);
        expect(calls).toEqual(["a1"]);
        first.resolve();
        await queue.idle();
        expect(calls).toEqual(["a1", "a3", "b"]);

        const second = deferred();
        queue.push("a", async () => {
            calls.push("a4");
            await second.promise;
        });
        queue.push("b", async () => calls.push("dropped"));
        queue.push("*", async () => calls.push("reset"), { reset: true });
        second.resolve();
        await queue.idle();
        expect(calls.slice(3)).toEqual(["a4", "reset"]);
        expect(queue.busy()).toBe(false);
    });

    it("withOverrides changes quantities in place, removes zeros and appends new plates", () => {
        const confirmed = [{ plate: FRIES, quantity: 1 }, { plate: BURGER, quantity: 2 }];
        const overrides = { 12: { plate: BURGER, quantity: 0 }, 11: { plate: FRIES, quantity: 4 }, 30: { plate: SUSHI, quantity: 1 } };
        expect(withOverrides(confirmed, overrides).map(l => [l.plate.id, l.quantity])).toEqual([[11, 4], [30, 1]]);
    });

    it("isNewerSnapshot keeps the newest cart of an owner and always takes another owner's", () => {
        expect(isNewerSnapshot(null, { owner: "guest", version: 0 })).toBe(true);
        expect(isNewerSnapshot({ owner: "user", version: 5 }, { owner: "user", version: 4 })).toBe(false);
        expect(isNewerSnapshot({ owner: "user", version: 5 }, { owner: "guest", version: 1 })).toBe(true);
    });
});

describe("loading the cart", () => {
    it("404: the server cart is off and the local cart is used", async () => {
        store.commit("addPlate", { ...FRIES });
        axios.get.mockRejectedValueOnce(httpError(404));
        expect(store.getters.cartReady).toBe(false);
        await store.dispatch("loadCart");

        expect(axios.get).toHaveBeenCalledWith("/api/cart");
        expect(store.state.cartMode).toBe("local");
        expect(store.getters.cartReady).toBe(true);
        expect(lines(store)).toEqual([[11, 1]]);
        expect(console.error).not.toHaveBeenCalled();
        expect(axios.post).not.toHaveBeenCalled();
    });

    it("200: the lines come from the server, with its notices", async () => {
        await serverStore(answer([[FRIES, 2]], { notices: [{ code: "merged", params: {} }] }));

        expect(store.state.cartMode).toBe("server");
        expect(lines(store)).toEqual([[11, 2]]);
        expect(store.getters.cartAmount).toBe(5);
        expect(store.getters.cartRestaurant).toEqual({ id: 7, slug: "mc-donalds" });
        expect(store.state.cartNotices.map(n => n.code)).toEqual(["merged"]);
    });

    it("the old localStorage cart is imported once, then removed", async () => {
        store.commit("addPlate", { ...FRIES });
        store.commit("addPlate", { ...FRIES });
        axios.post.mockResolvedValueOnce(answer([[FRIES, 2]], { version: 3 }));
        await serverStore();

        expect(axios.post).toHaveBeenCalledWith("/api/cart/import", { items: [{ plate_id: 11, quantity: 2 }] });
        expect(store.state.cart).toEqual([]);
        expect(lines(store)).toEqual([[11, 2]]);
        expect(JSON.parse(localStorage.getItem("vuex")).cart).toEqual([]);
    });

    it("a failed import keeps the local cart for the next load", async () => {
        store.commit("addPlate", { ...FRIES });
        axios.post.mockRejectedValueOnce(new Error("Network Error"));
        await serverStore();

        expect(store.state.cart).toHaveLength(1);
        expect(lines(store)).toEqual([]);
    });

    it("network error on the first load: local cart and an offline message", async () => {
        axios.get.mockRejectedValueOnce(new Error("Network Error"));
        await store.dispatch("loadCart");

        expect(store.state.cartMode).toBe("local");
        expect(store.state.cartNotices.map(n => n.code)).toEqual(["offline"]);
    });

    it("the server cart is never saved in localStorage, nor the open warning", async () => {
        await serverStore(answer([[FRIES, 1]]));
        store.commit("changeWarn");
        const saved = JSON.parse(localStorage.getItem("vuex"));

        expect(saved.serverCart).toBeUndefined();
        expect(saved.cartMode).toBeUndefined();
        expect(saved.warn).toBeUndefined();
    });
});

describe("changing the server cart", () => {
    it("shows the change at once, then sends the absolute quantity and takes the answer", async () => {
        await serverStore();
        const put = deferred();
        axios.put.mockReturnValueOnce(put.promise);

        const done = store.dispatch("addOne", FRIES);
        await flush();
        expect(lines(store)).toEqual([[11, 1]]);
        expect(axios.put).toHaveBeenCalledWith("/api/cart/items/11", { quantity: 1 });

        put.resolve(answer([[FRIES, 1]], { version: 2 }));
        await done;
        expect(store.state.cartOverrides).toEqual({});
        expect(store.state.serverCart.version).toBe(2);
        expect(lines(store)).toEqual([[11, 1]]);
    });

    it("a burst of clicks sends the first and then only the last quantity", async () => {
        await serverStore();
        const first = deferred();
        axios.put.mockReturnValueOnce(first.promise).mockResolvedValueOnce(answer([[FRIES, 3]], { version: 3 }));

        store.dispatch("addOne", FRIES);
        await flush();
        store.dispatch("addOne", FRIES);
        await flush();
        const last = store.dispatch("addOne", FRIES);
        await flush();
        expect(store.getters.portionsOf(11)).toBe(3);

        first.resolve(answer([[FRIES, 1]], { version: 2 }));
        await last;
        await store.dispatch("cartIdle");
        expect(axios.put.mock.calls.map(c => c[1].quantity)).toEqual([1, 3]);
        expect(store.getters.portionsOf(11)).toBe(3);
    });

    it("an older answer does not overwrite a newer cart", async () => {
        await serverStore(answer([[FRIES, 2]], { version: 5 }));
        store.commit("setServerCart", answer([[FRIES, 1]], { version: 4 }).data.cart);
        expect(store.getters.portionsOf(11)).toBe(2);
    });

    it("removing the last portion sends a DELETE", async () => {
        await serverStore(answer([[FRIES, 1]]));
        axios.delete.mockResolvedValueOnce(answer([], { version: 2 }));
        await store.dispatch("removeOne", FRIES);

        expect(axios.delete).toHaveBeenCalledWith("/api/cart/items/11");
        expect(lines(store)).toEqual([]);
    });

    it("emptying the cart hides every line at once and sends one DELETE", async () => {
        await serverStore(answer([[FRIES, 1], [BURGER, 2]]));
        const del = deferred();
        axios.delete.mockReturnValueOnce(del.promise);

        const done = store.dispatch("clearCart");
        await flush();
        expect(lines(store)).toEqual([]);
        expect(axios.delete).toHaveBeenCalledWith("/api/cart");

        del.resolve(answer([], { version: 2 }));
        await done;
        expect(store.state.cartOverrides).toEqual({});
        expect(lines(store)).toEqual([]);
    });

    it("a plate of another restaurant opens the warning without asking the server", async () => {
        await serverStore(answer([[FRIES, 1]]));
        expect(await store.dispatch("addOne", SUSHI)).toBe(false);
        expect(store.state.warn).toBe(true);
        expect(axios.put).not.toHaveBeenCalled();
    });

    it("409 (another tab changed restaurant): the server cart is shown and the warning opens", async () => {
        await serverStore();
        axios.put.mockRejectedValueOnce(httpError(409, { code: "other_restaurant", message: "x", ...answer([[SUSHI, 1]], { version: 4 }).data }));
        await store.dispatch("addOne", FRIES);

        expect(lines(store)).toEqual([[30, 1]]);
        expect(store.state.warn).toBe(true);
    });

    it("422 with a code: the change is undone and the server message shown", async () => {
        await serverStore(answer([[FRIES, 1]]));
        axios.put.mockRejectedValueOnce(
            httpError(422, { code: "unavailable", message: "Fries cannot be ordered right now.", ...answer([[FRIES, 1]], { version: 2 }).data })
        );
        await store.dispatch("addOne", FRIES);

        expect(lines(store)).toEqual([[11, 1]]);
        expect(store.state.cartNotices).toMatchObject([{ code: "server", params: { message: "Fries cannot be ordered right now." } }]);
    });

    it("network error: the change is undone and the offline message shown", async () => {
        await serverStore(answer([[FRIES, 1]]));
        axios.put.mockRejectedValueOnce(new Error("Network Error"));
        await store.dispatch("addOne", FRIES);

        expect(lines(store)).toEqual([[11, 1]]);
        expect(store.state.cartNotices.map(n => n.code)).toEqual(["offline"]);
    });

    it("419 (expired CSRF token): a GET renews it and the change is sent once more", async () => {
        await serverStore();
        axios.put.mockRejectedValueOnce(httpError(419)).mockResolvedValueOnce(answer([[FRIES, 1]], { version: 2 }));
        axios.get.mockResolvedValueOnce(answer([]));
        await store.dispatch("addOne", FRIES);

        expect(axios.put).toHaveBeenCalledTimes(2);
        expect(lines(store)).toEqual([[11, 1]]);
        expect(store.state.cartNotices).toEqual([]);
    });

    it("offline: nothing is sent and the message is shown", async () => {
        await serverStore();
        store.commit("setCartOffline", true);
        await store.dispatch("addOne", FRIES);

        expect(axios.put).not.toHaveBeenCalled();
        expect(lines(store)).toEqual([]);
        expect(store.state.cartNotices.map(n => n.code)).toEqual(["offline"]);
    });

    it("a change made while the cart loads waits for it", async () => {
        const get = deferred();
        axios.get.mockReturnValueOnce(get.promise);
        axios.put.mockResolvedValueOnce(answer([[FRIES, 3]], { version: 6 }));
        const loading = store.dispatch("loadCart");
        const adding = store.dispatch("addOne", FRIES);

        get.resolve(answer([[FRIES, 2]], { version: 5 }));
        await loading;
        await adding;
        expect(axios.put).toHaveBeenCalledWith("/api/cart/items/11", { quantity: 3 });
        expect(store.state.cart).toEqual([]);
    });

    it("expired session: a guest cart while logged in logs the site out with a message", async () => {
        await serverStore(answer([[FRIES, 1]], { owner: "user" }));
        store.commit("setUser", { id: 1, name: "Mario" });
        axios.put.mockResolvedValueOnce(answer([[FRIES, 2]], { owner: "guest", version: 1 }));
        await store.dispatch("addOne", FRIES);

        expect(store.state.user).toBeNull();
        expect(store.state.cartNotices.map(n => n.code)).toEqual(["sessionExpired"]);
        expect(store.state.serverCart.owner).toBe("guest");
    });
});

describe("leaving the page", () => {
    it("changes not yet confirmed are sent again with keepalive on pagehide", async () => {
        const fetch = vi.fn(() => Promise.resolve({}));
        vi.stubGlobal("fetch", fetch);
        document.cookie = "XSRF-TOKEN=abc%3D";
        await serverStore(answer([[BURGER, 1]]));
        store.dispatch("watchCart");
        axios.put.mockReturnValueOnce(deferred().promise);
        axios.delete.mockReturnValueOnce(deferred().promise);
        store.dispatch("setQuantity", { plate: FRIES, quantity: 2 });
        store.dispatch("removeOne", BURGER);
        await flush();

        window.dispatchEvent(new Event("pagehide"));

        expect(fetch).toHaveBeenCalledWith("/api/cart/items/11", expect.objectContaining({
            method: "PUT", keepalive: true, body: JSON.stringify({ quantity: 2 }),
            headers: expect.objectContaining({ "X-XSRF-TOKEN": "abc=" })
        }));
        expect(fetch).toHaveBeenCalledWith("/api/cart/items/12", expect.objectContaining({ method: "DELETE", keepalive: true }));
        vi.unstubAllGlobals();
    });
});

describe("CartNotice", () => {
    it("shows the messages, the server ones as they come, and closes them", async () => {
        await serverStore(answer([], { notices: [{ code: "replaced", params: { restaurant: "Sushi Bar" } }] }));
        store.commit("pushCartNotice", { id: 99, code: "server", params: { message: "Fries cannot be ordered right now." } });
        const wrapper = shallowMount(CartNotice, { global: { plugins: [store] } });

        const texts = wrapper.findAll(".cart-notice-text").map(e => e.text());
        expect(texts[0]).toContain("Sushi Bar");
        expect(texts[1]).toBe("Fries cannot be ordered right now.");

        await wrapper.find(".cart-notice-close").trigger("click");
        expect(store.state.cartNotices).toHaveLength(1);
    });
});
