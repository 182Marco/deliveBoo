import { mapGetters, mapState } from "vuex";
import { Hamburger } from "../Hamburger";
import { Icon } from "../Icon";
import { Logo } from "../Logo";
import { RestaurantSearch } from "../RestaurantSearch";
import { cartTotal, formatPrice } from "../../utils";

// sections of the home page reachable from every page
const LINKS = [
    { to: { name: "home", hash: "#cuisines" }, text: "Cuisines" },
    { to: { name: "home", hash: "#top-rated" }, text: "Guest favourites" },
    { to: { name: "home", hash: "#explore" }, text: "All restaurants" }
];

const headerLogic = {
    name: "Header",
    components: { Hamburger, Icon, Logo, RestaurantSearch },
    data: () => ({
        links: LINKS
    }),
    computed: {
        ...mapState(["cart"]),
        ...mapGetters(["cartLenght"]),
        cartAmount() {
            return formatPrice(cartTotal(this.cart));
        },
        bagLabel() {
            return this.cartLenght ? `Your bag: ${this.cartLenght} items, €${this.cartAmount}` : "Your bag is empty";
        }
    }
};

export { headerLogic };
