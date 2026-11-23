<template>
    <div class="lost">
        <section class="ui-container ui-container--narrow lost-head" aria-labelledby="lost-title">
            <p class="lost-code">404</p>
            <h1 id="lost-title" class="ui-title">{{ t("notFound.title") }}</h1>
            <p class="ui-lead">{{ t("notFound.lead") }}</p>
            <router-link :to="{ name: 'home' }" class="ui-btn ui-btn--primary ui-btn--lg">
                {{ t("notFound.home") }}
                <Icon name="arrow-right" />
            </router-link>
        </section>
        <Content :eyebrow="t('notFound.eyebrow')" :title="t('notFound.explore')" />
    </div>
</template>

<script>
import { notFoundLogic } from "./NotFound.logic.js";

export default notFoundLogic;
</script>

<style scoped src="./NotFound.css"></style>
