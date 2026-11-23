<template>
    <aside class="summary" aria-labelledby="summary-title">
        <div class="summary-head">
            <h2 id="summary-title" class="summary-title">{{ t("summary.title") }}</h2>
            <span v-if="cart.length" class="ui-badge">
                {{ t("summary.items", { count: cart.length }) }}
            </span>
        </div>

        <div v-if="!lines.length" class="summary-empty">
            <span class="summary-empty-icon"><Icon name="bag" :size="26" /></span>
            <p class="summary-empty-title">{{ t("summary.emptyTitle") }}</p>
            <p class="ui-muted">{{ t("summary.emptyText") }}</p>
        </div>

        <ul v-else class="summary-lines">
            <li v-for="line in lines" :key="line.plate.id" class="summary-line">
                <p class="summary-name">{{ line.plate.name }}</p>
                <span class="summary-line-total ui-price">€{{ formatPrice(line.count * line.plate.price) }}</span>
                <span class="summary-unit">{{ t("summary.each", { price: formatPrice(line.plate.price) }) }}</span>
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
            <span class="summary-total-label">{{ t("summary.total") }}</span>
            <span class="summary-amount ui-price">€{{ amount }}</span>
        </div>

        <router-link
            v-if="ctaRoute && lines.length"
            :to="{ name: ctaRoute }"
            class="ui-btn ui-btn--primary ui-btn--lg ui-btn--block"
        >
            {{ ctaLabel || t("summary.review") }}
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
