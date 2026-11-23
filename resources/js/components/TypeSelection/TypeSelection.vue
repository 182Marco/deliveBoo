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
        <!-- phones: the chips run in rows inside one sideways scroller (faded edge = there is more);
             from 900px the rows dissolve and the chips wrap as a single list -->
        <div class="filters-scroll">
            <div class="filters-list" role="group" aria-labelledby="explore-title">
                <ul v-for="(row, i) in rows" :key="`row${i}`" class="filters-row">
                    <li v-for="type in row" :key="`type_id${type.id}`">
                        <TypeComp :type="type" />
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
import { typeSelectionLogic } from "./TypeSelection.logic.js";

export default typeSelectionLogic;
</script>

<style scoped src="./TypeSelection.css"></style>
