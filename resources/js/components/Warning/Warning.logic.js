import { mapActions, mapMutations, mapState } from "vuex";
import { Icon } from "../Icon";
import { scrollToTop } from "../../utils";

// modal opened when a plate of another restaurant is added to the cart
const warningLogic = {
    name: "Warning",
    components: { Icon },
    computed: {
        ...mapState(["cart"]),
        // restaurant of the plates already in the cart
        cartSlug() {
            return this.cart[0].restaurant_slug;
        }
    },
    // keyboard focus moves into the dialog, so Esc and Tab work at once
    mounted() {
        this.$refs.primary?.focus();
    },
    methods: {
        ...mapMutations(["changeWarn", "emptyCart"]),
        ...mapActions(["getMenuAndDetails"]),
        emptyCartAndStay() {
            this.changeWarn();
            this.emptyCart();
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
