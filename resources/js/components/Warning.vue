<template>
    <section>
        <div>
            <h1 class="mb-5">
                You can't purchase plates from different restaurants in a single
                order...
            </h1>
            <button @click="empty()" class="btn btn-col2 btn-block mb-3">
                empty the cart and stat purchasing here
            </button>
            <router-link
                @click.native="ridirect()"
                class="btn btn-brand btn-block"
                :to="{
                    name: 'restMenu',
                    params: { id: cart[0] ? cart[0].restaurant_id : 1 }
                }"
            >
                go back to restaurant where I was ordering
            </router-link>
        </div>
    </section>
</template>

<script>
import { mapActions, mapMutations, mapState } from "vuex";

export default {
    name: "Warning",
    props: {
        warn: Boolean
    },
    computed: {
        ...mapState(["cart"])
    },
    methods: {
        ...mapMutations["emptyCart, changeWarn"],
        ...mapActions(["getMenuAndDetails"]),
        //
        empty() {
            this.$store.commit("changeWarn");
            this.$store.commit("emptyCart");
        },
        ridirect() {
            this.$store.commit("changeWarn");
            this.$store.dispatch("getMenuAndDetails", {
                id: this.$route.params.id
            });
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

// ******************
section {
    height: 100%;
    width: 100vw;
    position: fixed;
    top: 0;
    left: 0;
    background-color: rgba(255, 255, 255, 0.25);
    div {
        border-radius: 10px;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 70%;
        padding: 40px;
        background-color: #fff;
        opacity: 1;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.07), 0 2px 4px rgba(0, 0, 0, 0.07),
            0 4px 8px rgba(0, 0, 0, 0.07), 0 8px 16px rgba(0, 0, 0, 0.07),
            0 16px 32px rgba(0, 0, 0, 0.07), 0 32px 64px rgba(0, 0, 0, 0.07);
        h1 {
            font-weight: 700;
            color: #444;
        }
    }
    .btn {
        &.btn-col2 {
            background: $col2;
        }
        &.btn-brand {
            background: $brand;
        }
        color: #fff;
        font-weight: 700;
        transition: transform 0.4s;
        &:hover {
            transform: scale(1.01);
            color: #fff;
        }
    }
}
</style>
