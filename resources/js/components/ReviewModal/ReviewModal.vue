<template>
    <div class="ui-modal-backdrop review-backdrop" @click.self="close" @keydown.esc="close" @keydown.tab="trap">
        <section
            ref="dialog"
            class="ui-modal review-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="review-title"
            aria-describedby="review-lead"
        >
            <p class="ui-eyebrow">{{ t("reviews.modalEyebrow", { id: orderId }) }}</p>
            <h2 id="review-title" class="ui-subtitle review-title">{{ t("reviews.modalTitle", { restaurant }) }}</h2>
            <p id="review-lead" class="review-lead">{{ t("reviews.modalLead") }}</p>

            <form class="review-form" novalidate @submit.prevent="submit">
                <div class="review-field">
                    <span id="review-rating" class="ui-label">{{ t("reviews.rating") }}:</span>
                    <StarInput ref="stars" v-model="rating" labelledby="review-rating" describedby="review-rating-hint" />
                    <p id="review-rating-hint" class="ui-hint">{{ t("reviews.ratingHint") }}</p>
                </div>

                <div class="ui-field">
                    <label class="ui-label" for="review-comment">
                        {{ t("reviews.comment") }}:
                        <span class="review-optional">({{ t("reviews.optional") }})</span>
                    </label>
                    <textarea
                        id="review-comment"
                        v-model="comment"
                        class="ui-input ui-textarea review-textarea"
                        rows="5"
                        :maxlength="max"
                        :placeholder="t('reviews.placeholder')"
                        aria-describedby="review-counter"
                    ></textarea>
                    <p id="review-counter" class="ui-hint review-counter">
                        {{ t("reviews.counter", { count: comment.length, max }) }}
                    </p>
                </div>

                <p v-if="error" class="ui-notice ui-notice--error" role="alert">{{ error }}</p>

                <div class="ui-modal-actions">
                    <button type="submit" class="ui-btn ui-btn--primary ui-btn--block" :disabled="saving" :aria-busy="saving">
                        {{ saving ? t("reviews.saving") : editing ? t("reviews.save") : t("reviews.publish") }}
                    </button>
                    <button type="button" class="ui-btn ui-btn--outline ui-btn--block" :disabled="saving" @click="close">
                        {{ t("reviews.cancel") }}
                    </button>
                </div>
            </form>
        </section>
    </div>
</template>

<script>
import { reviewModalLogic } from "./ReviewModal.logic.js";

export default reviewModalLogic;
</script>

<style scoped src="./ReviewModal.css"></style>
