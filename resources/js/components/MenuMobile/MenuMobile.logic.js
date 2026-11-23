import { mapMutations, mapState } from "vuex";
import { Icon } from "../Icon";
import { Logo } from "../Logo";
import { t } from "../../utils";

// pages of the SPA (router) and pages served by Laravel (full reload); texts are keys of t()
const ROUTE_LINKS = [
    { to: { name: "home", hash: "#cuisines" }, icon: "utensils", text: "nav.cuisines" },
    { to: { name: "home", hash: "#top-rated" }, icon: "star", text: "nav.favourites" },
    { to: { name: "home", hash: "#explore" }, icon: "store", text: "nav.restaurants" },
    { to: { name: "cart" }, icon: "bag", text: "nav.bag" }
];

const PAGE_LINKS = [
    // partner CTAs preselect the restaurateur sign-up; one login page for every account
    { href: "/register?role=restaurateur", icon: "plus", text: "nav.addRestaurant" },
    { href: "/login", icon: "user", text: "nav.login" }
];

const menuMobileLogic = {
    name: "MenuMobile",
    components: { Icon, Logo },
    data: () => ({
        routeLinks: ROUTE_LINKS,
        pageLinks: PAGE_LINKS
    }),
    computed: {
        ...mapState(["menuMobile"])
    },
    watch: {
        // keyboard users land on the close button, so Esc and Tab work at once
        async menuMobile(open) {
            if (!open) return;
            await this.$nextTick();
            this.$refs.close?.focus();
        }
    },
    methods: {
        ...mapMutations(["toggleMenuMobile"]),
        t
    }
};

export { menuMobileLogic };
