<template>
    <section class="ui-container ui-container--narrow done" aria-labelledby="done-title">
        <span class="done-badge"><Icon name="check" :size="36" /></span>
        <p class="ui-eyebrow">Order confirmed</p>
        <h1 id="done-title" class="ui-display done-title">Thank you, <span class="ui-accent">enjoy!</span></h1>
        <p class="ui-lead">
            The kitchen has your order and is getting it ready. A confirmation email with every detail is on
            its way to your inbox.
        </p>

        <!-- payment receipt: proof that the payment provider accepted the transaction -->
        <div v-if="receipt" class="receipt">
            <div class="receipt-head">
                <span class="receipt-status"><Icon name="check" :size="14" /> Paid</span>
                <span class="receipt-order">Order #{{ receipt.order }}</span>
            </div>
            <dl class="receipt-rows">
                <div class="receipt-row">
                    <dt class="receipt-label">Processed by</dt>
                    <dd class="receipt-value">{{ receipt.provider.name }}</dd>
                </div>
                <div class="receipt-row">
                    <dt class="receipt-label">Transaction ID</dt>
                    <dd class="receipt-value receipt-tx">
                        <code class="receipt-code">{{ receipt.tx }}</code>
                        <button
                            type="button"
                            class="ui-btn ui-btn--outline ui-btn--sm"
                            aria-label="Copy the transaction ID"
                            @click="copyTransaction"
                        >
                            <Icon :name="copied ? 'check' : 'receipt'" :size="16" />
                            {{ copied ? "Copied" : "Copy" }}
                        </button>
                    </dd>
                </div>
            </dl>
            <p class="receipt-note">{{ receipt.provider.note }}</p>
        </div>

        <router-link :to="{ name: 'home' }" class="ui-btn ui-btn--primary ui-btn--lg">
            Back to home
            <Icon name="arrow-right" />
        </router-link>
    </section>
</template>

<script>
import { successPageLogic } from "./SuccessPage.logic.js";

export default successPageLogic;
</script>

<style scoped src="./SuccessPage.css"></style>
