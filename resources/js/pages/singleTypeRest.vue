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
            this.$store.dispatch("getRestsSingleType", {
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



h1 {
    margin-top: 80px;
    margin-bottom: 20px;
    font-weight: 700;
    font-size: 2.2rem;
}
</style>
