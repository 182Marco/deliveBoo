import { mapMutations, mapState } from "vuex";
import { Icon } from "../Icon";
import { QuantityStepper } from "../QuantityStepper";
import { cartTotal, countPortions, formatPrice, t, uniquePlates } from "../../utils";

// live summary of the bag: editable on the menu and in the bag, read-only at checkout
const orderSummaryLogic = {
    name: "OrderSummary",
    components: { Icon, QuantityStepper },
    props: {
        readonly: Boolean,
        // route name of the main button: none, no button
        ctaRoute: { type: String, default: "" },
        // empty: "Review order"
        ctaLabel: { type: String, default: "" }
    },
    computed: {
        ...mapState(["cart"]),
        // one line per plate, with its number of portions
        lines() {
            return uniquePlates(this.cart).map(plate => ({ plate, count: countPortions(this.cart, plate) }));
        },
        amount() {
            return formatPrice(cartTotal(this.cart));
        }
    },
    methods: {
        ...mapMutations(["addPlate", "removePlate"]),
        formatPrice,
        t
    }
};

export { orderSummaryLogic };
