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
            <h2 id="warning-title" class="ui-subtitle">One restaurant per order</h2>
            <p id="warning-text" class="warning-text">
                Your bag already holds dishes from another restaurant. Start a new order here, or go back and
                finish the one you began.
            </p>
            <div class="ui-modal-actions">
                <button ref="primary" type="button" class="ui-btn ui-btn--primary ui-btn--block" @click="emptyCartAndStay">
                    Start a new order here
                </button>
                <router-link
                    class="ui-btn ui-btn--outline ui-btn--block"
                    :to="{ name: 'restMenu', params: { slug: cartSlug } }"
                    @click="backToCartRestaurant"
                >
                    Back to my order
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
