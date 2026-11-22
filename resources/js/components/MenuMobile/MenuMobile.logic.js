import { mapState } from "vuex";

const MAIN_LINKS = [
    { href: "/register", icon: "fas fa-utensils", text: "Aggiungi il tuo ristorante" },
    { href: "/register", icon: "fas fa-store-alt", text: "Crea un account aziendale" },
    { href: "#", icon: "fas fa-question", text: "FAQ" }
];

const LANGUAGES = ["Italiano", "Italy"];

const menuMobileLogic = {
    name: "MenuMobile",
    data: () => ({
        mainLinks: MAIN_LINKS,
        languages: LANGUAGES
    }),
    computed: {
        ...mapState(["menuMobile"])
    }
};

export { menuMobileLogic };
