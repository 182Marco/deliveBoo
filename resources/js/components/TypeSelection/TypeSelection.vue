<template>
    <div class="filters">
        <div class="ui-section-head">
            <div class="ui-section-intro">
                <p class="ui-eyebrow">{{ t("explore.eyebrow") }}</p>
                <h2 id="explore-title" class="ui-title">{{ t("explore.title") }}</h2>
                <p class="ui-lead">{{ t("explore.lead") }}</p>
            </div>
            <p class="filters-count" aria-live="polite">
                <span class="filters-count-num ui-price">{{ selectedTypesLenght }}</span>
                {{ t("explore.selected", { count: selectedTypesLenght }) }}
            </p>
        </div>
        <ul class="filters-list" aria-labelledby="explore-title">
            <li v-for="type in alltypes" :key="`type_id${type.id}`">
                <TypeComp :type="type" />
            </li>
        </ul>
    </div>
</template>

<script>
import { typeSelectionLogic } from "./TypeSelection.logic.js";

export default typeSelectionLogic;
</script>

<style scoped src="./TypeSelection.css"></style>
