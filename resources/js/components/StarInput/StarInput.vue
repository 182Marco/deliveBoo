<template>
    <div class="star-input">
        <span class="star-input-level" aria-hidden="true">{{ level }}</span>
        <div
            class="star-input-group"
            role="radiogroup"
            :aria-labelledby="labelledby"
            :aria-describedby="describedby"
            @mouseleave="preview = 0"
        >
            <button
                v-for="star in max"
                :key="`star_${star}`"
                ref="stars"
                type="button"
                role="radio"
                :aria-checked="star === modelValue ? 'true' : 'false'"
                :aria-label="label(star)"
                :tabindex="tabIndex(star)"
                :class="star <= shown ? 'star-input-btn star-input-btn--on' : 'star-input-btn'"
                @click="choose(star)"
                @mouseenter="preview = star"
                @keydown="onKey"
            >
                <Icon name="star" :size="size" :filled="star <= shown" />
            </button>
        </div>
    </div>
</template>

<script>
import { starInputLogic } from "./StarInput.logic.js";

export default starInputLogic;
</script>

<style scoped src="./StarInput.css"></style>
