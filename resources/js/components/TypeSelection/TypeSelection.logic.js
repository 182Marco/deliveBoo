import { mapGetters, mapState } from "vuex";
import { TypeComp } from "../TypeComp";
import { t } from "../../utils";

const ROWS = 3;

// the cuisines in ROWS consecutive groups (same reading order once the rows are flattened)
const splitRows = (types, rows = ROWS) => {
    const size = Math.max(1, Math.ceil(types.length / rows));
    return Array.from({ length: Math.min(rows, Math.ceil(types.length / size)) }, (_, i) => types.slice(i * size, (i + 1) * size));
};

const typeSelectionLogic = {
    name: "TypeSelection",
    components: { TypeComp },
    computed: {
        ...mapState(["alltypes"]),
        ...mapGetters(["selectedTypesLenght"]),
        rows() {
            return splitRows(this.alltypes);
        }
    },
    methods: {
        t
    }
};

export { typeSelectionLogic, splitRows };
