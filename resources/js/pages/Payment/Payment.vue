<template>
    <div class="mrg">
        <!-- Bootstrap 4.1.3 from CDN, loaded only on this page as before -->
        <link
            rel="stylesheet"
            href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
            integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO"
            crossorigin="anonymous"
        />
        <div class="col-md-6 offset-md-3">
            <div class="card bg-light">
                <div class="card-header">
                    <h5>Payment Information</h5>
                    <p class="mb-2">* all fields are required</p>
                </div>
                <div class="card-body">
                    <div v-if="nonce" class="alert alert-success">
                        Successfully generated nonce.
                    </div>
                    <form>
                        <div v-for="field in customerFields" :key="field.key" class="form-group">
                            <label>{{ field.label }}</label>
                            <span
                                v-for="(er, i) in errors[field.key]"
                                :key="`${field.key}_er${i}`"
                                class="text-danger d-inline-block ml-3"
                            >
                                {{ er }}
                            </span>
                            <input
                                v-model="$data[field.key]"
                                :type="field.type"
                                :minlength="field.minlength"
                                :maxlength="field.maxlength"
                                class="form-control"
                                required
                            />
                        </div>
                        <div class="form-group">
                            <label for="amount">Amount</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">€</span>
                                </div>
                                <input
                                    id="amount"
                                    type="number"
                                    class="form-control not-allowed"
                                    placeholder="Enter Amount"
                                    :value="formatPrice(total)"
                                    disabled
                                />
                            </div>
                        </div>
                        <hr />
                        <!-- containers filled by the Braintree hosted fields -->
                        <div class="form-group">
                            <label>Credit Card Number</label>
                            <div id="creditCardNumber" class="form-control"></div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-6">
                                    <label>Expire Date</label>
                                    <div id="expireDate" class="form-control"></div>
                                </div>
                                <div class="col-6">
                                    <label>CVV</label>
                                    <div id="cvv" class="form-control"></div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <button class="btn btn-primary btn-block" type="submit" @click.prevent="payWithCreditCard">
                {{ sending ? "sending..." : "Pay with Credit Card" }}
            </button>
            <div v-if="error" class="alert alert-danger">
                {{ error }}
            </div>
        </div>
    </div>
</template>

<script>
import { paymentLogic } from "./Payment.logic.js";

export default paymentLogic;
</script>

<style lang="scss" scoped src="./Payment.scss"></style>
