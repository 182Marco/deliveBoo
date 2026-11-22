import { mapActions, mapMutations, mapState } from "vuex";
import { scrollToTop } from "../../utils";

// modal opened when a plate of another restaurant is added to the cart
const warningLogic = {
    name: "Warning",
    computed: {
        ...mapState(["cart"]),
        // restaurant of the plates already in the cart
        cartSlug() {
            return this.cart[0].restaurant_slug;
        }
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
