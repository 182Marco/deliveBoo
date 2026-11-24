import { createStore } from "vuex";
import createPersistedState from "vuex-persistedstate";
import {
    clearServerCart,
    deleteCartItem,
    fetchCart,
    fetchMe,
    fetchRestaurantMenu,
    fetchRestaurantsByTypes,
    fetchTypes,
    importCart,
    logout,
    putCartItem,
    sendCartItemOnUnload
} from "./api";
import {
    canAddPlate,
    cartLines,
    createCartQueue,
    isNewerSnapshot,
    linesCount,
    linesRestaurant,
    linesTotal,
    snapshotLines,
    toPagination,
    withOverrides
} from "./utils";

// other tabs of the same browser get every new cart at once (dev/PIANO_CARRELLO.md)
const CART_CHANNEL = "foodsy-cart";

const state = () => ({
    alltypes: [],
    // types the user is selecting to filter the restaurants
    typesSelected: [],
    // restaurants matching the selected types
    restByTypes: [],
    // details and menu of the open restaurant
    restaurant: {},
    // local cart (one entry per portion): the cart when the server cart is off (GET /api/cart = 404),
    // otherwise only the old localStorage cart waiting to be imported
    cart: [],
    // "pending" until GET /api/cart answers, then "server" or "local"
    cartMode: "pending",
    // last cart confirmed by the server ({ owner, version, restaurant, items, count, total })
    serverCart: null,
    // changes on their way to the server, shown at once: { [plateId]: { plate, quantity, seq } }
    cartOverrides: {},
    // messages about the cart, shown by CartNotice: [{ id, code, params }]
    cartNotices: [],
    cartOffline: false,
    // modal "you can't buy from two restaurants in the same order"
    warn: false,
    menuMobile: false,
    restPagination: {},
    menuPagination: {},
    // logged-in account from /api/me (null = guest); never saved in localStorage
    user: null
});

const getters = {
    selectedTypesLenght: state => state.typesSelected.length,
    // the cart as every component reads it: lines { plate, quantity }, never the storage
    cartLines: state =>
        state.cartMode === "server"
            ? withOverrides(snapshotLines(state.serverCart), state.cartOverrides)
            : cartLines(state.cart),
    // false until the first answer of the server (the bag page shows "loading", not an empty bag)
    cartReady: state => state.cartMode !== "pending",
    cartCount: (state, getters) => linesCount(getters.cartLines),
    cartAmount: (state, getters) => linesTotal(getters.cartLines),
    // { id, slug } of the restaurant of the cart, null when empty
    cartRestaurant: (state, getters) => linesRestaurant(getters.cartLines),
    portionsOf: (state, getters) => plateId => getters.cartLines.find(l => l.plate.id == plateId)?.quantity ?? 0
};

const mutations = {
    fillTypesArray: (state, types) => {
        state.alltypes = types;
    },
    addType: (state, typeId) => {
        state.typesSelected.push(typeId);
    },
    pullType: (state, typeId) => {
        state.typesSelected = state.typesSelected.filter(e => e != typeId);
    },
    cleanSelectedTypes: state => {
        state.typesSelected = [];
    },
    fillRestByTypesArray: (state, restaurants) => {
        state.restByTypes = restaurants;
    },
    fillRest: (state, restaurant) => {
        state.restaurant = restaurant;
    },
    addPlate: (state, plate) => {
        state.cart.push(plate);
    },
    // removes the LAST portion of that plate (by id): the first one stays, so rows
    // (listed by first portion) keep their position. Not in the cart: ignored
    removePlate: (state, plate) => {
        const index = state.cart.findLastIndex(e => e.id == plate.id);
        if (index >= 0) state.cart.splice(index, 1);
    },
    emptyCart: state => {
        state.cart = [];
    },
    setCartMode: (state, mode) => {
        state.cartMode = mode;
    },
    // answers can arrive out of order: an older cart of the same owner is ignored
    setServerCart: (state, snapshot) => {
        if (isNewerSnapshot(state.serverCart, snapshot)) state.serverCart = snapshot;
    },
    setCartOverride: (state, { plate, quantity, seq }) => {
        state.cartOverrides = { ...state.cartOverrides, [plate.id]: { plate, quantity, seq } };
    },
    // only the change that is done: a newer click on the same plate keeps its own
    dropCartOverride: (state, { plateId, seq }) => {
        if (state.cartOverrides[plateId]?.seq !== seq) return;
        const { [plateId]: _done, ...rest } = state.cartOverrides;
        state.cartOverrides = rest;
    },
    pushCartNotice: (state, notice) => {
        // the same message twice in a row is shown once
        if (state.cartNotices.some(e => e.code === notice.code)) return;
        state.cartNotices = [...state.cartNotices, notice];
    },
    dropCartNotice: (state, id) => {
        state.cartNotices = state.cartNotices.filter(e => e.id !== id);
    },
    setCartOffline: (state, offline) => {
        state.cartOffline = offline;
    },
    changeWarn: state => {
        state.warn = !state.warn;
    },
    toggleMenuMobile: state => {
        state.menuMobile = !state.menuMobile;
    },
    setRestPagination: (state, { current, last }) => {
        state.restPagination = { current, last };
    },
    setMenuPagination: (state, { current, last }) => {
        state.menuPagination = { current, last };
    },
    setUser: (state, user) => {
        state.user = user;
    }
};

