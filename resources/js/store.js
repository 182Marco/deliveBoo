import Vue from "vue";
import Vuex from "vuex";
import axios from "axios";
import createPersistedState from "vuex-persistedstate";

Vue.use(Vuex);

const store = () => {
    return new Vuex.Store({
        plugins: [createPersistedState()],
        state: {
            alltypes: [],
            // types that user is currently selecting for getting restaurants
            typesSelected: [],
            // restaurant associated with selected types
            restByTypes: [],
            // restaurant  menu and detail
            restaurant: {},
            cart: [],
            // remove duplicate from cart
            // show warn (can't purchase more than one restaurant)
            warn: false,
            total: 0,
            menuMobile: false,
            // ------------//
            restPagination: {}
        },
        getters: {
            selectedTypesLenght: state => {
                return state.typesSelected.length;
            },
            cartLenght: state => {
                return state.cart.length;
            }
        },
        mutations: {
            // get all types at create
            fillTypesArray(state, apiResult) {
                state.alltypes = apiResult;
            },
            // manage array of selected types
            addType(state, typeId) {
                state.typesSelected.push(typeId);
            },
            pullType(state, typeId) {
                state.typesSelected = [
                    ...state.typesSelected.filter(e => e != typeId)
                ];
            },
            // empty type selected
            cleanSelectedTypes(state) {
                state.typesSelected = [];
            },
            // fiil restbytypes with results from axicall in action
            fillRestByTypesArray(state, apiResult) {
                state.restByTypes = apiResult;
            },
            // fiil state obj restaurant(single restaurant detail and menu)
            //  with results from axicall in action
            fillRest(state, apiResult) {
                state.restaurant = apiResult;
            },
            // manage array of plate in cart
            addPlate(state, plateObj) {
                state.cart.push(plateObj);
            },
            removePlate(state, plateObj) {
                if (state.cart.filter(e => e.id == plateObj.id).length) {
                    const idx = state.cart.indexOf(plateObj);
                    if (idx >= 0) {
                        state.cart.splice(idx, 1);
                    }
                }
            },
            emptyCart(state) {
                state.cart = [];
            },
            changeWarn(state) {
                state.warn = !state.warn;
            },
            changeTotal(state) {
                state.total = 0;
                state.cart.forEach(e => (state.total += e.price));
            },
            toggleMenuMobile(state) {
                state.menuMobile = !state.menuMobile;
            },
            emptyCart(state) {
                state.cart = [];
            },
            // fill pagintation of restaurants
            // (to pass more than one payload you pass obj)
            setRestPagination(state, ObjPayload) {
                state.restPagination = {
                    current: ObjPayload.current,
                    last: ObjPayload.last
                };
            }
        },
        actions: {
            // axicall on created get all types
            getTypes({ commit }) {
                axios
                    .get("http://127.0.0.1:8000/api/types")
                    .then(r => commit("fillTypesArray", r.data.types))
                    .catch(r => console.log(r));
            },
            // axicall for restaurant matching selected typesSelected array
            getRestaurants({ state, getters, commit }, page) {
                if (getters.selectedTypesLenght) {
                    axios
                        .get(
                            `http://127.0.0.1:8000/api/restaurants/${state.typesSelected}?page=${page}`
                        )
                        .then(r => {
                            commit("fillRestByTypesArray", r.data.data);
                            commit("setRestPagination", {
                                // (to pass more than one payload you pass obj)
                                current: r.data.current_page,
                                last: r.data.last_page
                            });
                        })
                        .catch(r => console.log(r));
                }
            },
            getMenuAndDetails({ commit }, rest) {
                axios
                    .get(`http://127.0.0.1:8000/api/restaurantsMenu/${rest.id}`)
                    .then(r => commit("fillRest", r.data))
                    .catch(r => console.log(r));
            }
        }
    });
};

export default store;
