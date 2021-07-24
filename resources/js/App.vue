<template>
    <div class="app">
        <Header />
        <div class="wrap">
            <router-view>
                <!-- component matched by the route will be rendered here -->
            </router-view>
            <div class="cont"></div>
        </div>
        <Content class="main-content" />
        <Footer class="footer" />
    </div>
</template>

<script>
import Header from "./components/Header.vue";
import Content from "./components/Content.vue";
import Footer from "./components/Footer.vue";
import { mapState, mapActions } from "vuex";

//*** */
export default {
    name: "App",
    components: {
        Header,
        Content,
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
// pagination tool bellow
// classes from framewor
// visibile in dev tools
// font import from google font
@import "./../sass/paginationFooterFront";
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

article {
    box-shadow: 3px 4px 6px 4px #5acdbe18;
    padding: 20px 10px;
}
</style>
