<template>
    <div class="stars-box" :class="{ top_list: topList }">
        <i v-for="n in average" :key="`fasStar${n}`" class="fas fa-star"></i>
        <i v-for="n in emptyStars" :key="`farStar${n}`" class="far fa-star"></i>
    </div>
</template>

<script>
import { starsBoxLogic } from "./StarsBox.logic.js";

export default starsBoxLogic;
</script>

<style lang="scss" scoped src="./StarsBox.scss"></style>
