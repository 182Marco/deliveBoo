<template>
    <section id="cuisines" class="cuisines" aria-labelledby="cuisines-title">
        <div class="ui-container">
            <div class="ui-section-head">
                <div class="ui-section-intro">
                    <p class="ui-eyebrow">{{ eyebrowText }}</p>
                    <h2 id="cuisines-title" class="ui-title">{{ titleText }}</h2>
                </div>
            </div>
            <ul class="ui-rail cuisines-rail">
                <li v-for="type in alltypes" :key="`type_index${type.id}`" class="ui-rail-item">
                    <button
                        type="button"
                        class="cuisine"
                        :aria-current="isSelected(type.id) ? 'true' : null"
                        @click="selectSingleType(type.id)"
                    >
                        <FoodImage
                            class="cuisine-photo"
                            :class="{ 'cuisine-photo--active': isSelected(type.id) }"
                            :src="type.img"
                        />
                        <span class="cuisine-name">{{ type.name }}</span>
                    </button>
                </li>
            </ul>
        </div>
    </section>
</template>

<script>
import { contentLogic } from "./Content.logic.js";

export default contentLogic;
</script>

<style scoped src="./Content.css"></style>
