import { mapMutations, mapState } from "vuex";
import { Icon } from "../Icon";
import { Logo } from "../Logo";

// pages of the SPA (router) and pages served by Laravel (full reload)
const ROUTE_LINKS = [
    { to: { name: "home", hash: "#cuisines" }, icon: "utensils", text: "Cuisines" },
    { to: { name: "home", hash: "#top-rated" }, icon: "star", text: "Guest favourites" },
    { to: { name: "home", hash: "#explore" }, icon: "store", text: "All restaurants" },
    { to: { name: "cart" }, icon: "bag", text: "Your bag" }
];

const PAGE_LINKS = [
    { href: "/register", icon: "plus", text: "Add your restaurant" },
    { href: "/login", icon: "user", text: "Partner login" }
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
        ...mapMutations(["toggleMenuMobile"])
    }
};

export { menuMobileLogic };
