import { mapActions, mapGetters, mapState } from "vuex";
import { FoodImage, Icon, OrderSummary, PaginateEl, Plate, RestReviews, StarsBox, Warning } from "../../components";
import { formatPrice, t } from "../../utils";

const restMenuLogic = {
    name: "RestMenu",
    components: { FoodImage, Icon, OrderSummary, PaginateEl, Plate, RestReviews, StarsBox, Warning },
    computed: {
        ...mapState(["restaurant", "menuPagination", "warn"]),
        ...mapGetters(["cartCount", "cartAmount"]),
        // stored average (arithmetic mean) and number of counted reviews; null average = no reviews yet
        rating() {
            return this.restaurant.rating ?? { average: null, count: 0 };
        },
        amount() {
            return formatPrice(this.cartAmount);
        }
    },
    created() {
        this.getMenuAndDetails({ page: 1, slug: this.$route.params.slug });
    },
    watch: {
        // the page is reused when only the slug changes (e.g. a restaurant picked from the header search)
        "$route.params.slug"(slug) {
            if (slug) this.getMenuAndDetails({ page: 1, slug });
        }
    },
    methods: {
        ...mapActions(["getMenuAndDetails"]),
        t
    }
};

export { restMenuLogic };
