<template>
    <div class="cont">
        <h1>Would you like something?</h1>
        <p>Types Selected: {{ selectedTypesLenght }}</p>
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

h1 {
    font-weight: bold;
}

.types-container {
    display: flex;
    padding: 20px 0px;
}

p {
    font-size: 1.3rem;
    margin: 20px 5px 20px;
    color: $col2;
    font-weight: 700;
}
</style>
