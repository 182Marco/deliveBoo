import { mapActions } from "vuex";
// direct imports, not the components barrel: the CSS of the other components must come
// after the global App.scss (as before), or plain rules like `.cont` would win over the scoped reset
import { Header } from "./components/Header";
import { Content } from "./components/Content";
import { Footer } from "./components/Footer";

const appLogic = {
    name: "App",
    components: { Header, Content, Footer },
    created() {
        this.getTypes();
    },
    methods: {
        ...mapActions(["getTypes"])
    }
};

export { appLogic };
