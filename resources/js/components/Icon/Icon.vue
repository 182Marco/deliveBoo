<template>
    <svg
        class="ui-icon"
        viewBox="0 0 24 24"
        :width="size"
        :height="size"
        :fill="filled ? 'currentColor' : 'none'"
        stroke="currentColor"
        stroke-width="1.75"
        stroke-linecap="round"
        stroke-linejoin="round"
        :role="label ? 'img' : null"
        :aria-label="label || null"
        :aria-hidden="label ? null : 'true'"
        focusable="false"
    >
        <path :d="d" />
    </svg>
</template>

<script>
import { iconLogic } from "./Icon.logic.js";

export default iconLogic;
</script>

