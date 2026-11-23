<template>
    <section class="hero" aria-labelledby="hero-title">
        <div class="ui-container hero-inner">
            <div class="hero-copy">
                <p class="ui-eyebrow">{{ t("hero.eyebrow") }}</p>
                <h1 id="hero-title" class="ui-display hero-title">
                    {{ t("hero.titleStart") }} <span class="ui-accent">{{ t("hero.titleAccent") }}</span>{{ t("hero.titleEnd") }}
                </h1>
                <p class="ui-lead hero-lead">
                    {{ t("hero.lead") }}
                </p>
                <div class="hero-actions">
                    <router-link :to="{ name: 'home', hash: '#explore' }" class="ui-btn ui-btn--primary ui-btn--lg">
                        {{ t("hero.browse") }}
                        <Icon name="arrow-right" />
                    </router-link>
                    <router-link :to="{ name: 'home', hash: '#top-rated' }" class="ui-btn ui-btn--outline ui-btn--lg">
                        {{ t("nav.favourites") }}
                    </router-link>
                </div>
                <ul class="hero-perks">
                    <li v-for="perk in perks" :key="perk.text" class="hero-perk">
                        <span class="hero-perk-icon"><Icon :name="perk.icon" :size="18" /></span>
                        {{ t(perk.text) }}
                    </li>
                </ul>
            </div>

            <div class="hero-mosaic" aria-hidden="true">
                <FoodImage class="hero-photo hero-photo--main" eager :src="photos.main" />
                <FoodImage class="hero-photo" eager :src="photos.top" />
                <FoodImage class="hero-photo" eager :src="photos.bottom" />
                <p class="hero-ticket">
                    <span class="hero-ticket-icon"><Icon name="receipt" :size="18" /></span>
                    <span>
                        <span class="hero-ticket-title">{{ t("hero.ticketTitle") }}</span>
                        <span class="hero-ticket-text">{{ t("hero.ticketText") }}</span>
                    </span>
                </p>
            </div>
        </div>
    </section>
</template>

<script>
import { jumbotronLogic } from "./Jumbotron.logic.js";

export default jumbotronLogic;
</script>

<style scoped src="./Jumbotron.css"></style>
