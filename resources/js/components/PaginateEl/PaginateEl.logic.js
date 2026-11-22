import { mapActions } from "vuex";
import { Icon } from "../Icon";

// one component, two paginations:
// getMenu -> menu of the restaurant `slug`, otherwise restaurants of the selected types
const paginateElLogic = {
    name: "PaginateEl",
    components: { Icon },
    props: {
        getMenu: Boolean,
        paginate: Object,
        slug: String
    },
    computed: {
        isFirst() {
            return this.paginate.current == 1;
        },
        isLast() {
            return this.paginate.current == this.paginate.last;
        }
    },
    methods: {
        ...mapActions(["getMenuAndDetails", "getRestaurants"]),
        goTo(page) {
            return this.getMenu
                ? this.getMenuAndDetails({ page, slug: this.slug })
                : this.getRestaurants(page);
        }
    }
};

export { paginateElLogic };
