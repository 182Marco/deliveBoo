<template>
    <span class="stars">
        <span class="ui-stars" role="img" :aria-label="t('stars.rated', { average: label })">
            <template v-for="(state, i) in states" :key="`star${i}`">
                <span v-if="state === 'half'" class="ui-star-half">
                    <Icon name="star" :size="size" class="ui-star" />
                    <span class="ui-star-half-fill">
                        <Icon name="star" :size="size" filled class="ui-star ui-star--on" />
                    </span>
                </span>
                <Icon
                    v-else
                    name="star"
                    :size="size"
                    :filled="state === 'full'"
                    :class="state === 'full' ? 'ui-star ui-star--on' : 'ui-star'"
                />
            </template>
        </span>
        <span v-if="count !== null" class="stars-meta">
            <span class="stars-number">{{ label }}</span>
            · {{ t("reviews.count", { count }) }}
        </span>
    </span>
</template>

<script>
import { starsBoxLogic } from "./StarsBox.logic.js";

export default starsBoxLogic;
</script>

<style scoped src="./StarsBox.css"></style>
