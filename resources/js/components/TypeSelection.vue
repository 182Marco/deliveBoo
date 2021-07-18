<template>
    <div class="container">
        <h2>Select by type a list of restaurants:</h2>
        <small> types selected: {{ selectedTypesLenght }}</small>
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
import { mapState, mapGetters } from "vuex";
import TypeComp from "./TypeComp.vue";

export default {
    name: "TypeSelection",
    components: {
        TypeComp
    },
    data() {
        return {};
    },
    computed: {
        ...mapState(["alltypes"]),
        ...mapGetters(["selectedTypesLenght"])
    },
    methods: {}
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
}
</style>
