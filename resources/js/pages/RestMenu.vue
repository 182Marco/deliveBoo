<template>
    <div class="cont">
        <div class="items-box">
            <div class="img-box">
                <img
                    :src="restaurant.location.img"
                    :alt="restaurant.location.name"
                />
            </div>
            <div class="txt box">
                <h1>
                    Restaurant: <em>{{ restaurant.location.name }}</em>
                </h1>
                <p>
                    <strong class="little-title">address: </strong>
                    {{ restaurant.location.address }},
                    {{ restaurant.location.city }}
                </p>
                <p>
                    <strong class="little-title">phone: </strong>
                    {{ restaurant.location.phone }}
                </p>
                <strong class="little-title">Types: </strong>
                <span
                    class="label"
                    v-for="type in restaurant.types"
                    :key="`tId${type}`"
                    >{{ type }}</span
                >
            </div>
        </div>
        <h2>Menu:</h2>
        <!-- ****** menu ********* -->
        <Plate
            v-for="(plate, i) in restaurant.menu.data"
            :key="`_${i}`"
            :plate="plate"
            :paginationData="restaurant.menu"
        />
        <!-- pagination element  -->
        <PaginateEl
            :paginate="menuPagination"
            :getMenu="true"
            :slug="restaurant.location.slug"
        />
    </div>
</template>

<script>
import { mapActions, mapState } from "vuex";
import Plate from "../components/Plate.vue";
import PaginateEl from "../components/PaginateEl.vue";

export default {
    name: "RestMenu",
    components: {
        Plate,
        PaginateEl
    },
    created() {
        this.callDetailAndMenuAction();
    },
    computed: {
        ...mapState(["restaurant", "cart", "warn", "menuPagination"])
    },
    methods: {
        ...mapActions["getMenuAndDetails"],
        //
        callDetailAndMenuAction() {
            this.$store.dispatch("getMenuAndDetails", {
                page: 1,
                slug: this.$route.params.slug
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

.cont {
    margin-top: 80px;
}

.txt.box {
    @include media-desk-first(tablet) {
        padding: 0 20px;
    }
}

.items-box {
    display: flex;
    align-items: center;
    @include media-desk-first(l-tablet) {
        flex-direction: column;
        margin: 0;
        margin-bottom: 30px;
    }
}

h1 {
    margin-bottom: 20px;
    font-weight: 700;
    font-size: 2.3rem;
    line-height: 36px;
}

h2 {
    font-size: 2rem;
    font-weight: 700;
    margin-top: 10px 0;
    background-color: #f7f7f7;
    padding-bottom: 0px;
    padding-left: 30px;
    padding-top: 20px;
}

h4 {
    font-weight: 700;
    margin-bottom: 20px;
}

.img-box {
    margin-right: 30px;
    width: 370px;
    height: 280px;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 10px;
    overflow: hidden;
    @include media-desk-first(desktop) {
        padding-left: 20px;
    }
    @include media-desk-first(tablet) {
        padding-left: 0;
        width: 80vw;
        height: 190px;
    }
    img {
        width: 100%;
        object-fit: cover;
        border-radius: 10px;
    }
}

p {
    max-width: 10px 0;
    margin-bottom: 10px;
}

.little-title {
    display: inline-block;
    margin-right: 20px;
    color: #888;
}

.label {
    display: inline-block;
    background-color: #198dcc;
    color: white;
    border-radius: 5px;
    padding: 5px 10px;
    margin: 8px 15px 0 0;
}
</style>
