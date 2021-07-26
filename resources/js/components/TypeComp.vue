<template>
    <div class="cont">
        <button
            :class="{ clicked: typesSelected.includes(this.type.id) }"
            @click="toggle(type.id)"
        >
            {{ type.name }}
        </button>
    </div>
</template>

<script>
import { mapMutations, mapState } from "vuex";

export default {
    name: "TypeComp",
    props: {
        type: Object
    },
    created() {},
    methods: {
        ...mapMutations(["addType", "pullType"]),
        toggle(typeId) {
            this.typesSelected.includes(this.type.id)
                ? this.$store.commit("pullType", typeId)
                : this.$store.commit("addType", typeId);
            //
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

div.cont {
    button {
        font-size: 25px;
        font-weight: 600;
        text-decoration: none;
        background: $foft-back;
        color: $txt-col;
        margin-right: 10px;
        padding: 5px;
        margin-bottom: 10px;
        transition: transform 0.25;
        cursor: pointer;
        border: none;
        &:hover {
            transform: scale(1.05);
        }
        &.clicked {
            color: $brand;
        }
    }
}
</style>
