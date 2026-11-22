<template>
    <div class="stepper" :class="{ 'stepper--small': small }">
        <button
            type="button"
            class="stepper-btn"
            :class="{ 'stepper-btn--small': small }"
            :aria-label="`Remove one ${item}`"
            @click="$emit('decrement')"
        >
            <Icon :name="count > 1 ? 'minus' : 'trash'" :size="small ? 16 : 18" />
        </button>
        <span class="stepper-count" aria-live="polite">{{ count }}</span>
        <button
            type="button"
            class="stepper-btn"
            :class="{ 'stepper-btn--small': small }"
            :aria-label="`Add one ${item}`"
            @click="$emit('increment')"
        >
            <Icon name="plus" :size="small ? 16 : 18" />
        </button>
    </div>
</template>

<script>
import { quantityStepperLogic } from "./QuantityStepper.logic.js";

export default quantityStepperLogic;
</script>

<style scoped src="./QuantityStepper.css"></style>
