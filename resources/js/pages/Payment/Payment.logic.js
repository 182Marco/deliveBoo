import { markRaw } from "vue";
import { mapMutations, mapState } from "vuex";
import { createHostedFields, postOrder } from "../../api";
import { formatPrice } from "../../utils";
import { CUSTOMER_FIELDS, buildOrder, emptyCustomer, isCustomerComplete, pickCustomer } from "./Payment.utils.js";

const paymentLogic = {
    name: "Payment",
    data: () => ({
        customerFields: CUSTOMER_FIELDS,
        ...emptyCustomer(),
        error: "",
        hostedFieldInstance: false,
        nonce: "",
        errors: {},
        sending: false
    }),
    computed: {
        ...mapState(["total", "cart"])
    },
    async mounted() {
        try {
            // markRaw: a Braintree instance must not be wrapped in a Vue 3 reactive Proxy
            this.hostedFieldInstance = markRaw(await createHostedFields());
        } catch (error) {
            console.error(error);
        }
    },
    methods: {
        ...mapMutations(["emptyCart"]),
        formatPrice,
        // nothing happens until Braintree is ready and every customer field is filled
        async payWithCreditCard() {
            if (!this.hostedFieldInstance || !isCustomerComplete(this.$data)) return;
            this.error = "";
            this.nonce = "";
            try {
                const { nonce } = await this.hostedFieldInstance.tokenize();
                this.nonce = nonce;
                await this.postForm();
            } catch (error) {
                console.error(error);
                this.error = error.message;
            }
        },
        async postForm() {
            this.sending = true;
            const order = buildOrder(this.cart, this.total, pickCustomer(this.$data));
            try {
                const response = await postOrder(order);
                this.sending = false;
                if (response.errors) {
                    this.errors = response.errors;
                    return;
                }
                this.errors = {};
                Object.assign(this.$data, emptyCustomer());
                this.emptyCart();
                this.$router.push({ name: "success" });
            } catch (error) {
                // network error: cart and page stay, `sending` stays true (current behaviour)
                console.error(error);
            }
        }
    }
};

export { paymentLogic };
