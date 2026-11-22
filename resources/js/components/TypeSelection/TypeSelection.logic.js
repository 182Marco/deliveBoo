import { mapGetters, mapState } from "vuex";
import { TypeComp } from "../TypeComp";

const typeSelectionLogic = {
    name: "TypeSelection",
    components: { TypeComp },
    computed: {
        ...mapState(["alltypes"]),
        ...mapGetters(["selectedTypesLenght"])
    }
};

export { typeSelectionLogic };
