<template>
    <div class="ui-container bag">
        <button type="button" class="bag-back" @click="$router.go(-1)">
            <Icon name="arrow-left" :size="18" />
            {{ t("cart.continue") }}
        </button>
        <div class="ui-section-intro bag-title">
            <p class="ui-eyebrow">{{ t("cart.eyebrow") }}</p>
            <h1 class="ui-title">{{ t("cart.title") }}</h1>
        </div>

        <div v-if="!cart.length" class="ui-empty">
            <img class="ui-empty-mark" src="/images/foodsy-mark.svg" alt="" />
            <p class="ui-subtitle">{{ t("cart.emptyTitle") }}</p>
            <p class="ui-muted">{{ t("cart.emptyText") }}</p>
            <router-link :to="{ name: 'home', hash: '#explore' }" class="ui-btn ui-btn--primary bag-empty-cta">
                {{ t("cart.browse") }}
            </router-link>
        </div>

        <div v-else class="bag-layout">
            <section class="bag-list" :aria-label="t('cart.list')">
                <ul class="bag-lines">
                    <li v-for="(plate, i) in plates" :key="`index_in_cart${i}`" class="bag-line">
                        <FoodImage class="bag-photo" :src="plate.img" :alt="plate.name" />
                        <div class="bag-info">
                            <h2 class="bag-name">{{ plate.name }}</h2>
                            <p class="bag-unit">{{ t("summary.each", { price: formatPrice(plate.price) }) }}</p>
                        </div>
                        <QuantityStepper
                            class="bag-stepper"
                            small
                            :count="portionsOf(plate)"
                            :item="plate.name"
                            @increment="addPlate(plate)"
                            @decrement="removePlate(plate)"
                        />
                        <span class="bag-line-total ui-price">€{{ formatPrice(portionsOf(plate) * plate.price) }}</span>
                    </li>
                </ul>
                <button type="button" class="ui-btn ui-btn--danger ui-btn--sm bag-clear" @click="emptyCart">
                    <Icon name="trash" :size="16" />
                    {{ t("cart.clear") }}
                </button>
            </section>

            <aside class="bag-total" aria-labelledby="total-title">
                <h2 id="total-title" class="ui-subtitle">{{ t("cart.totalTitle") }}</h2>
                <dl class="bag-rows">
                    <div class="bag-row">
                        <dt>{{ t("cart.dishes") }}</dt>
                        <dd class="bag-row-value">{{ cart.length }}</dd>
                    </div>
                    <div class="bag-row bag-row--total">
                        <dt>{{ t("summary.total") }}</dt>
                        <dd class="bag-row-value bag-row-amount ui-price">€{{ formatPrice(total) }}</dd>
                    </div>
                </dl>
                <router-link :to="{ name: 'payment' }" class="ui-btn ui-btn--primary ui-btn--lg ui-btn--block">
                    {{ t("cart.checkout") }}
                    <Icon name="arrow-right" />
                </router-link>
                <p class="bag-secure">
                    <Icon name="lock" :size="16" />
                    {{ t("cart.secure") }}
                </p>
            </aside>
        </div>
    </div>
</template>

<script>
import { cartLogic } from "./Cart.logic.js";

export default cartLogic;
</script>

<style scoped src="./Cart.css"></style>
