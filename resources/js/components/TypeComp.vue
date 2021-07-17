<template>
    <div>
        <span>{{ typesSelected }}</span>
        <a :class="{ clicked: checked }" @click="toggle(typeId)" href="#">{{
            typeName
        }}</a>
    </div>
</template>

<script>
import { mapMutations, mapState } from "vuex";

export default {
    name: "TypeComp",
    props: {
        typeName: String,
        typeId: Number
    },
    data() {
        return {
            checked: false
        };
    },
    created() {},
    methods: {
        ...mapMutations(["addType", "pullType"]),
        toggle(typeId) {
            this.checked = !this.checked;
            this.checked
                ? this.$store.commit("addType", typeId)
                : this.$store.commit("pullType", typeId);
            this.$store.dispatch("getRestaurants");
        }
    },
    computed: {
        ...mapState(["typesSelected"])
    }
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

a {
    font-size: 28px;
    font-weight: 600;
    text-decoration: none;
    background: #f9fafa;
    color: #2e3333;
    margin-right: 10px;
    padding: 5px;
    margin-bottom: 10px;
    &.clicked {
        color: blue;
    }
}
</style>
