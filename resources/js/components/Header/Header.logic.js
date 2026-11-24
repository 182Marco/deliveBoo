import { mapGetters } from "vuex";
import { AccountMenu } from "../AccountMenu";
import { Hamburger } from "../Hamburger";
import { Icon } from "../Icon";
import { LocaleSwitch } from "../LocaleSwitch";
import { Logo } from "../Logo";
import { RestaurantSearch } from "../RestaurantSearch";
import { formatPrice, t } from "../../utils";

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
        ...mapGetters(["cartCount", "cartAmount"]),
        bagAmount() {
            return formatPrice(this.cartAmount);
        },
        bagLabel() {
            return this.cartCount
                ? t("header.bagFull", { count: this.cartCount, amount: this.bagAmount })
                : t("header.bagEmpty");
        }
    },
    methods: {
        t
    }
};

export { headerLogic };
