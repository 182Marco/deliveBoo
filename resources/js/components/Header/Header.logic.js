import { mapGetters } from "vuex";
import { Hamburger } from "../Hamburger";
import { scrollToTop } from "../../utils";

const headerLogic = {
    name: "Header",
    components: { Hamburger },
    computed: {
        ...mapGetters(["cartLenght"])
    },
    methods: {
        scrollToTop
    }
};

export { headerLogic };
