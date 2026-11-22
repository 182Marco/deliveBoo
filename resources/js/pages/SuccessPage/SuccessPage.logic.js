import { Icon } from "../../components";
import { receiptFrom } from "./SuccessPage.utils.js";

// how long the "Copied" confirmation stays on the button
const COPIED_MS = 2000;

// page shown after a completed order, with the payment receipt passed by the checkout
const successPageLogic = {
    name: "SuccessPage",
    components: { Icon },
    data: () => ({
        copied: false
    }),
    computed: {
        receipt() {
            return receiptFrom(this.$route?.query);
        }
    },
    methods: {
        async copyTransaction() {
            try {
                await navigator.clipboard.writeText(this.receipt.tx);
                this.copied = true;
                setTimeout(() => {
                    this.copied = false;
                }, COPIED_MS);
            } catch (error) {
                console.error(error);
            }
        }
    }
};

export { successPageLogic };
