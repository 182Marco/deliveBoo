import { mapActions, mapGetters } from "vuex";
import { FoodImage, Icon, QuantityStepper } from "../../components";
import { formatPrice, t } from "../../utils";

const cartLogic = {
    name: "Cart",
    components: { FoodImage, Icon, QuantityStepper },
    computed: {
        // one row per plate, with its number of portions
        ...mapGetters({ lines: "cartLines", count: "cartCount", total: "cartAmount", cartReady: "cartReady" }),
        // a dish no longer orderable (sold out, deleted) blocks the checkout until it is removed
        hasUnavailable() {
            return this.lines.some(line => line.plate.orderable === false);
        }
    },
    methods: {
        ...mapActions(["addOne", "removeOne", "clearCart"]),
        formatPrice,
        t
    }
};

export { cartLogic };
