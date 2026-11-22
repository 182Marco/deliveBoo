<template>
    <div class="home">
        <Jumbotron />
        <Content />
        <TopRated />
        <section id="explore" class="explore" aria-labelledby="explore-title">
            <div class="ui-container">
                <TypeSelection />
                <RestByTypes />
            </div>
        </section>
        <section class="partner" aria-labelledby="partner-title">
            <div class="ui-container partner-inner">
                <div class="partner-copy">
                    <h2 id="partner-title" class="ui-title">Run a kitchen? Cook for Foodsy.</h2>
                    <p class="partner-text">
                        Publish your menu, receive paid orders by email and follow your sales month by month.
                    </p>
                </div>
                <a class="ui-btn ui-btn--dark ui-btn--lg partner-cta" href="/register">
                    Add your restaurant
                    <Icon name="arrow-right" />
                </a>
            </div>
        </section>
    </div>
</template>

<script>
import { homeLogic } from "./Home.logic.js";

export default homeLogic;
</script>

<style scoped src="./Home.css"></style>
