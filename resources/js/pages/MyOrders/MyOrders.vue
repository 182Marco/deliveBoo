<template>
    <section class="ui-container ui-container--narrow orders" aria-labelledby="orders-title">
        <div class="ui-section-intro orders-title">
            <p class="ui-eyebrow">{{ t("orders.eyebrow") }}</p>
            <h1 id="orders-title" class="ui-title">{{ t("orders.title") }}</h1>
            <p class="ui-lead">{{ t("orders.lead") }}</p>
        </div>

        <p v-if="status === 'loading'" class="ui-muted orders-loading" role="status">{{ t("orders.loading") }}</p>

        <div v-else-if="status === 'guest'" class="ui-empty">
            <img class="ui-empty-mark" src="/images/foodsy-mark.svg" alt="" />
            <h2 class="ui-subtitle">{{ t("orders.guestTitle") }}</h2>
            <p class="ui-muted">{{ t("orders.guestText") }}</p>
            <a href="/login" class="ui-btn ui-btn--primary orders-cta">{{ t("header.login") }}</a>
        </div>

        <div v-else-if="status === 'error'" class="ui-notice ui-notice--error orders-error" role="alert">
            <span class="orders-error-text">{{ t("orders.error") }}</span>
            <button type="button" class="ui-btn ui-btn--outline ui-btn--sm" @click="load(pagination.current)">
                {{ t("orders.retry") }}
            </button>
        </div>

        <div v-else-if="!rows.length" class="ui-empty">
            <img class="ui-empty-mark" src="/images/foodsy-mark.svg" alt="" />
            <h2 class="ui-subtitle">{{ t("orders.emptyTitle") }}</h2>
            <p class="ui-muted">{{ t("orders.emptyText") }}</p>
            <router-link :to="{ name: 'home', hash: '#explore' }" class="ui-btn ui-btn--primary orders-cta">
                {{ t("orders.browse") }}
            </router-link>
        </div>

        <template v-else>
            <ol class="orders-list">
                <li v-for="order in rows" :key="order.id" class="ui-card orders-card">
                    <div class="orders-head">
                        <FoodImage class="orders-photo" :src="order.restaurant?.img ?? ''" alt="" />
                        <div class="orders-who">
                            <h2 class="orders-restaurant">
                                <router-link
                                    v-if="order.restaurant"
                                    class="orders-link"
                                    :to="{ name: 'restMenu', params: { slug: order.restaurant.slug } }"
                                >
                                    {{ order.restaurant.name }}
                                </router-link>
                                <span v-else class="orders-gone">{{ t("orders.restaurantGone") }}</span>
                            </h2>
                            <p class="orders-meta">
                                {{ t("orders.order", { id: order.id }) }} · {{ t("orders.placed", { date: order.placed }) }}
                            </p>
                        </div>
                        <span class="ui-badge orders-badge" :class="order.tone">{{ t(`orders.state.${order.state}`) }}</span>
                    </div>

                    <ul class="orders-lines">
                        <li v-for="(line, i) in order.lines" :key="`line_${order.id}_${i}`" class="orders-line">
                            <span class="orders-qty">{{ line.quantity }}×</span>
                            <span class="orders-dish">{{ line.name }}</span>
                            <span class="ui-price orders-line-price">€{{ formatPrice(line.quantity * line.unit_price) }}</span>
                        </li>
                    </ul>

                    <div class="orders-foot">
                        <p class="orders-meta">
                            {{ t("orders.items", { count: order.items }) }}
                            <template v-if="order.delivered"> · {{ t("orders.delivered", { date: order.delivered }) }}</template>
                            <template v-if="order.address"> · {{ t("orders.address") }}: {{ order.address }}</template>
                        </p>
                        <p class="orders-total">
                            {{ t("orders.total") }}
                            <strong class="ui-price orders-amount">€{{ formatPrice(order.price) }}</strong>
                        </p>
                        <router-link
                            v-if="order.restaurant"
                            class="ui-btn ui-btn--outline ui-btn--sm orders-again"
                            :to="{ name: 'restMenu', params: { slug: order.restaurant.slug } }"
                        >
                            {{ t("orders.again") }}
                            <Icon name="arrow-right" :size="16" />
                        </router-link>
                    </div>
                </li>
            </ol>
            <PaginateEl local :paginate="pagination" @go="goTo" />
        </template>
    </section>
</template>

<script>
import { myOrdersLogic } from "./MyOrders.logic.js";

export default myOrdersLogic;
</script>

<style scoped src="./MyOrders.css"></style>
