import Vue from "vue";
import Vuex from "vuex";
import axios from "axios";

Vue.use(Vuex);

const store = () => {
    return new Vuex.Store({
        state: {
            Alltypes: [],
            // types that user is currently selecting for getting restaurants
            typesSelected: [],
            // restaurant associated with selected types
            RestByTypes: []
        },
        getters: {},
        mutations: {
            // accesso all'app
            addType(state, typeId) {
                state.typesSelected.push(typeId);
            },
            pullType(state, typeId) {
                state.typesSelected = [
                    ...state.typesSelected.filter(e => e != typeId)
                ];
            },
            fillTypesArray(state, apiResult) {
                state.Alltypes = apiResult;
            },
            fillRestByTypesArray(state, apiResult) {
                state.RestByTypes = apiResult;
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
            getRestaurants({ state, commit }) {
                axios
                    .get(
                        `http://127.0.0.1:8000/api/restaurants/${state.typesSelected}`
                    )
                    .then(r => commit("fillRestByTypesArray", r.data))
                    .catch(r => console.log(r));
            }
        }
    });
};

export default store;
