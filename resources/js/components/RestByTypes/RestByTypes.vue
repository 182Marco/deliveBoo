<template>
    <div class="results">
        <div v-if="!selectedTypesLenght" class="ui-empty">
            <img class="ui-empty-mark" src="/images/foodsy-mark.svg" alt="" />
            <p class="ui-subtitle">{{ t("results.chooseTitle") }}</p>
            <p class="ui-muted">{{ t("results.chooseText") }}</p>
        </div>
        <div v-else-if="!restByTypes.length" class="ui-empty">
            <img class="ui-empty-mark" src="/images/foodsy-mark.svg" alt="" />
            <p class="ui-subtitle">{{ t("results.emptyTitle") }}</p>
            <p class="ui-muted">{{ t("results.emptyText") }}</p>
        </div>
        <template v-else>
            <ul class="results-grid">
                <li v-for="(rest, i) in restByTypes" :key="`restaurant_index${i}`">
                    <router-link :to="menuRoute(rest)" class="rest-card">
                        <FoodImage class="rest-photo" :src="rest.img" :alt="rest.name" />
                        <div class="rest-body">
                            <h3 class="rest-name">{{ rest.name }}</h3>
                            <p class="rest-meta">
                                <Icon name="pin" :size="16" />
                                {{ rest.address }}, {{ rest.city }}
                            </p>
                            <span class="rest-cta">
                                {{ t("results.viewMenu") }}
                                <Icon name="arrow-right" :size="16" />
                            </span>
                        </div>
                    </router-link>
                </li>
            </ul>
            <PaginateEl :paginate="restPagination" />
        </template>
    </div>
</template>

<script>
import { restByTypesLogic } from "./RestByTypes.logic.js";

export default restByTypesLogic;
</script>

<style scoped src="./RestByTypes.css"></style>
