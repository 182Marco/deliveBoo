import { mapMutations, mapState } from "vuex";
import { scrollToTop } from "../../utils";

const contentLogic = {
    name: "Content",
    computed: {
        ...mapState(["alltypes", "typesSelected"]),
        selectedTypes() {
            return this.alltypes.filter(e => this.typesSelected.includes(e.id));
        }
    },
    methods: {
        ...mapMutations(["cleanSelectedTypes", "addType"]),
        isSelected(typeId) {
            return this.typesSelected.includes(typeId);
        },
        // only this type selected, then its page
        selectSingleType(typeId) {
            this.cleanSelectedTypes();
            this.addType(typeId);
            this.$router.push({ name: "singleType", params: { id: typeId } });
            scrollToTop();
        }
    }
};

export { contentLogic };
