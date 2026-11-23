import { createStore } from "vuex";
import createPersistedState from "vuex-persistedstate";
import { fetchMe, fetchRestaurantMenu, fetchRestaurantsByTypes, fetchTypes, logout } from "./api";
import { cartTotal, toPagination } from "./utils";

const state = () => ({
    alltypes: [],
    // types the user is selecting to filter the restaurants
    typesSelected: [],
    // restaurants matching the selected types
    restByTypes: [],
    // details and menu of the open restaurant
    restaurant: {},
    // one entry per portion
    cart: [],
    // modal "you can't buy from two restaurants in the same order"
    warn: false,
    total: 0,
    menuMobile: false,
    restPagination: {},
    menuPagination: {},
    // logged-in account from /api/me (null = guest); never saved in localStorage
    user: null
});

const getters = {
    selectedTypesLenght: state => state.typesSelected.length,
    cartLenght: state => state.cart.length
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
    changeWarn: state => {
        state.warn = !state.warn;
    },
    changeTotal: state => {
        state.total = cartTotal(state.cart);
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

// everything but the account is kept in localStorage: a stale or shared browser must not show it
const persisted = ({ user, ...rest }) => rest;

// a new store for each app (and test): the state is restored from localStorage ("vuex")
const createAppStore = () =>
    createStore({
        plugins: [createPersistedState({ reducer: persisted })],
        state,
        getters,
        mutations,
        actions
    });

export { createAppStore };
