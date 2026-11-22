<template>
    <section>
        <div>
            <h1 class="mb-5">
                You can't purchase plates from different restaurants in a single order...
            </h1>
            <button class="btn btn-col2 btn-block mb-3" @click="emptyCartAndStay">
                Empty the cart and start purchasing here
            </button>
            <router-link
                class="btn btn-brand btn-block"
                :to="{ name: 'restMenu', params: { slug: cartSlug } }"
                @click="backToCartRestaurant"
            >
                Go back to restaurant where I was ordering
            </router-link>
        </div>
    </section>
</template>

<script>
import { warningLogic } from "./Warning.logic.js";

export default warningLogic;
</script>

<style lang="scss" scoped src="./Warning.scss"></style>
