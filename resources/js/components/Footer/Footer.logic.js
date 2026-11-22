import { Icon } from "../Icon";
import { Logo } from "../Logo";

// only real destinations: SPA routes (`to`) or Laravel pages (`href`)
const COLUMNS = [
    {
        title: "Discover",
        links: [
            { to: { name: "home", hash: "#cuisines" }, text: "Cuisines" },
            { to: { name: "home", hash: "#explore" }, text: "All restaurants" },
            { to: { name: "home", hash: "#top-rated" }, text: "Guest favourites" },
            { to: { name: "cart" }, text: "Your bag" }
        ]
    },
    {
        title: "Partners",
        links: [
            { href: "/register", text: "Add your restaurant" },
            { href: "/login", text: "Partner login" }
        ]
    }
];

const footerLogic = {
    name: "Footer",
    components: { Icon, Logo },
    data: () => ({
        columns: COLUMNS,
        year: new Date().getFullYear()
    })
};

export { footerLogic };
