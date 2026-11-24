import { mapActions } from "vuex";
import { CartNotice } from "./components/CartNotice";
import { Header } from "./components/Header";
import { Footer } from "./components/Footer";
import { t } from "./utils";

const appLogic = {
    name: "App",
    components: { CartNotice, Header, Footer },
    created() {
        this.getTypes();
        this.loadUser();
        // the cart lives on the server (or locally while the server cart is off): asked on every load
        this.loadCart();
        this.watchCart();
    },
    methods: {
        ...mapActions(["getTypes", "loadUser", "loadCart", "watchCart"]),
        t
    }
};

export { appLogic };
