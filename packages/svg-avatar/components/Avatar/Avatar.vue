<template>
    <svg
        :class="['sa-avatar', { 'sa-avatar--square': shape === 'square' }]"
        xmlns="http://www.w3.org/2000/svg"
        :viewBox="viewBox"
        :width="size"
        :height="size"
        :role="label ? 'img' : null"
        :aria-label="label || null"
        :aria-hidden="label ? null : 'true'"
        focusable="false"
    >
        <title v-if="label">{{ label }}</title>
        <path v-for="(attrs, index) in paths" :key="index" v-bind="attrs" />
    </svg>
</template>

<script>
import { avatarLogic } from "./Avatar.logic.js";

export default avatarLogic;
</script>

<style scoped src="./Avatar.css"></style>
