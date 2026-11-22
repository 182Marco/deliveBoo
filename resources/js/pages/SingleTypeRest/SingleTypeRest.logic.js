import { mapActions, mapMutations, mapState } from "vuex";
import { Content, FoodImage, Icon, RestByTypes } from "../../components";

// the "starred restaurants" type already says restaurants: the title must not repeat it
const STARRED_TYPE_ID = 4;

const singleTypeRestLogic = {
    name: "SingleTypeRest",
    components: { Content, FoodImage, Icon, RestByTypes },
    computed: {
        ...mapState(["alltypes", "typesSelected"]),
        currentType() {
            return this.alltypes.find(e => e.id == this.$route.params.id);
        },
        titleSuffix() {
            return this.$route.params.id == STARRED_TYPE_ID ? "s" : " restaurants";
        }
    },
    watch: {
        // the page shows only the type in the address: opened from a link, a bookmark or a reload
        // the selection may be empty or hold other types, so it is reset to this one first
        "$route.params.id": {
            handler(id) {
                const typeId = Number(id);
                if (this.typesSelected.length !== 1 || this.typesSelected[0] != typeId) {
                    this.cleanSelectedTypes();
                    this.addType(typeId);
                }
                this.getRestaurants(1);
            },
            immediate: true
        }
    },
    methods: {
        ...mapMutations(["cleanSelectedTypes", "addType"]),
        ...mapActions(["getRestaurants"])
    }
};

export { singleTypeRestLogic };
