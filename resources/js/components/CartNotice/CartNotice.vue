<template>
    <div class="cart-notices" aria-live="polite">
        <p v-for="notice in cartNotices" :key="notice.id" :class="['ui-notice', toneOf(notice), 'cart-notice']">
            <span class="cart-notice-text">{{ textOf(notice) }}</span>
            <button type="button" class="cart-notice-close" :aria-label="t('cartNotice.close')" @click="dropCartNotice(notice.id)">
                <Icon name="close" :size="16" />
            </button>
        </p>
    </div>
</template>

<script>
import { cartNoticeLogic } from "./CartNotice.logic.js";

export default cartNoticeLogic;
</script>

<style scoped src="./CartNotice.css"></style>
