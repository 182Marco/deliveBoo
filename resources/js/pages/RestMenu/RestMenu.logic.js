import { mapActions, mapState } from "vuex";
import { FoodImage, Icon, OrderSummary, PaginateEl, Plate, StarsBox, Warning } from "../../components";
import { cartTotal, formatPrice } from "../../utils";

const restMenuLogic = {
    name: "RestMenu",
    components: { FoodImage, Icon, OrderSummary, PaginateEl, Plate, StarsBox, Warning },
    computed: {
        ...mapState(["restaurant", "menuPagination", "cart", "warn"]),
        stars() {
            return Math.ceil(this.restaurant.averageRate);
        },
        amount() {
            return formatPrice(cartTotal(this.cart));
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
