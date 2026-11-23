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
                    <h2 id="partner-title" class="ui-title">{{ t("partner.title") }}</h2>
                    <p class="partner-text">
                        {{ t("partner.text") }}
                    </p>
                </div>
                <a class="ui-btn ui-btn--dark ui-btn--lg partner-cta" href="/register">
                    {{ t("partner.cta") }}
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
