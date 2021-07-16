<template>
    <div>
        <Header />
        <router-view>
            <!-- component matched by the route will be rendered here -->
            <TypeSelection />
        </router-view>
        <Footer />
    </div>
</template>

<script>
import Header from "./components/Header.vue";

import Footer from "./components/Footer.vue";

//*** */
export default {
    name: "App",
    components: {
        Header,
        Footer
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
