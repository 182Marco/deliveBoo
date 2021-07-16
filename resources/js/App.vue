<template>
    <div>
        <Header />
        <router-view :types="types">
            <!-- component matched by the route will be rendered here -->
        </router-view>
        <div class="cont">
            <div v-if="typesSelected.length > 0">{{ RestByTypes }}</div>
            <div v-else>you haven't selected any type</div>
        </div>
        <Footer />
    </div>
</template>

<script>
import Header from "./components/Header.vue";
import Footer from "./components/Footer.vue";
import { mapState, mapActions } from "vuex";

//*** */
export default {
    name: "App",
    components: {
        Header,
        Footer
    },
    data() {
        return {
            types: [],
            query: [1, 2, 3]
        };
    },
    created() {
        this.$store.dispatch("getTypes");
    },
    computed: {
        ...mapState(["Alltypes", "RestByTypes", "typesSelected"])
    },
    methods: {
        ...mapActions(["getTypes"])
    }
};
</script>

<style lang="scss">
// font import from google font
@import url("https://fonts.googleapis.com/css2?family=Quicksand:wght@400;700&display=swap");
* {
    font-family: "Quicksand", sans-serif;
}

// those are the reusable partial for very general
//  stuff that we bring from the back office zone
@import "../sass/variables";
@import "../sass/mixins";
@import "../sass/reset";
@import "../sass/utilities";
</style>
