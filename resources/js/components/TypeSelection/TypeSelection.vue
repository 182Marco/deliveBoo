<template>
    <div class="filters">
        <div class="ui-section-head">
            <div class="ui-section-intro">
                <p class="ui-eyebrow">All restaurants</p>
                <h2 id="explore-title" class="ui-title">Mix your cravings</h2>
                <p class="ui-lead">Pick one or more cuisines and see every kitchen that cooks them.</p>
            </div>
            <p class="filters-count" aria-live="polite">
                <span class="filters-count-num ui-price">{{ selectedTypesLenght }}</span>
                {{ selectedTypesLenght === 1 ? "cuisine" : "cuisines" }} selected
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
