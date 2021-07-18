<template>
    <div class="cont">
        <h2>Ti va qualcosa?</h2>
        <!-- <small> Generi selezionati: {{ selectedTypesLenght }}</small> -->
        <div class="types-container">
            <ul>
                <li v-for="type in alltypes" :key="`type_id${type.id}`">
                    <TypeComp :type="type" />
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import { mapState, mapGetters, mapMutations, mapActions } from "vuex";
import TypeComp from "./TypeComp.vue";

export default {
    name: "TypeSelection",
    components: {
        TypeComp
    },
    created() {
        // if user press back in history routes get
        // a clean response api in restaurant by types
        this.$store.commit("cleanTypesSelected");
        this.$store.dispatch("getRestaurants");
    },
    methods: {
        ...mapMutations["cleanTypesSelected"],
        ...mapActions["getRestaurants"]
    },
    computed: {
        ...mapState(["alltypes"]),
        ...mapGetters(["selectedTypesLenght"])
    }
};
</script>

<style lang="scss" scoped>
@import "../../sass/variables";
@import "../../sass/mixins";
@import "../../sass/reset";
@import "../../sass/utilities";

h2 {
    font-weight: bold;
}

.types-container {
    display: flex;
    padding: 20px 0px;
}

small {
    font-size: 0.9rem;
    margin: 0px 5px 20px;
}
</style>
