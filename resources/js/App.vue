<template>
    <div>
        <Header />
        <router-view :types="types">
            <!-- component matched by the route will be rendered here -->
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
    },
    data() {
        return {
            types: [],
            query: [1, 2, 3]
        };
    },
    created() {
        this.getTypes();
    },
    methods: {
        getTypes() {
            axios
                .get("http://127.0.0.1:8000/api/types")
                .then(r => {
                    this.types = r.data.types;
                })
                .catch(r => console.log(r));
        }
        // getRestaurants(id) {
        //     axios
        //         .get(`http://127.0.0.1:8000/api/restaurants/${this.query}`)
        //         .then(r => console.error(r.data))
        //         .catch(r => console.log(r));
        // }
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
