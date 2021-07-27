<template>
    <section>
        <div>
            <h1 class="mb-5">
                You can't purchase plates from different restaurants in a single
                order...
            </h1>
            <button @click="empty()" class="btn btn-col2 btn-block mb-3">
                Empty the cart and start purchasing here
            </button>
            <router-link
                @click.native="ridirect()"
                class="btn btn-brand btn-block"
                :to="{
                    name: 'restMenu',
                    params: { id: cart[0] ? cart[0].restaurant_id : 1 }
                }"
            >
                Go back to restaurant where I was ordering
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
            window.scrollTo(0, 0);
        },
        ridirect() {
            this.$store.commit("changeWarn");
            this.$store.dispatch("getMenuAndDetails", {
                id: this.$route.params.id
            });
            window.scrollTo(0, 0);
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
    position: fixed;
    top: 0;
    left: 0;
    background-color: rgba(255, 255, 255, 0.25);
    z-index: 100;
    height: 100%;
    width: 100vw;
    div {
        width: 58%;
        border-radius: 10px;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: #fff;
        opacity: 1;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.07), 0 2px 4px rgba(0, 0, 0, 0.07),
            0 4px 8px rgba(0, 0, 0, 0.07), 0 8px 16px rgba(0, 0, 0, 0.07),
            0 16px 32px rgba(0, 0, 0, 0.07), 0 32px 64px rgba(0, 0, 0, 0.07);
        padding: 40px;
        @include media-desk-first(phone) {
            padding: 30px 20px;
            width: 85%;
        }
        h1 {
            font-weight: 700;
            color: #444;
            @include media-desk-first(xs-tablet) {
                font-size: 1.7rem;
            }
            @include media-desk-first(phone) {
                font-size: 1.4rem;
            }
        }
    }
    .btn {
        color: #fff;
        font-weight: 700;
        transition: transform 0.4s;
        @include media-desk-first(phone) {
            font-size: 1rem;
            padding: 10px 20px;
        }
        &.btn-col2 {
            background: $col2;
        }
        &.btn-brand {
            background: $brand;
        }
        &:hover {
            transform: scale(1.01);
            color: #fff;
        }
    }
}
</style>
