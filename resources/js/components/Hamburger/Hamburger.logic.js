import { mapMutations, mapState } from "vuex";
import { Icon } from "../Icon";
import { MenuMobile } from "../MenuMobile";
import { t } from "../../utils";

// menu button of small screens: MenuMobile is the drawer it opens
const hamburgerLogic = {
    name: "Hamburger",
    components: { Icon, MenuMobile },
    computed: {
        ...mapState(["menuMobile"])
    },
    methods: {
        ...mapMutations(["toggleMenuMobile"]),
        t
    }
};

export { hamburgerLogic };