// --- cart: the only way components change it (dev/PIANO_CARRELLO.md) ---
// Server mode: a change shows at once (override), then goes to the server through a serial queue;
// every answer brings the whole cart, which replaces the confirmed one. Local mode (server cart off,
// GET /api/cart = 404): the local array, kept in localStorage. One set per store (queue, counters, channel).
const createCartActions = () => {
    const queue = createCartQueue();
    let seq = 0;
    let noticeId = 0;
    // GET /api/cart running: writes wait for it, so nothing is written to the wrong cart
    let loading = null;
    let channel = null;

    const notice = (commit, code, params = {}) => commit("pushCartNotice", { id: ++noticeId, code, params });

    // a cart from the server (answer, error or other tab): the newest one wins
    const applyCart = ({ state, commit, dispatch }, data, { broadcast = true } = {}) => {
        if (!data?.cart) return;
        commit("setServerCart", data.cart);
        (data.notices ?? []).forEach(e => notice(commit, e.code, e.params));
        if (broadcast) channel?.postMessage(data.cart);
        // the session expired: requests are a guest's now, and so is the cart
        if (data.cart.owner === "guest" && state.user) {
            commit("setUser", null);
            notice(commit, "sessionExpired");
        }
    };

    const failed = (context, error) => {
        const { commit, dispatch } = context;
        const answer = error.response?.data;
        applyCart(context, answer);
        if (!error.response) {
            notice(commit, "offline");
        } else if (answer?.code === "other_restaurant") {
            // another tab filled the cart with another restaurant meanwhile
            if (!context.state.warn) commit("changeWarn");
        } else if (error.response.status === 403) {
            // banned: the server closed the session
            dispatch("loadUser");
        } else if (answer?.code) {
            notice(commit, "server", { message: answer.message });
        } else {
            notice(commit, "error");
            dispatch("loadCart");
        }
        if (!answer?.code) console.error(error);
    };

    // sends one change; an expired CSRF token (419) is renewed by a GET and the request tried once more
    const send = async (context, request, { plateIds, seqOf }) => {
        try {
            try {
                applyCart(context, await request());
            } catch (error) {
                if (error.response?.status !== 419) throw error;
                await fetchCart();
                applyCart(context, await request());
            }
        } catch (error) {
            failed(context, error);
        } finally {
            plateIds.forEach(plateId => context.commit("dropCartOverride", { plateId, seq: seqOf }));
        }
    };

    const canWrite = ({ state, commit }) => {
        if (!state.cartOffline) return true;
        notice(commit, "offline");
        return false;
    };

    return {
        // asked on every load: the server cart, or 404 when it is off (local cart)
        loadCart: async context => {
            const { state, commit, dispatch } = context;
            loading = (async () => {
                try {
                    const data = await fetchCart();
                    commit("setCartMode", "server");
                    applyCart(context, data, { broadcast: false });
                    if (state.cart.length) await dispatch("importLocalCart");
                } catch (error) {
                    if (state.cartMode === "pending") commit("setCartMode", "local");
                    if (error.response?.status === 404) return;
                    console.error(error);
                    notice(commit, "offline");
                }
            })();
            await loading;
            loading = null;
        },
        // the cart of the previous site version (localStorage), once: the server takes it only into an empty cart
        importLocalCart: async context => {
            const items = cartLines(context.state.cart).map(l => ({ plate_id: l.plate.id, quantity: l.quantity }));
            try {
                applyCart(context, await importCart(items));
                context.commit("emptyCart");
            } catch (error) {
                // kept in localStorage: tried again at the next load
                console.error(error);
            }
        },
        // absolute number of portions of a plate; 0 removes it
        setQuantity: async (context, { plate, quantity }) => {
            if (loading) await loading;
            const { state, getters, commit } = context;
            const target = Math.max(0, quantity);
            if (state.cartMode !== "server") {
                const current = getters.portionsOf(plate.id);
                for (let i = current; i < target; i++) commit("addPlate", { ...plate });
                for (let i = current; i > target; i--) commit("removePlate", plate);
                return;
            }
            if (!canWrite(context)) return;
            const mySeq = ++seq;
            commit("setCartOverride", { plate, quantity: target, seq: mySeq });
            await queue.push(plate.id, () =>
                send(context, () => (target ? putCartItem(plate.id, target) : deleteCartItem(plate.id)), {
                    plateIds: [plate.id],
                    seqOf: mySeq
                })
            );
        },
        // a plate of another restaurant opens the warning instead (false = not added)
        addOne: async ({ getters, commit, dispatch }, plate) => {
            if (loading) await loading;
            if (!canAddPlate(getters.cartRestaurant, plate)) {
                commit("changeWarn");
                return false;
            }
            await dispatch("setQuantity", { plate, quantity: getters.portionsOf(plate.id) + 1 });
            return true;
        },
        removeOne: async ({ getters, dispatch }, plate) => {
            if (loading) await loading;
            await dispatch("setQuantity", { plate, quantity: getters.portionsOf(plate.id) - 1 });
        },
        clearCart: async context => {
            if (loading) await loading;
            const { state, getters, commit } = context;
            if (state.cartMode !== "server") {
                commit("emptyCart");
                return;
            }
            if (!canWrite(context)) return;
            const mySeq = ++seq;
            // every plate shown or on its way goes to 0 at once; waiting changes are dropped by the queue
            const plates = [...getters.cartLines.map(l => l.plate), ...Object.values(state.cartOverrides).map(o => o.plate)];
            plates.forEach(plate => commit("setCartOverride", { plate, quantity: 0, seq: mySeq }));
            await queue.push("*", () => send(context, clearServerCart, { plateIds: plates.map(p => p.id), seqOf: mySeq }), {
                reset: true
            });
        },
        dropCartNotice: ({ commit }, id) => commit("dropCartNotice", id),
        // other tabs, focus and connection (once per app): the cart stays the same everywhere
        watchCart: context => {
            const { commit, dispatch } = context;
            if (typeof window === "undefined" || channel) return;
            if (typeof BroadcastChannel !== "undefined") {
                channel = new BroadcastChannel(CART_CHANNEL);
                // Node (tests) only: the channel must not keep the process alive
                channel.unref?.();
                channel.onmessage = ({ data }) => applyCart(context, { cart: data, notices: [] }, { broadcast: false });
            }
            commit("setCartOffline", navigator.onLine === false);
            window.addEventListener("offline", () => commit("setCartOffline", true));
            window.addEventListener("online", () => {
                commit("setCartOffline", false);
                dispatch("loadCart");
            });
            document.addEventListener("visibilitychange", () => {
                if (document.visibilityState === "visible" && !queue.busy()) dispatch("loadCart");
            });
            // the page goes away with changes not yet confirmed: they leave anyway (keepalive), one per plate
            window.addEventListener("pagehide", () => {
                if (context.state.cartMode !== "server") return;
                Object.values(context.state.cartOverrides).forEach(({ plate, quantity }) => sendCartItemOnUnload(plate.id, quantity));
            });
        },
        // for the tests: resolves when every change has been answered
        cartIdle: () => queue.idle()
    };
};

