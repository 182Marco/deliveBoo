<template>
    <div class="cont">
        <h1>
            Here only
            <span
                >{{ alltypes.filter(e => e.id == $route.params.id)[0].name
                }}<!-- if starred restaurants in title do not say restaurants again -->{{
                    $route.params.id == 4 ? "s:" : " restaurants:"
                }}</span
            >
        </h1>
        <RestByTypes />
        <div class="btn-box">
            <router-link
                :to="{ name: 'home' }"
                class="btn btn-success btn-small txt"
                @click.native="scroll()"
                >Select more types</router-link
            >
        </div>
    </div>
</template>

<script>
import { mapState } from "vuex";
import RestByTypes from "../components/RestByTypes.vue";

export default {
    name: "singleTypeRest",
    components: {
        RestByTypes
    },
    created() {
        this.apiCallWithQuryInUrl();
    },
    watch: {
        "$route.params.id": {
            handler() {
                this.apiCallWithQuryInUrl();
            }
        }
    },
    computed: {
        ...mapState(["alltypes"])
    },

    methods: {
        apiCallWithQuryInUrl() {
            this.$store.dispatch("getRestaurants", {
                id: this.$route.params.id
            });
        },
        scroll() {
            window.scrollTo(0, 1000);
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

.cont {
    @include media-desk-first(s-desktop) {
        padding: 0 20px;
    }
}
h1 {
    margin-top: 80px;
    margin-bottom: 20px;
    font-weight: 700;
    font-size: 2.2rem;
}

.btn-box {
    @include media-desk-first(s-desktop) {
        margin: 25px 0;
        margin-left: 20px;
    }
}

.btn.btn-success.btn-small {
    background-color: $col2;
    color: white;
    font-weight: 700;
    border: none;
    transition: background-color 0.4s;
    margin-bottom: 21px;
    @include media-desk-first(s-phone) {
        margin-left: 20px;
    }
    &:hover {
        animation: tremble 0.4s;
        background-color: $brand;
    }
}

.txt {
    padding-right: 15px;
    margin: 10px 0;
    @include media-desk-first(s-tablet) {
        margin-top: 0px;
    }
}
</style>
