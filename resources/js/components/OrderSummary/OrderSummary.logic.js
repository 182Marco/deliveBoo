import { mapActions, mapGetters } from "vuex";
import { Icon } from "../Icon";
import { QuantityStepper } from "../QuantityStepper";
import { formatPrice, t } from "../../utils";

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
        // one line per plate, with its number of portions
        ...mapGetters({ lines: "cartLines", count: "cartCount", total: "cartAmount" }),
        amount() {
            return formatPrice(this.total);
        }
    },
    methods: {
        ...mapActions(["addOne", "removeOne"]),
        formatPrice,
        t
    }
};

export { orderSummaryLogic };
