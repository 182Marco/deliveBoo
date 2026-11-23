<template>
    <section class="ui-container ui-container--narrow done" aria-labelledby="done-title">
        <span class="done-badge"><Icon name="check" :size="36" /></span>
        <p class="ui-eyebrow">{{ t("success.eyebrow") }}</p>
        <h1 id="done-title" class="ui-display done-title">{{ t("success.titleStart") }} <span class="ui-accent">{{ t("success.titleAccent") }}</span></h1>
        <p class="ui-lead">
            {{ t("success.lead") }}
        </p>

        <!-- payment receipt: proof that the payment provider accepted the transaction -->
        <div v-if="receipt" class="receipt">
            <div class="receipt-head">
                <span class="receipt-status"><Icon name="check" :size="14" /> {{ t("success.paid") }}</span>
                <span class="receipt-order">{{ t("success.order", { id: receipt.order }) }}</span>
            </div>
            <dl class="receipt-rows">
                <div class="receipt-row">
                    <dt class="receipt-label">{{ t("success.processedBy") }}</dt>
                    <dd class="receipt-value">{{ receipt.provider.name }}</dd>
                </div>
                <div class="receipt-row">
                    <dt class="receipt-label">{{ t("success.transaction") }}</dt>
                    <dd class="receipt-value receipt-tx">
                        <code class="receipt-code">{{ receipt.tx }}</code>
                        <button
                            type="button"
                            class="ui-btn ui-btn--outline ui-btn--sm"
                            :aria-label="t('success.copyTransaction')"
                            @click="copyTransaction"
                        >
                            <Icon :name="copied ? 'check' : 'receipt'" :size="16" />
                            {{ t(copied ? "checkout.copied" : "checkout.copy") }}
                        </button>
                    </dd>
                </div>
            </dl>
            <p class="receipt-note">{{ receipt.provider.note }}</p>
        </div>

        <div class="done-actions">
            <router-link :to="{ name: 'home' }" class="ui-btn ui-btn--primary ui-btn--lg">
                {{ t("success.home") }}
                <Icon name="arrow-right" />
            </router-link>
            <!-- the order is linked to the account (step 7.1): it is already in the history -->
            <router-link v-if="user?.role === 'customer'" :to="{ name: 'myOrders' }" class="ui-btn ui-btn--outline ui-btn--lg">
                <Icon name="receipt" />
                {{ t("account.orders") }}
            </router-link>
        </div>
    </section>
</template>

<script>
import { successPageLogic } from "./SuccessPage.logic.js";

export default successPageLogic;
</script>

<style scoped src="./SuccessPage.css"></style>
