import { mapMutations, mapState } from "vuex";
import { countPortions, formatPrice, uniquePlates } from "../../utils";

const cartLogic = {
    name: "Cart",
    computed: {
        ...mapState(["cart", "total"]),
        // one row per plate, with its number of portions
        plates() {
            return uniquePlates(this.cart);
        }
    },
    watch: {
        // deep: in Vue 3 push/splice on the same array do not trigger a plain watcher
        cart: {
            handler() {
                this.changeTotal();
            },
            deep: true
        }
    },
    created() {
        this.changeTotal();
    },
    methods: {
        ...mapMutations(["addPlate", "removePlate", "changeTotal", "emptyCart"]),
        portionsOf(plate) {
            return countPortions(this.cart, plate);
        },
        formatPrice
    }
};

export { cartLogic };
