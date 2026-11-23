import { mapActions, mapMutations, mapState } from "vuex";
import { Icon } from "../Icon";
import { Logo } from "../Logo";
import { t } from "../../utils";
import { accountAreaLink, fullName } from "../AccountMenu/AccountMenu.utils.js";

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
        routeLinks: ROUTE_LINKS
    }),
    computed: {
        ...mapState(["menuMobile", "user"]),
        // a logged-in account has its own block below: no "Log in" here
        pageLinks() {
            return this.user ? PAGE_LINKS.filter(link => link.href !== "/login") : PAGE_LINKS;
        },
        // under 600px the header has no room for the avatar: the account lives here
        fullName() {
            return fullName(this.user);
        },
        accountLinks() {
            if (!this.user) return [];
            const area = accountAreaLink(this.user.role);
            return [
                { href: "/account", icon: "user", text: "account.settings" },
                ...(area ? [{ href: this.user.home, icon: area.icon, text: area.text }] : [])
            ];
        }
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
        ...mapActions(["logoutUser"]),
        t,
        async onLogout() {
            this.toggleMenuMobile();
            await this.logoutUser();
            // a fresh page: new CSRF token and nothing of the account left in memory (like AccountMenu)
            window.location.assign("/");
        }
    }
};

export { menuMobileLogic };
