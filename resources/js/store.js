import { createStore } from "vuex";
import createPersistedState from "vuex-persistedstate";
import { fetchRestaurantMenu, fetchRestaurantsByTypes, fetchTypes } from "./api";
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
    menuPagination: {}
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
    // removes that very portion (same reference): a plate not in the cart is ignored
    removePlate: (state, plate) => {
        const index = state.cart.indexOf(plate);
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

// a new store for each app (and test): the state is restored from localStorage ("vuex")
const createAppStore = () =>
    createStore({
        plugins: [createPersistedState()],
        state,
        getters,
        mutations,
        actions
    });

export { createAppStore };
