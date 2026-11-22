import { mapActions, mapMutations, mapState } from "vuex";
import { Icon } from "../Icon";

const typeCompLogic = {
    name: "TypeComp",
    components: { Icon },
    props: {
        type: Object
    },
    computed: {
        ...mapState(["typesSelected"]),
        isSelected() {
            return this.typesSelected.includes(this.type.id);
        }
    },
    methods: {
        ...mapMutations(["addType", "pullType"]),
        ...mapActions(["getRestaurants"]),
        // (de)select the type and reload the restaurants from the first page
        toggle() {
            if (this.isSelected) this.pullType(this.type.id);
            else this.addType(this.type.id);
            this.getRestaurants(1);
        }
    }
};

export { typeCompLogic };
