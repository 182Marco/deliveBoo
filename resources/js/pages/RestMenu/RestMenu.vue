<template>
    <!-- render only once the menu is loaded -->
    <div v-if="restaurant.location && restaurant.menu" class="rest">
        <section class="rest-hero" aria-labelledby="rest-name">
            <FoodImage class="rest-cover" eager :src="restaurant.location.img" :alt="restaurant.location.name" />
            <div class="rest-shade"></div>
            <div class="ui-container rest-hero-inner">
                <router-link :to="{ name: 'home', hash: '#explore' }" class="ui-btn ui-btn--light ui-btn--sm rest-back">
                    <Icon name="arrow-left" :size="18" />
                    {{ t("menu.back") }}
                </router-link>
                <div class="rest-id">
                    <ul class="rest-tags" :aria-label="t('menu.cuisines')">
                        <li v-for="type in restaurant.types" :key="`tId${type}`" class="ui-badge ui-badge--light">
                            {{ type }}
                        </li>
                    </ul>
                    <h1 id="rest-name" class="ui-display rest-name">{{ restaurant.location.name }}</h1>
                    <p class="rest-rating">
                        <a v-if="rating.count" href="#reviews" class="rest-rating-link">
                            <StarsBox :average="rating.average" :count="rating.count" :size="18" />
                        </a>
                        <span v-else class="ui-badge ui-badge--light">{{ t("reviews.noRating") }}</span>
                    </p>
                </div>
            </div>
        </section>

        <div class="rest-facts">
            <ul class="ui-container rest-facts-list">
                <li class="rest-fact">
                    <span class="rest-fact-icon"><Icon name="pin" :size="18" /></span>
                    <span>
                        <span class="rest-fact-label">{{ t("menu.address") }}</span>
                        {{ restaurant.location.address }}, {{ restaurant.location.city }}
                    </span>
                </li>
                <li class="rest-fact">
                    <span class="rest-fact-icon"><Icon name="phone" :size="18" /></span>
                    <span>
                        <span class="rest-fact-label">{{ t("menu.phone") }}</span>
                        <a class="rest-phone" :href="`tel:${restaurant.location.phone}`">
                            {{ restaurant.location.phone }}
                        </a>
                    </span>
                </li>
                <li class="rest-fact">
                    <span class="rest-fact-icon"><Icon name="lock" :size="18" /></span>
                    <span>
                        <span class="rest-fact-label">{{ t("menu.payment") }}</span>
                        {{ t("menu.paymentValue") }}
                    </span>
                </li>
            </ul>
        </div>

        <div class="ui-container rest-layout">
            <section class="rest-menu" aria-labelledby="menu-title">
                <div class="rest-menu-head">
                    <div class="ui-section-intro">
                        <p class="ui-eyebrow">{{ t("menu.eyebrow") }}</p>
                        <h2 id="menu-title" class="ui-title">{{ t("menu.title") }}</h2>
                    </div>
                    <span v-if="menuPagination.last > 1" class="rest-page ui-muted">
                        {{ t("menu.page", { current: menuPagination.current, last: menuPagination.last }) }}
                    </span>
                </div>
                <ul class="rest-dishes">
                    <li v-for="(plate, i) in restaurant.menu.data" :key="`_${i}`">
                        <Plate :plate="plate" />
                    </li>
                </ul>
                <PaginateEl :paginate="menuPagination" :get-menu="true" :slug="restaurant.location.slug" />
            </section>

            <div class="rest-aside">
                <OrderSummary cta-route="cart" />
            </div>
        </div>

        <RestReviews :slug="restaurant.location.slug" :restaurant="restaurant.location.name" />

        <router-link v-if="cart.length" :to="{ name: 'cart' }" class="rest-bagbar">
            <span class="rest-bagbar-count">{{ cart.length }}</span>
            {{ t("menu.viewBag") }}
            <span class="rest-bagbar-amount ui-price">€{{ amount }}</span>
        </router-link>

        <Warning v-if="warn" />
    </div>

    <div v-else class="rest-loading" aria-busy="true" :aria-label="t('menu.loading')">
        <div class="rest-skeleton rest-skeleton--cover"></div>
        <div class="ui-container rest-loading-lines">
            <div class="rest-skeleton rest-skeleton--line"></div>
            <div class="rest-skeleton rest-skeleton--short"></div>
        </div>
    </div>
</template>

<script>
import { restMenuLogic } from "./RestMenu.logic.js";

export default restMenuLogic;
</script>

<style scoped src="./RestMenu.css"></style>
