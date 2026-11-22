import { mapMutations, mapState } from "vuex";
import { FoodImage } from "../FoodImage";
import { scrollToTop } from "../../utils";

// rail of every cuisine: one click opens the page of that cuisine only
const contentLogic = {
    name: "Content",
    components: { FoodImage },
    props: {
        eyebrow: { type: String, default: "Cuisines" },
        title: { type: String, default: "What are you craving?" }
    },
    computed: {
        ...mapState(["alltypes", "typesSelected"])
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
