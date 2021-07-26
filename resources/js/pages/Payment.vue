<template>
    <div>
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
                    <div class="alert alert-success" v-if="nonce">
                        Successfully generated nonce.
                    </div>
                    <form>
                        <!-- NAME -->
                        <div class="form-group">
                            <label>Name</label
                            ><span
                                v-for="(er, i) in errors.customer_name"
                                :key="`name_er${i}`"
                                class="text-danger d-inline-block ml-3"
                            >
                                {{ er }}
                            </span>
                            <input
                                minlength="3"
                                maxlength="20"
                                type="text"
                                v-model="customer_name"
                                class="form-control"
                                required
                            />
                        </div>
                        <!-- LAST NAME -->
                        <div class="form-group">
                            <label>Last name</label>
                            <span
                                class="text-danger d-inline-block ml-3"
                                v-for="(er, i) in errors.customer_lastName"
                                :key="`last_er${i}`"
                            >
                                {{ er }}</span
                            >
                            <input
                                minlength="3"
                                maxlength="20"
                                type="text"
                                v-model="customer_lastName"
                                class="form-control"
                                required
                            />
                        </div>
                        <!-- EMAIL -->
                        <div class="form-group">
                            <label>Email</label>
                            <span
                                class="text-danger d-inline-block ml-3"
                                v-for="(er, i) in errors.customer_email"
                                :key="`mail_er${i}`"
                            >
                                {{ er }}</span
                            >
                            <input
                                type="email"
                                v-model="customer_email"
                                class="form-control"
                                required
                            />
                        </div>
                        <div class="form-group">
                            <!-- PHONE -->
                            <label>Phone</label>
                            <span
                                class="text-danger d-inline-block ml-3"
                                v-for="(er, i) in errors.customer_phone"
                                :key="`phone_er${i}`"
                            >
                                {{ er }}</span
                            >
                            <input
                                minlength="9"
                                maxlength="11"
                                type="text"
                                v-model="customer_phone"
                                class="form-control"
                                required
                            />
                        </div>
                        <!-- ADDRESS -->
                        <div class="form-group">
                            <label>Address</label>
                            <span
                                class="text-danger d-inline-block ml-3"
                                v-for="(er, i) in errors.customer_address"
                                :key="`address_er${i}`"
                            >
                                {{ er }}</span
                            >
                            <input
                                minlength="3"
                                type="text"
                                v-model="customer_address"
                                class="form-control"
                                required
                            />
                        </div>
                        <!-- AMMOUNT -->
                        <div class="form-group">
                            <label for="amount">Amount</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">€</span>
                                </div>
                                <input
                                    type="number"
                                    id="amount"
                                    class="form-control not-allowed"
                                    placeholder="Enter Amount"
                                    :value="total.toFixed(2)"
                                    disabled
                                />
                            </div>
                        </div>
                        <hr />
                        <div class="form-group">
                            <label>Credit Card Number</label>
                            <div
                                id="creditCardNumber"
                                class="form-control"
                            ></div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-6">
                                    <label>Expire Date</label>
                                    <div
                                        id="expireDate"
                                        class="form-control"
                                    ></div>
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
            <button
                class="btn btn-primary btn-block"
                @click.prevent="payWithCreditCard"
                type="submit"
            >
                {{ sending ? "sending..." : "Pay with Credit Card" }}
            </button>
            <div class="alert alert-danger" v-if="error">
                {{ error }}
            </div>
        </div>
    </div>
</template>

<script>
import { mapState, mapMutations } from "vuex";
import braintree from "braintree-web";
export default {
    name: "Payment",
    data() {
        return {
            error: "",
            hostedFieldInstance: false,
            nonce: "",
            customer_name: "",
            customer_lastName: "",
            customer_email: "",
            customer_phone: "",
            customer_address: "",
            errors: {},
            sending: false
        };
    },
    mounted() {
        braintree.client
            .create({
                authorization: "sandbox_rzpfyzvh_dgt5rntdphpdzyhh"
            })
            .then(clientInstance => {
                let options = {
                    client: clientInstance,
                    styles: {
                        input: {
                            "font-size": "14px",
                            "font-family": "Open Sans"
                        }
                    },
                    fields: {
                        number: {
                            selector: "#creditCardNumber",
                            placeholder: "Enter Credit Card"
                        },
                        cvv: {
                            selector: "#cvv",
                            placeholder: "Enter CVV"
                        },
                        expirationDate: {
                            selector: "#expireDate",
                            placeholder: "00 / 0000"
                        }
                    }
                };
                return braintree.hostedFields.create(options);
            })
            .then(hostedFieldInstance => {
                this.hostedFieldInstance = hostedFieldInstance;
            })
            .catch(err => {
                console.error(err);
            });
    },
    computed: {
        ...mapState(["total", "cart"])
    },
    methods: {
        ...mapMutations(["emptyCart"]),
        payWithCreditCard() {
            if (
                this.hostedFieldInstance &&
                this.customer_name &&
                this.customer_lastName &&
                this.customer_email &&
                this.customer_phone &&
                this.customer_address
            ) {
                this.error = "";
                this.nonce = "";
                this.hostedFieldInstance
                    .tokenize()
                    .then(payload => {
                        console.log(payload);
                        this.nonce = payload.nonce;
                        this.postForm();
                    })
                    .catch(err => {
                        console.error(err);
                        this.error = err.message;
                    });
            }
        },
        // POST THE FORM
        postForm() {
            this.sending = true;
            axios
                .post("http://127.0.0.1:8000/api/orders", {
                    restaurant_id: this.cart[0].restaurant_id,
                    price: this.total,
                    customer_name: this.customer_name,
                    customer_lastName: this.customer_lastName,
                    customer_email: this.customer_email,
                    customer_phone: this.customer_phone,
                    customer_address: this.customer_address,
                    plates: this.cart.map((e, i) => (i = e.id))
                })
                .then(r => {
                    console.log(r.data);
                    this.sending = false;
                    if (r.data.errors) {
                        this.errors = r.data.errors;
                        //
                    } else {
                        // clean errors
                        this.errors = {};
                        // clean fields after success
                        this.customer_name = "";
                        this.customer_lastName = "";
                        this.customer_email = "";
                        this.customer_phone = "";
                        this.customer_address = "";
                        //
                        this.$store.commit("emptyCart");
                        this.$router.push({ name: "success" });
                    }
                })
                .catch(er => console.log(er));
        }
    }
};
</script>

<style lang="scss" scoped>
.not-allowed {
    cursor: not-allowed;
}
.form-group {
    span {
        font-size: 0.9rem;
    }
}
</style>
