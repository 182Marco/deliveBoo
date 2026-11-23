<template>
    <div class="hamburger">
        <button
            type="button"
            class="hamburger-btn"
            aria-controls="mobile-menu"
            :aria-expanded="menuMobile ? 'true' : 'false'"
            :aria-label="t('drawer.open')"
            @click="toggleMenuMobile"
        >
            <Icon name="menu" :size="22" />
        </button>
        <MenuMobile />
    </div>
</template>

<script>
import { hamburgerLogic } from "./Hamburger.logic.js";

export default hamburgerLogic;
</script>

<style scoped src="./Hamburger.css"></style>
