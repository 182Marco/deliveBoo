import { mapActions } from "vuex";
import { Header } from "./components/Header";
import { Footer } from "./components/Footer";
import { t } from "./utils";

const appLogic = {
    name: "App",
    components: { Header, Footer },
    created() {
        this.getTypes();
        this.loadUser();
    },
    methods: {
        ...mapActions(["getTypes", "loadUser"]),
        t
    }
};

export { appLogic };
