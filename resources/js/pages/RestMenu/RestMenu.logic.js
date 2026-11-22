import { mapActions, mapState } from "vuex";
import { PaginateEl, Plate, StarsBox } from "../../components";

const restMenuLogic = {
    name: "RestMenu",
    components: { Plate, PaginateEl, StarsBox },
    computed: {
        ...mapState(["restaurant", "menuPagination"]),
        stars() {
            return Math.ceil(this.restaurant.averageRate);
        }
    },
    created() {
        this.getMenuAndDetails({ page: 1, slug: this.$route.params.slug });
    },
    methods: {
        ...mapActions(["getMenuAndDetails"])
    }
};

export { restMenuLogic };
