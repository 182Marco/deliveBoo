<template>
    <section id="reviews" class="reviews" aria-labelledby="reviews-title">
        <div class="ui-container">
            <div class="ui-section-intro reviews-intro">
                <p class="ui-eyebrow">{{ t("reviews.eyebrow") }}</p>
                <h2 id="reviews-title" class="ui-title">{{ t("reviews.title") }}</h2>
                <p class="ui-muted">{{ t("reviews.listNote") }}</p>
            </div>

            <div v-if="status === 'loading'" class="reviews-layout" role="status">
                <span class="ui-sr-only">{{ t("reviews.loading") }}</span>
                <div class="reviews-summary reviews-bone" aria-hidden="true"></div>
                <div class="reviews-list" aria-hidden="true">
                    <div v-for="n in 2" :key="`bone_${n}`" class="ui-card reviews-card reviews-bone"></div>
                </div>
            </div>

            <div v-else-if="status === 'error'" class="ui-notice ui-notice--error reviews-error" role="alert">
                <span class="reviews-error-text">{{ t("reviews.loadError") }}</span>
                <button type="button" class="ui-btn ui-btn--outline ui-btn--sm" @click="load(pagination.current)">
                    {{ t("reviews.retry") }}
                </button>
            </div>

            <div v-else-if="!summary.count && !reviews.length" class="ui-empty reviews-empty">
                <img class="ui-empty-mark" src="/images/foodsy-mark.svg" alt="" />
                <h3 class="ui-subtitle">{{ t("reviews.emptyTitle") }}</h3>
                <p class="ui-muted">{{ t("reviews.emptyText") }}</p>
            </div>

            <div v-else class="reviews-layout">
                <aside class="reviews-summary" :aria-label="t('reviews.rating')">
                    <p class="reviews-average">{{ average }}</p>
                    <StarsBox :average="summary.average ?? 0" :size="20" />
                    <p class="reviews-based">{{ t("reviews.basedOn", { count: summary.count }) }}</p>
                    <ul class="reviews-dist">
                        <li v-for="row in rows" :key="`dist_${row.stars}`" class="reviews-dist-row">
                            <span class="reviews-dist-label" aria-hidden="true">
                                {{ row.stars }}
                                <Icon name="star" :size="14" filled />
                            </span>
                            <span class="reviews-dist-track" aria-hidden="true">
                                <span class="reviews-dist-fill" :style="{ transform: `scaleX(${row.share})` }"></span>
                            </span>
                            <span class="reviews-dist-count" aria-hidden="true">{{ row.count }}</span>
                            <span class="ui-sr-only">{{ t("reviews.distribution", { stars: row.stars, count: row.count }) }}</span>
                        </li>
                    </ul>
                    <p class="reviews-note">{{ t("reviews.starsOnlyNote") }}</p>
                </aside>

                <div ref="list" class="reviews-list">
                    <ol class="reviews-items">
                        <li v-for="review in items" :key="`review_${review.id}`" class="ui-card reviews-card">
                            <div class="reviews-head">
                                <Avatar
                                    v-if="review.author"
                                    class="reviews-avatar"
                                    :seed="review.author.avatar_seed"
                                    :overrides="review.author.avatar_overrides"
                                    :size="44"
                                />
                                <img v-else class="reviews-avatar reviews-avatar--mark" src="/images/foodsy-mark.svg" alt="" />
                                <div class="reviews-who">
                                    <h3 class="reviews-name">{{ review.author ? review.author.name : t("reviews.anonymous") }}</h3>
                                    <p class="reviews-meta">
                                        <time :datetime="review.created_at">{{ review.day }}</time>
                                        · <Icon name="check" :size="14" /> {{ t("reviews.verified") }}
                                    </p>
                                </div>
                                <StarsBox class="reviews-stars" :average="review.rating" />
                            </div>
                            <p class="reviews-text">{{ review.comment }}</p>
                            <div v-if="review.reply" class="reviews-reply">
                                <p class="reviews-reply-label">{{ t("reviews.replyFrom", { restaurant }) }}</p>
                                <p class="reviews-reply-text">{{ review.reply }}</p>
                            </div>
                        </li>
                    </ol>
                    <PaginateEl v-if="pagination.last > 1" local :paginate="pagination" @go="goTo" />
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import { restReviewsLogic } from "./RestReviews.logic.js";

export default restReviewsLogic;
</script>

<style scoped src="./RestReviews.css"></style>
