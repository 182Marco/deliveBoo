<template>
    <nav>
        <ul>
            <li class="search-item">
                <div v-show="showSearch" class="input-wrap">
                    <input
                        ref="input"
                        type="text"
                        v-model.trim="query"
                        @keyup="$store.dispatch('getAllData')"
                    />
                    <select v-model="language" name="" id="">
                        <option value="it-IT">it</option>
                        <option value="en-US">en</option>
                    </select>
                </div>
                <i
                    @click="
                        putFocus();
                        $store.dispatch('changeColNav');
                    "
                    class="fas fa-search"
                ></i>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-gift"></i>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-bell"></i>
                </a>
            </li>
            <li>
                <a href="#" class="withArrow">
                    <img
                        src="@/assets/img/marcoMilza.webp"
                        alt="Autore dell' App"
                    />
                    <div class="arrow"></div>
                </a>
            </li>
        </ul>
    </nav>
</template>

<script>
import { mapMutations, mapState, mapActions } from "vuex";
// components
export default {
    name: "NavbarRight",
    props: {},
    data() {
        return {
            showSearch: false,
            navCol: false
        };
    },
    computed: {
        ...mapState(["colNav"]),
        query: {
            get() {
                return this.$store.state.query;
            },
            set(value) {
                this.$store.commit("setQuery", value);
            }
        },
        language: {
            get() {
                return this.$store.state.language;
            },
            set(value) {
                this.$store.commit("setLanguage", value);
            }
        }
    },
    methods: {
        ...mapMutations(["toggleColNav", "setQueryLang"]),
        ...mapActions(["changeColNav"]),
        // ***
        putFocus() {
            this.showSearch = !this.showSearch;
            setTimeout(() => this.$refs.input.focus(), 10);
        },
        sengaPosto() {
            console.log("funzione da scrivere chiamata");
            console.warn(this.query);
            console.log(this.language);
        }
    }
};
</script>
