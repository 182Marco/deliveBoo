<template>
    <span class="stars" role="img" :aria-label="t('stars.rated', { average })">
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
