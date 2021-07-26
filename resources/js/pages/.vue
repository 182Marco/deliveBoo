<template>
    <div class="cont">
        <h1>
            Restaurant: <em>{{ restaurant.name }}</em>
        </h1>
        <img :src="restaurant.img" :alt="restaurant.name" />
        <p>
            <strong>address: </strong> {{ restaurant.address }},
            {{ restaurant.city }}
        </p>
        <p><strong>phone: </strong> {{ restaurant.phone }}</p>
        <span
            class="label"
            v-for="type in restaurant.types"
            :key="`tId${type.id}`"
            >{{ type.name }}</span
        >
        <!-- ****** menu ********* -->
        <h2>Menu:</h2>
        <Plate
            v-for="(plate, i) in pageOfItems"
            :key="`_${i}`"
            :plate="plate"
        />
        <!-- do not display if there's the warn (you can't purchese from to restaurant in the same order) -->
        <!-- pagination element -->
        <div v-show="!warn" class="card-footer pb-0 pt-3">
            <jw-pagination
                :pageSize="4"
                :items="restaurant.plates"
                @changePage="onChangePage"
            ></jw-pagination>
        </div>
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

h1 {
    font-weight: 700;
    margin-bottom: 40px;
}

h2 {
    font-weight: 700;
    margin-bottom: 50px;
}

h4 {
    font-weight: 700;
    margin-bottom: 20px;
}

img {
    margin-bottom: 10px;
}

p {
    max-width: 10px 0;
    margin-bottom: 10px;
    color: #777;

    strong {
        color: #888;
    }
}

.label {
    display: inline-block;
    background-color: #198dcc;
    color: white;
    border-radius: 5px;
    padding: 5px 10px;
    margin: 8px 15px 50px 0;
}
</style>
