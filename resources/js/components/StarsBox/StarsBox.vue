<template>
    <span class="stars" role="img" :aria-label="`Rated ${average} out of 5`">
        <Icon
            v-for="n in max"
            :key="`star${n}`"
            name="star"
            :size="size"
            :filled="n <= average"
            :class="n <= average ? 'star star--on' : 'star'"
        />
    </span>
</template>

<script>
import { starsBoxLogic } from "./StarsBox.logic.js";

export default starsBoxLogic;
</script>

<style scoped src="./StarsBox.css"></style>
