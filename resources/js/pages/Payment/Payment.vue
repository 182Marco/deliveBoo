<template>
    <div class="ui-container checkout">
        <router-link :to="{ name: 'cart' }" class="checkout-back">
            <Icon name="arrow-left" :size="18" />
            Back to your bag
        </router-link>
        <div class="ui-section-intro checkout-title">
            <p class="ui-eyebrow">Secure checkout</p>
            <h1 class="ui-title">Almost at your table</h1>
        </div>

        <div class="checkout-layout">
            <form class="checkout-form" novalidate @submit.prevent="payWithCreditCard">
                <section class="checkout-step" aria-labelledby="step-delivery">
                    <div class="checkout-step-head">
                        <span class="checkout-step-num">1</span>
                        <h2 id="step-delivery" class="ui-subtitle">Delivery details</h2>
                    </div>
                    <!-- test environments only: one discreet line, the details open in a dialog -->
                    <button
                        v-if="testGuide"
                        ref="testOpener"
                        type="button"
                        class="testbar"
                        aria-haspopup="dialog"
                        @click="openTestCards"
                    >
                        <span class="testbar-badge">Test mode</span>
                        <span class="testbar-text">No real money moves here: pay with a test card.</span>
                        <span class="testbar-link">
                            Test cards
                            <Icon name="arrow-right" :size="16" />
                        </span>
                    </button>
                    <div class="checkout-grid">
                        <div
                            v-for="field in customerFields"
                            :key="field.key"
                            class="ui-field checkout-field"
                            :class="{ 'checkout-wide': field.wide }"
                        >
                            <label class="ui-label" :for="field.key">
                                {{ field.label }} <span class="ui-required" aria-hidden="true">*</span>
                            </label>
                            <!-- the message is absolute: an error never moves the fields below -->
                            <div class="field" :class="{ 'is-error': fieldError(field.key) }">
                                <input
                                    :id="field.key"
                                    v-model="$data[field.key]"
                                    :type="field.type"
                                    :maxlength="field.maxlength"
                                    :autocomplete="field.autocomplete"
                                    class="ui-input"
                                    :class="{ 'ui-input--error': fieldError(field.key) }"
                                    required
                                    :aria-invalid="!!fieldError(field.key)"
                                    @input="clearError(field.key)"
                                />
                                <small v-if="fieldError(field.key)" class="field-error">{{ fieldError(field.key) }}</small>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="checkout-step" aria-labelledby="step-payment">
                    <div class="checkout-step-head">
                        <span class="checkout-step-num">2</span>
                        <h2 id="step-payment" class="ui-subtitle">Payment</h2>
                    </div>
                    <!-- containers filled by the Braintree hosted fields (or by their offline stand-in):
                         Vue never touches them, the error class goes on the wrapper -->
                    <div class="checkout-grid">
                        <div
                            v-for="card in cardFields"
                            :key="card.key"
                            class="ui-field checkout-field"
                            :class="{ 'checkout-wide': card.key === 'number' }"
                        >
                            <span class="ui-label">
                                {{ card.label }} <span class="ui-required" aria-hidden="true">*</span>
                            </span>
                            <div class="field" :class="{ 'is-error': fieldError(`card_${card.key}`) }">
                                <div
                                    :id="card.id"
                                    class="ui-input checkout-card-input"
                                    :class="{ 'ui-input--error': fieldError(`card_${card.key}`) }"
                                ></div>
                                <small v-if="fieldError(`card_${card.key}`)" class="field-error">
                                    {{ fieldError(`card_${card.key}`) }}
                                </small>
                            </div>
                        </div>
                    </div>
                    <p class="checkout-secure">
                        <Icon name="lock" :size="16" />
                        Card details go straight to our payment provider and never reach Foodsy.
                    </p>
                </section>

                <div v-if="nonce" class="ui-notice ui-notice--success" role="status">
                    <Icon name="check" />
                    <p>Card verified, placing your order…</p>
                </div>
                <div v-if="error" class="ui-notice ui-notice--error" role="alert">
                    <p>{{ error }}</p>
                </div>
                <div
                    v-for="(er, i) in paymentErrors"
                    :key="`payment_er${i}`"
                    class="ui-notice ui-notice--error"
                    role="alert"
                >
                    <p>{{ er }}</p>
                </div>

                <button
                    class="ui-btn ui-btn--primary ui-btn--lg ui-btn--block"
                    type="submit"
                    :disabled="sending || !hostedFieldInstance"
                >
                    <Icon name="lock" :size="18" />
                    {{ sending ? "Placing your order…" : `Pay €${amount}` }}
                </button>
            </form>

            <div class="checkout-aside">
                <OrderSummary readonly />
            </div>
        </div>
        <div v-if="testOpen && testGuide" class="ui-modal-backdrop" @click.self="closeTestCards" @keydown.esc="closeTestCards">
            <section class="ui-modal testmodal" role="dialog" aria-modal="true" aria-labelledby="testmodal-title">
                <div class="testmodal-head">
                    <span class="testbar-badge">Test mode</span>
                    <button
                        ref="testClose"
                        type="button"
                        class="ui-btn ui-btn--ghost ui-btn--icon ui-btn--icon-sm"
                        aria-label="Close"
                        @click="closeTestCards"
                    >
                        <Icon name="close" :size="18" />
                    </button>
                </div>
                <h2 id="testmodal-title" class="ui-subtitle">Test payments</h2>
                <p class="testmodal-note">{{ testGuide.note }}</p>
                <ul class="testpay-list">
                    <li v-for="row in testGuide.rows" :key="row.key" class="testpay-row">
                        <span class="testpay-status" :class="row.ok ? 'testpay-status--ok' : 'testpay-status--ko'">
                            <Icon :name="row.ok ? 'check' : 'close'" :size="16" />
                        </span>
                        <div class="testpay-body">
                            <p class="testpay-label">{{ row.label }}</p>
                            <p class="testpay-card ui-price">{{ row.card }}</p>
                            <p class="testpay-hint">{{ row.hint }}</p>
                        </div>
                        <button
                            type="button"
                            class="ui-btn ui-btn--outline ui-btn--sm testpay-copy"
                            :aria-label="`Copy the card number for: ${row.label}`"
                            @click="copyCard(row)"
                        >
                            <Icon :name="copied === row.key ? 'check' : 'receipt'" :size="16" />
                            {{ copied === row.key ? "Copied" : "Copy" }}
                        </button>
                    </li>
                </ul>
                <div class="ui-modal-actions">
                    <button type="button" class="ui-btn ui-btn--primary ui-btn--block" @click="closeTestCards">
                        Got it
                    </button>
                </div>
            </section>
        </div>
    </div>
</template>

<script>
import { paymentLogic } from "./Payment.logic.js";

export default paymentLogic;
</script>

<style scoped src="./Payment.css"></style>