const actions = {
    getTypes: async ({ commit }) => {
        try {
            commit("fillTypesArray", await fetchTypes());
        } catch (error) {
            console.error(error);
        }
    },
    // restaurants matching the selected types (nothing to ask without types)
    getRestaurants: async ({ state, getters, commit }, page) => {
        if (!getters.selectedTypesLenght) return;
        try {
            const result = await fetchRestaurantsByTypes(state.typesSelected, page);
            commit("fillRestByTypesArray", result.data);
            commit("setRestPagination", toPagination(result));
        } catch (error) {
            console.error(error);
        }
    },
    // asked on every load: the session lives in the Laravel cookie, not in the store
    loadUser: async ({ commit }) => {
        try {
            commit("setUser", await fetchMe());
        } catch (error) {
            // 403 = banned account, logged out by the server
            commit("setUser", null);
        }
    },
    logoutUser: async ({ commit }) => {
        try {
            await logout();
        } finally {
            commit("setUser", null);
        }
    },
    getMenuAndDetails: async ({ commit }, { slug, page }) => {
        try {
            const restaurant = await fetchRestaurantMenu(slug, page);
            commit("fillRest", restaurant);
            commit("setMenuPagination", toPagination(restaurant.menu));
        } catch (error) {
            console.error(error);
        }
    }
};

// kept in localStorage: not the account (a stale or shared browser must not show it), not the server
// cart (the server has it) and not the open warning; the local cart only matters while the server cart is off
const persisted = ({ user, cartMode, serverCart, cartOverrides, cartNotices, cartOffline, warn, ...rest }) => rest;

// a new store for each app (and test): the state is restored from localStorage ("vuex")
const createAppStore = () =>
    createStore({
        plugins: [createPersistedState({ reducer: persisted })],
        state,
        getters,
        mutations,
        actions: { ...actions, ...createCartActions() }
    });

export { createAppStore };
