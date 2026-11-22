<template>
    <div class="cont">
        <button :class="{ clicked: isSelected }" @click="toggle">
            {{ type.name }}
        </button>
    </div>
</template>

<script>
import { typeCompLogic } from "./TypeComp.logic.js";

export default typeCompLogic;
</script>

<style lang="scss" scoped src="./TypeComp.scss"></style>
