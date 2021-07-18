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
        <article
            class="plate"
            v-for="(plate, i) in restaurant.plates"
            :key="`_${i}`"
        >
            <h4>{{ plate.name }}</h4>
            <img :src="plate.img" :alt="plate.name" />
            <p><strong>description: </strong> {{ plate.description }}</p>
            <p><strong>ingredients: </strong> {{ plate.ingredients }}</p>
            <p>
                <strong>available: </strong>
                <span v-if="plate.visible"> yes</span>
                <span v-else>no</span>
            </p>
            <p><strong>price: </strong>{{ plate.price }}€</p>
        </article>
    </div>
</template>

<script>
import { mapState, mapActions } from "vuex";

export default {
    name: "RestMenu",
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

h1,
h2,
h4 {
    font-weight: 700;
    margin-bottom: 10px;
}

p {
    max-width: 10px 0;
}

.label {
    display: inline-block;
    background-color: #198dcc;
    color: white;
    border-radius: 5px;
    padding: 5px 10px;
    margin: 8px 15px 20px 0;
}
.plate {
    margin-bottom: 20px;
}
</style>
