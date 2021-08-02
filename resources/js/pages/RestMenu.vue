<template>
    <div class="cont">
        <div class="items-box">
            <div class="img-box">
                <img :src="restaurant.img" :alt="restaurant.name" />
            </div>
            <div class="txt box">
                <h1>
                    Restaurant: <em>{{ restaurant.name }}</em>
                </h1>
                <p>
                    <strong class="little-title">address: </strong>
                    {{ restaurant.address }},
                    {{ restaurant.city }}
                </p>
                <p>
                    <strong class="little-title">phone: </strong>
                    {{ restaurant.phone }}
                </p>
                <strong class="little-title">Types: </strong>
                <span
                    class="label"
                    v-for="type in restaurant.types"
                    :key="`tId${type.id}`"
                    >{{ type.name }}</span
                >
                <!-- do not display if there's the warn (you can't purchese from to restaurant in the same order) -->
                <!-- pagination element -->
            </div>
        </div>
        <h2>Menu:</h2>
        <!-- ****** menu ********* -->
        <Plate
            v-for="(plate, i) in restaurant.plates"
            :key="`_${i}`"
            :plate="plate"
        />
        <div v-show="!warn" class="card-footer pagination tool pb-0 pt-3"></div>
    </div>
</template>

<script>
import { mapActions, mapState } from "vuex";
import Plate from "../components/Plate.vue";

export default {
    name: "RestMenu",
    components: {
        Plate
    },
    data() {
        return {
            // pagination data
            pageOfItems: []
        };
    },
    created() {
        this.callDetailAndMenuAction();
    },
    computed: {
        ...mapState(["restaurant", "cart", "warn"])
    },
    methods: {
        ...mapActions["getMenuAndDetails"],
        //
        callDetailAndMenuAction() {
            this.$store.dispatch("getMenuAndDetails", {
                id: this.$route.params.id
            });
        },
        // pagination method
        onChangePage(pageOfItems) {
            // update page of items
            this.pageOfItems = pageOfItems;
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
