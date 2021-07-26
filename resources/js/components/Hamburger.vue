<template>
    <div @click="open">
        <div class="hamburger">
            <span class="line"></span>
            <span class="line"></span>
            <span class="line"></span>
        </div>
        <Menumobile />
    </div>
</template>

<script>
import { mapMutations } from "vuex";
import Menumobile from "./Menumobile.vue";

export default {
    name: "Hamburger",
    components: {
        Menumobile
    },
    data() {
        return {};
    },
    methods: {
        ...mapMutations(["toggleMenuMobile"]),
        //
        open() {
            this.$store.commit("toggleMenuMobile");
        }
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

.hamburger {
    width: 27px;
    margin-right: 0;
    cursor: pointer;
    &:hover .line {
        background-color: $col2;
    }
    .line {
        display: block;
        width: 100%;
        height: 4px;
        background-color: $white;
        border-radius: 5px;
        margin-top: 4px;
        transition: background 0.5s;
    }
}
</style>
