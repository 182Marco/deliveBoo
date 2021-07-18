<template>
    <div class="container">
        <h2>Cercavi qualcosa?</h2>
        <div class="types-container">
            <ul>
                <li v-for="type in alltypes" :key="`id_of_type${type.id}`">
                    <TypeComp :typeName="type.name" :typeId="type.id" />
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import { mapState } from "vuex";
import TypeComp from "../components/TypeComp.vue";

export default {
    name: "TypeSelection",
    components: {
        TypeComp
    },
    props: {
        types: Array
    },
    data() {
        return {};
    },
    computed: {
        ...mapState(["Alltypes"])
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
    font-size: 40px;
}

.types-container {
    display: flex;
    padding: 20px 0px;
}
</style>
