import { mapActions } from "vuex";
import { Icon } from "../Icon";
import { pageWindow, t } from "../../utils";

// one component, three paginations:
// getMenu -> menu of the restaurant `slug`; local -> emits "go" and the parent loads the page itself
// (e.g. My orders); otherwise restaurants of the selected types
const paginateElLogic = {
    name: "PaginateEl",
    components: { Icon },
    props: {
        getMenu: Boolean,
        local: Boolean,
        paginate: Object,
        slug: String
    },
    emits: ["go"],
    computed: {
        pages() {
            return pageWindow(Number(this.paginate.current), Number(this.paginate.last));
        },
        isFirst() {
            return this.paginate.current == 1;
        },
        isLast() {
            return this.paginate.current == this.paginate.last;
        }
    },
    methods: {
        ...mapActions(["getMenuAndDetails", "getRestaurants"]),
        t,
        goTo(page) {
            if (this.local) return this.$emit("go", page);
            return this.getMenu
                ? this.getMenuAndDetails({ page, slug: this.slug })
                : this.getRestaurants(page);
        }
    }
};

export { paginateElLogic };
