<template>
    <header class="header">
        <div class="ui-container header-inner">
            <router-link :to="{ name: 'home' }" class="header-brand" :aria-label="t('nav.home')">
                <Logo />
            </router-link>

            <nav class="header-nav" :aria-label="t('nav.main')">
                <router-link
                    v-for="link in links"
                    :key="link.text"
                    :to="link.to"
                    class="header-link"
                >
                    {{ t(link.text) }}
                </router-link>
            </nav>

            <div class="header-actions">
                <RestaurantSearch />
                <LocaleSwitch />
                <a href="/login" class="header-partner">
                    <Icon name="store" :size="18" />
                    {{ t("header.partner") }}
                </a>
                <router-link :to="{ name: 'cart' }" class="header-bag" :aria-label="bagLabel">
                    <Icon name="bag" />
                    <span class="header-bag-text">{{ t("header.bag") }}</span>
                    <span v-if="cartLenght" class="header-bag-count">{{ cartLenght }}</span>
                    <span v-if="cartLenght" class="header-bag-amount ui-price">€{{ cartAmount }}</span>
                </router-link>
                <Hamburger />
            </div>
        </div>
    </header>
</template>

<script>
import { headerLogic } from "./Header.logic.js";

export default headerLogic;
</script>

<style scoped src="./Header.css"></style>
