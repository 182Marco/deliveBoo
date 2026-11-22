import { mapActions } from "vuex";
import { Header } from "./components/Header";
import { Footer } from "./components/Footer";

const appLogic = {
    name: "App",
    components: { Header, Footer },
    created() {
        this.getTypes();
    },
    methods: {
        ...mapActions(["getTypes"])
    }
};

export { appLogic };
