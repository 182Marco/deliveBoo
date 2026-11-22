<template>
    <article class="dish">
        <div class="dish-media">
            <FoodImage class="dish-photo" :src="plate.img" :alt="plate.name" />
            <span v-if="!orderable" class="dish-flag">{{ unavailableLabel }}</span>
            <div class="dish-action">
                <QuantityStepper
                    v-if="portionsInCart"
                    :count="portionsInCart"
                    :item="plate.name"
                    @increment="addToCart"
                    @decrement="removeOne"
                />
                <button
                    v-else-if="orderable"
                    type="button"
                    class="dish-add"
                    :aria-label="`Add ${plate.name} to your bag`"
                    @click="addToCart"
                >
                    <Icon name="plus" :size="22" />
                </button>
            </div>
        </div>
        <div class="dish-body">
            <div class="dish-head">
                <h3 class="dish-name">{{ plate.name }}</h3>
                <span class="dish-price ui-price">€{{ price }}</span>
            </div>
            <p class="dish-description">{{ plate.description }}</p>
            <p class="dish-ingredients">
                <span class="ui-sr-only">Ingredients:</span>
                {{ plate.ingredients }}
            </p>
        </div>
    </article>
</template>

<script>
import { plateLogic } from "./Plate.logic.js";

export default plateLogic;
</script>

<style scoped src="./Plate.css"></style>
