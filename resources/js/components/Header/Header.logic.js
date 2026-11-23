import { mapGetters, mapState } from "vuex";
import { AccountMenu } from "../AccountMenu";
import { Hamburger } from "../Hamburger";
import { Icon } from "../Icon";
import { LocaleSwitch } from "../LocaleSwitch";
import { Logo } from "../Logo";
import { RestaurantSearch } from "../RestaurantSearch";
import { cartTotal, formatPrice, t } from "../../utils";

// sections of the home page reachable from every page (texts are keys of t())
const LINKS = [
    { to: { name: "home", hash: "#cuisines" }, text: "nav.cuisines" },
    { to: { name: "home", hash: "#top-rated" }, text: "nav.favourites" },
    { to: { name: "home", hash: "#explore" }, text: "nav.restaurants" }
];

const headerLogic = {
    name: "Header",
    components: { AccountMenu, Hamburger, Icon, LocaleSwitch, Logo, RestaurantSearch },
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
            return this.cartLenght
                ? t("header.bagFull", { count: this.cartLenght, amount: this.cartAmount })
                : t("header.bagEmpty");
        }
    },
    methods: {
        t
    }
};

export { headerLogic };
