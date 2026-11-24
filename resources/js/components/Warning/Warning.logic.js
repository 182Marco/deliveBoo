import { mapActions, mapGetters, mapMutations } from "vuex";
import { Icon } from "../Icon";
import { scrollToTop, t } from "../../utils";

// modal opened when a plate of another restaurant is added to the cart
const warningLogic = {
    name: "Warning",
    components: { Icon },
    computed: {
        ...mapGetters(["cartRestaurant"]),
        // restaurant of the plates already in the cart ("" once emptied, while the dialog closes)
        cartSlug() {
            return this.cartRestaurant?.slug ?? "";
        }
    },
    // keyboard focus moves into the dialog, so Esc and Tab work at once
    mounted() {
        this.$refs.primary?.focus();
    },
    methods: {
        ...mapMutations(["changeWarn"]),
        ...mapActions(["getMenuAndDetails", "clearCart"]),
        t,
        emptyCartAndStay() {
            this.changeWarn();
            this.clearCart();
            scrollToTop();
        },
        // same route component, new slug: RestMenu does not reload by itself
        backToCartRestaurant() {
            this.changeWarn();
            this.getMenuAndDetails({ page: 1, slug: this.cartSlug });
            scrollToTop();
        }
    }
};

export { warningLogic };
