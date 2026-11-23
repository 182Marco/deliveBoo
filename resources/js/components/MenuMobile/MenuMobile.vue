<template>
    <div v-if="menuMobile" class="drawer-backdrop" @click.self="toggleMenuMobile" @keydown.esc="toggleMenuMobile">
        <section id="mobile-menu" class="drawer" role="dialog" aria-modal="true" :aria-label="t('drawer.label')">
            <div class="drawer-head">
                <Logo />
                <button
                    ref="close"
                    type="button"
                    class="ui-btn ui-btn--ghost ui-btn--icon"
                    :aria-label="t('drawer.close')"
                    @click="toggleMenuMobile"
                >
                    <Icon name="close" />
                </button>
            </div>

            <nav class="drawer-nav" :aria-label="t('nav.mobile')">
                <router-link
                    v-for="link in routeLinks"
                    :key="link.text"
                    :to="link.to"
                    class="drawer-link"
                    @click="toggleMenuMobile"
                >
                    <span class="drawer-link-icon"><Icon :name="link.icon" /></span>
                    {{ t(link.text) }}
                    <Icon class="drawer-link-arrow" name="chevron-right" :size="18" />
                </router-link>
                <a v-for="link in pageLinks" :key="link.text" :href="link.href" class="drawer-link">
                    <span class="drawer-link-icon"><Icon :name="link.icon" /></span>
                    {{ t(link.text) }}
                    <Icon class="drawer-link-arrow" name="chevron-right" :size="18" />
                </a>
            </nav>

            <div class="drawer-foot">
                <p class="drawer-note">{{ t("drawer.note") }}</p>
                <a class="ui-btn ui-btn--primary ui-btn--block" href="/register">{{ t("drawer.cta") }}</a>
            </div>
        </section>
    </div>
</template>

<script>
import { menuMobileLogic } from "./MenuMobile.logic.js";

export default menuMobileLogic;
</script>

<style scoped src="./MenuMobile.css"></style>
