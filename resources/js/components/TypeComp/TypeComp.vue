<template>
    <button
        type="button"
        class="ui-chip filters-chip"
        :class="{ 'ui-chip--active': isSelected }"
        :aria-pressed="isSelected ? 'true' : 'false'"
        @click="toggle"
    >
        <Icon v-if="isSelected" name="check" :size="16" />
        {{ type.name }}
    </button>
</template>

<script>
import { typeCompLogic } from "./TypeComp.logic.js";

export default typeCompLogic;
</script>

<style scoped src="./TypeComp.css"></style>
