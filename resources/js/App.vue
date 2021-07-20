<template>
    <div class="app">
        <Header />
        <div class="wrap">
            <router-view>
                <!-- component matched by the route will be rendered here -->
            </router-view>
            <div class="cont"></div>
        </div>
        <Footer class="footer" />
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
    created() {
        this.$store.dispatch("getTypes");
    },
    computed: {
        ...mapState(["alltypes", "restByTypes", "typesSelected"])
    },
    methods: {
        ...mapActions(["getTypes"])
    }
};
</script>

<style lang="scss">
// those are the reusable partial for very general
//  stuff that we bring from the back office zone
@import "./../sass/variables";
@import "./../sass/mixins";
@import "./../sass/reset";
@import "./../sass/utilities";
// font import from google font
@import url("https://fonts.googleapis.com/css2?family=Quicksand:wght@400;700&display=swap");
* {
    font-family: "Quicksand", sans-serif;
}

.app {
    min-height: 100vh;
    width: 100vw;
    display: flex;
    flex-direction: column;
    .wrap {
        padding: 0 20px;
        flex-grow: 1;
    }
}
</style>
