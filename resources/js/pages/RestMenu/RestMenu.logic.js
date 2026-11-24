import { mapActions, mapState } from "vuex";
import { FoodImage, Icon, OrderSummary, PaginateEl, Plate, RestReviews, StarsBox, Warning } from "../../components";
import { cartTotal, formatPrice, t } from "../../utils";

const restMenuLogic = {
    name: "RestMenu",
    components: { FoodImage, Icon, OrderSummary, PaginateEl, Plate, RestReviews, StarsBox, Warning },
    computed: {
        ...mapState(["restaurant", "menuPagination", "cart", "warn"]),
        // stored average (arithmetic mean) and number of counted reviews; null average = no reviews yet
        rating() {
            return this.restaurant.rating ?? { average: null, count: 0 };
        },
        amount() {
            return formatPrice(cartTotal(this.cart));
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
