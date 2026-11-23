<template>
    <!-- same markup as the Blade <x-flag>: the styles live once, in resources/css/ui.css -->
    <span class="ui-flag">
        <svg
            class="ui-flag-svg"
            viewBox="0 0 30 20"
            :width="size"
            :height="height"
            aria-hidden="true"
            focusable="false"
        >
            <path
                v-for="(shape, i) in shapes"
                :key="`flag${i}`"
                :d="shape.d"
                :fill="shape.fill ?? 'none'"
                :stroke="shape.stroke ?? null"
                :stroke-width="shape.width ?? null"
            />
        </svg>
    </span>
</template>

<script>
import { flagLogic } from "./Flag.logic.js";

export default flagLogic;
</script>
