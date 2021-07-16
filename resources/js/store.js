import Vue from "vue";
import Vuex from "vuex";
import axios from "axios";

Vue.use(Vuex);

const store = () => {
    return new Vuex.Store({
        state: {
            // accesso all'app
            prova: "vuex è qui!"
        },
        getters: {},
        mutations: {
            // accesso all'app
            loginOk(state) {
                state.loginDone = true;
            },
            accountChosen(state) {
                state.accountDone = true;
            }
        },
        actions: {
            // chiamata axios quando si fa ricerca
            getAllData({ state, commit }) {
                if (state.query !== "") {
                    // chiamata per i film
                    axios
                        .get(
                            `${state.basicUrl}/search${state.apiMv}?api_key=${state.apikey}&query=${state.query}
              &language=${state.language}
            `
                        )
                        .then(res => {
                            // aumentare le props con una favurite true/false
                            res.data.result = [
                                ...res.data.results.map(e => ({
                                    ...e,
                                    favourite: false
                                }))
                            ];
                            commit("setSearchedMovies", res.data.result);
                        });
                }
            }
        }
    });
};

export default store;
