<template>
    <!-- render only once the chosen type is known -->
    <div v-if="currentType" class="cuisine-page">
        <section class="cuisine-hero" aria-labelledby="cuisine-title">
            <FoodImage class="cuisine-cover" eager :src="currentType.img" />
            <div class="cuisine-shade"></div>
            <div class="ui-container cuisine-hero-inner">
                <router-link :to="{ name: 'home', hash: '#explore' }" class="ui-btn ui-btn--light ui-btn--sm">
                    <Icon name="arrow-left" :size="18" />
                    Combine more cuisines
                </router-link>
                <div class="cuisine-id">
                    <p class="ui-eyebrow ui-eyebrow--inverse">Cuisine</p>
                    <h1 id="cuisine-title" class="ui-display cuisine-name">{{ currentType.name }}{{ titleSuffix }}</h1>
                </div>
            </div>
        </section>
        <div class="ui-container cuisine-results">
            <RestByTypes />
        </div>
        <Content class="cuisine-more" eyebrow="Keep exploring" title="Other cuisines you might love" />
    </div>
</template>

<script>
import { singleTypeRestLogic } from "./SingleTypeRest.logic.js";

export default singleTypeRestLogic;
</script>

<style scoped src="./SingleTypeRest.css"></style>
