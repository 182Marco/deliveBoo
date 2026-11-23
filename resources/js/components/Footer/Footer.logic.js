import { Icon } from "../Icon";
import { Logo } from "../Logo";
import { t } from "../../utils";

// only real destinations: SPA routes (`to`) or Laravel pages (`href`); titles and texts are keys of t()
const COLUMNS = [
    {
        title: "footer.discover",
        links: [
            { to: { name: "home", hash: "#cuisines" }, text: "nav.cuisines" },
            { to: { name: "home", hash: "#explore" }, text: "nav.restaurants" },
            { to: { name: "home", hash: "#top-rated" }, text: "nav.favourites" },
            { to: { name: "cart" }, text: "nav.bag" }
        ]
    },
    {
        title: "footer.partners",
        links: [
            { href: "/register?role=restaurateur", text: "nav.addRestaurant" },
            { href: "/login", text: "nav.partnerLogin" }
        ]
    }
];

const footerLogic = {
    name: "Footer",
    components: { Icon, Logo },
    data: () => ({
        columns: COLUMNS,
        year: new Date().getFullYear()
    }),
    methods: {
        t
    }
};

export { footerLogic };
