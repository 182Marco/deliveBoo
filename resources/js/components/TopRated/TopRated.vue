<template>
    <section id="top-rated" class="favs" aria-labelledby="top-title">
        <div class="ui-container">
            <div class="ui-section-head">
                <div class="ui-section-intro">
                    <p class="ui-eyebrow ui-eyebrow--inverse">{{ t("top.eyebrow") }}</p>
                    <h2 id="top-title" class="ui-title ui-inverse">{{ t("nav.favourites") }}</h2>
                </div>
                <div class="favs-controls">
                    <button
                        type="button"
                        class="ui-btn ui-btn--icon favs-arrow"
                        :aria-label="t('top.previous')"
                        @click="scrollRail(-1)"
                    >
                        <Icon name="arrow-left" />
                    </button>
                    <button
                        type="button"
                        class="ui-btn ui-btn--icon favs-arrow"
                        :aria-label="t('top.next')"
                        @click="scrollRail(1)"
                    >
                        <Icon name="arrow-right" />
                    </button>
                </div>
            </div>
            <ol ref="rail" class="ui-rail favs-rail">
                <li v-for="(rest, i) in topRest" :key="`rest__id${rest.id}`" class="ui-rail-item">
                    <router-link :to="menuRoute(rest)" class="fav">
                        <div class="fav-media">
                            <FoodImage class="fav-photo" :src="rest.img" :alt="rest.name" />
                            <span class="fav-rank" aria-hidden="true">{{ rank(i) }}</span>
                        </div>
                        <h3 class="fav-name">
                            <span class="ui-sr-only">{{ t("top.number", { n: i + 1 }) }}</span>
                            {{ rest.name }}
                        </h3>
                        <p class="fav-meta">
                            <span class="fav-city">
                                <Icon name="pin" :size="16" />
                                {{ rest.city }}
                            </span>
                            <StarsBox :average="rest.average" />
                        </p>
                    </router-link>
                </li>
            </ol>
        </div>
    </section>
</template>

<script>
import { topRatedLogic } from "./TopRated.logic.js";

export default topRatedLogic;
</script>

<style scoped src="./TopRated.css"></style>
