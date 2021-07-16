<template>
    <div class="cont">
        <h4>
            The home component and route follows...
        </h4>
        <br />
        <br />
        <h1>
            This is the view you will get with any route that does not match the
            admin prefix...from here it strat's the singol page application.
            <br />
            <br />
            <em> Already scaffolded with love &hearts; Vue.js</em>
        </h1>
        <TypeSelection />
    </div>
</template>

<script>
import TypeSelection from "./TypeSelection.vue";
export default {
    name: "Home",
    components: {
        TypeSelection
    },
    props: {
        types: Array
    },
    data() {
        return {};
    },
    methods: {}
};
</script>

<style lang="scss" scoped>
// those are the reusable partial for very general
//  stuff that we bring from the back office zone
@import "../../sass/variables";
@import "../../sass/mixins";
@import "../../sass/reset";
@import "../../sass/utilities";
</style>
