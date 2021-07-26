<template>
    <section>
        <Jumbotron />
        <div class="cont">
        <TypeSelection />
        <RestByTypes />
    </div>
    </section>
</template>

<script>
import Jumbotron from "../components/Jumbotron.vue";
import TypeSelection from "../components/TypeSelection.vue";
import RestByTypes from "../components/RestByTypes.vue";
import { mapGetters } from "vuex";

export default {
    name: "Home",
    components: {
        Jumbotron,
        TypeSelection,
        RestByTypes
    },
    computed: {
        ...mapGetters(["selectedTypesLenght"])
    }
};
</script>

<style lang="scss" scoped>
// those are the reusable partial for very general
//  stuff that we bring from the back office zone
@import "../../sass/variables";
@import "../../sass/mixins";
@import "../../sass/reset";
@import "../../sass/utilities";

div .container {
    max-width: 1070px;
    a {
        color: white;
    }

    h2 {
        font-weight: bold;
    }
}
</style>
