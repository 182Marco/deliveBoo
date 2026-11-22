import { mapActions, mapState } from "vuex";
import { RestByTypes } from "../../components";

// the "starred restaurants" type already says restaurants: the title must not repeat it
const STARRED_TYPE_ID = 4;
// on the home page, height of the type selection
const TYPES_SCROLL_Y = 1000;

const singleTypeRestLogic = {
    name: "SingleTypeRest",
    components: { RestByTypes },
    computed: {
        ...mapState(["alltypes"]),
        currentType() {
            return this.alltypes.find(e => e.id == this.$route.params.id);
        },
        titleSuffix() {
            return this.$route.params.id == STARRED_TYPE_ID ? "s:" : " restaurants:";
        }
    },
    watch: {
        "$route.params.id": {
            handler() {
                this.getRestaurants(1);
            },
            immediate: true
        }
    },
    methods: {
        ...mapActions(["getRestaurants"]),
        scrollToTypes: () => window.scrollTo(0, TYPES_SCROLL_Y)
    }
};

export { singleTypeRestLogic };
