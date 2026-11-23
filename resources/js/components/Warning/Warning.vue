<template>
    <div class="ui-modal-backdrop" @click.self="changeWarn" @keydown.esc="changeWarn">
        <section
            class="ui-modal warning"
            role="alertdialog"
            aria-modal="true"
            aria-labelledby="warning-title"
            aria-describedby="warning-text"
        >
            <span class="warning-icon"><Icon name="bag" :size="26" /></span>
            <h2 id="warning-title" class="ui-subtitle">{{ t("warning.title") }}</h2>
            <p id="warning-text" class="warning-text">
                {{ t("warning.text") }}
            </p>
            <div class="ui-modal-actions">
                <button ref="primary" type="button" class="ui-btn ui-btn--primary ui-btn--block" @click="emptyCartAndStay">
                    {{ t("warning.newOrder") }}
                </button>
                <router-link
                    class="ui-btn ui-btn--outline ui-btn--block"
                    :to="{ name: 'restMenu', params: { slug: cartSlug } }"
                    @click="backToCartRestaurant"
                >
                    {{ t("warning.back") }}
                </router-link>
            </div>
        </section>
    </div>
</template>

<script>
import { warningLogic } from "./Warning.logic.js";

export default warningLogic;
</script>

<style scoped src="./Warning.css"></style>
