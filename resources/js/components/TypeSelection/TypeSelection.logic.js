import { mapGetters, mapState } from "vuex";
import { TypeComp } from "../TypeComp";
import { t } from "../../utils";

const typeSelectionLogic = {
    name: "TypeSelection",
    components: { TypeComp },
    computed: {
        ...mapState(["alltypes"]),
        ...mapGetters(["selectedTypesLenght"])
    },
    methods: {
        t
    }
};

export { typeSelectionLogic };
