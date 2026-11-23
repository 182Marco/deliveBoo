import { mapMutations, mapState } from "vuex";
import { FoodImage } from "../FoodImage";
import { scrollToTop, t } from "../../utils";

// rail of every cuisine: one click opens the page of that cuisine only
const contentLogic = {
    name: "Content",
    components: { FoodImage },
    props: {
        // empty: the texts of the home page
        eyebrow: { type: String, default: "" },
        title: { type: String, default: "" }
    },
    computed: {
        ...mapState(["alltypes", "typesSelected"]),
        eyebrowText() {
            return this.eyebrow || t("cuisines.eyebrow");
        },
        titleText() {
            return this.title || t("cuisines.title");
        }
    },
    methods: {
        ...mapMutations(["cleanSelectedTypes", "addType"]),
        t,
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
