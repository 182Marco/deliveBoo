<template>
    <section class="hero" aria-labelledby="hero-title">
        <div class="ui-container hero-inner">
            <div class="hero-copy">
                <p class="ui-eyebrow">Local kitchens, delivered</p>
                <h1 id="hero-title" class="ui-display hero-title">
                    Dinner from the city's <span class="ui-accent">best kitchens</span>.
                </h1>
                <p class="ui-lead hero-lead">
                    Pizza from the wood oven, sushi rolled to order, slow-cooked ragù: choose your dishes,
                    pay securely and let the kitchen do the rest.
                </p>
                <div class="hero-actions">
                    <router-link :to="{ name: 'home', hash: '#explore' }" class="ui-btn ui-btn--primary ui-btn--lg">
                        Browse restaurants
                        <Icon name="arrow-right" />
                    </router-link>
                    <router-link :to="{ name: 'home', hash: '#top-rated' }" class="ui-btn ui-btn--outline ui-btn--lg">
                        Guest favourites
                    </router-link>
                </div>
                <ul class="hero-perks">
                    <li v-for="perk in perks" :key="perk.text" class="hero-perk">
                        <span class="hero-perk-icon"><Icon :name="perk.icon" :size="18" /></span>
                        {{ perk.text }}
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
                        <span class="hero-ticket-title">Order confirmed</span>
                        <span class="hero-ticket-text">Receipt sent to your inbox</span>
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
