import { mapMutations } from "vuex";
import { MenuMobile } from "../MenuMobile";

const hamburgerLogic = {
    name: "Hamburger",
    components: { MenuMobile },
    // bound to the root: also the clicks inside the open menu bubble here and close it
    methods: {
        ...mapMutations(["toggleMenuMobile"])
    }
};

export { hamburgerLogic };
