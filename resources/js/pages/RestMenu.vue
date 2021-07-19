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
            v-for="(plate, i) in restaurant.plates"
            :key="`_${i}`"
            :plate="plate"
        />
    </div>
</template>

<script>
import { mapState } from "vuex";
import Plate from "../components/Plate.vue";

export default {
    name: "RestMenu",
    components: {
        Plate
    },
    computed: {
        ...mapState(["restaurant"])
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
