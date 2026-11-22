<template>
    <aside class="summary" aria-labelledby="summary-title">
        <div class="summary-head">
            <h2 id="summary-title" class="summary-title">Your order</h2>
            <span v-if="cart.length" class="ui-badge">
                {{ cart.length }} {{ cart.length === 1 ? "item" : "items" }}
            </span>
        </div>

        <div v-if="!lines.length" class="summary-empty">
            <span class="summary-empty-icon"><Icon name="bag" :size="26" /></span>
            <p class="summary-empty-title">Your bag is empty</p>
            <p class="ui-muted">Add a dish from the menu to start your order.</p>
        </div>

        <ul v-else class="summary-lines">
            <li v-for="line in lines" :key="line.plate.id" class="summary-line">
                <p class="summary-name">{{ line.plate.name }}</p>
                <span class="summary-line-total ui-price">€{{ formatPrice(line.count * line.plate.price) }}</span>
                <span class="summary-unit">€{{ formatPrice(line.plate.price) }} each</span>
                <span v-if="readonly" class="summary-qty">× {{ line.count }}</span>
                <QuantityStepper
                    v-else
                    class="summary-stepper"
                    small
                    :count="line.count"
                    :item="line.plate.name"
                    @increment="addPlate(line.plate)"
                    @decrement="removePlate(line.plate)"
                />
            </li>
        </ul>

        <div class="summary-total">
            <span class="summary-total-label">Total</span>
            <span class="summary-amount ui-price">€{{ amount }}</span>
        </div>

        <router-link
            v-if="ctaRoute && lines.length"
            :to="{ name: ctaRoute }"
            class="ui-btn ui-btn--primary ui-btn--lg ui-btn--block"
        >
            {{ ctaLabel }}
            <Icon name="arrow-right" />
        </router-link>
        <slot />
    </aside>
</template>

<script>
import { orderSummaryLogic } from "./OrderSummary.logic.js";

export default orderSummaryLogic;
</script>

<style scoped src="./OrderSummary.css"></style>
