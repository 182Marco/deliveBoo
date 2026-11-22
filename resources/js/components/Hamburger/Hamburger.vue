<template>
    <div @click="toggleMenuMobile">
        <div class="hamburger">
            <span class="line"></span>
            <span class="line"></span>
            <span class="line"></span>
        </div>
        <MenuMobile />
    </div>
</template>

<script>
import { hamburgerLogic } from "./Hamburger.logic.js";

export default hamburgerLogic;
</script>

<style lang="scss" scoped src="./Hamburger.scss"></style>
