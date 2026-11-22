<template>
    <div class="food-image">
        <img
            v-if="hasPhoto"
            class="food-image-photo"
            :src="src"
            :alt="alt"
            :loading="eager ? 'eager' : 'lazy'"
            decoding="async"
            @error="onError"
        />
        <div v-else class="food-image-empty" role="img" :aria-label="alt">
            <img class="food-image-mark" :src="placeholder" alt="" />
        </div>
    </div>
</template>

<script>
import { foodImageLogic } from "./FoodImage.logic.js";

export default foodImageLogic;
</script>

<style scoped src="./FoodImage.css"></style>
