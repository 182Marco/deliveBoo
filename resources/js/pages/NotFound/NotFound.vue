<template>
    <div class="lost">
        <section class="ui-container ui-container--narrow lost-head" aria-labelledby="lost-title">
            <p class="lost-code">404</p>
            <h1 id="lost-title" class="ui-title">This page went off the menu.</h1>
            <p class="ui-lead">The address you typed does not match anything we serve. Let us take you somewhere tasty.</p>
            <router-link :to="{ name: 'home' }" class="ui-btn ui-btn--primary ui-btn--lg">
                Back to home
                <Icon name="arrow-right" />
            </router-link>
        </section>
        <Content eyebrow="Or pick a craving" title="Explore a cuisine" />
    </div>
</template>

<script>
import { notFoundLogic } from "./NotFound.logic.js";

export default notFoundLogic;
</script>

<style scoped src="./NotFound.css"></style>
