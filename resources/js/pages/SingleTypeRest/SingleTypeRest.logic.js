import { mapActions, mapState } from "vuex";
import { Content, FoodImage, Icon, RestByTypes } from "../../components";

// the "starred restaurants" type already says restaurants: the title must not repeat it
const STARRED_TYPE_ID = 4;

const singleTypeRestLogic = {
    name: "SingleTypeRest",
    components: { Content, FoodImage, Icon, RestByTypes },
    computed: {
        ...mapState(["alltypes"]),
        currentType() {
            return this.alltypes.find(e => e.id == this.$route.params.id);
        },
        titleSuffix() {
            return this.$route.params.id == STARRED_TYPE_ID ? "s" : " restaurants";
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
        ...mapActions(["getRestaurants"])
    }
};

export { singleTypeRestLogic };
