<template>
    <div class="cont">
        <h1 class="font-weight-bold">
            Well done! You successfully send your payment!Your order has been
            confirmed
        </h1>
    </div>
</template>

<script>
import { mapActions, mapState } from "vuex";

export default {
    name: "SuccessPage",
    components: {},
    data() {
        return {};
    },
    created() {},
    computed: {
        ...mapState(["restaurant", "cart", "warn"])
    },
    methods: {
        ...mapActions["getMenuAndDetails"]
        //
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
</style>
