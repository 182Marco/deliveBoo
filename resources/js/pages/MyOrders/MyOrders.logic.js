import { mapState } from "vuex";
import { FoodImage, Icon, PaginateEl } from "../../components";
import { fetchMyOrders } from "../../api";
import { currentLocale, formatPrice, scrollToTop, t, toPagination } from "../../utils";
import { toOrderView } from "./MyOrders.utils.js";

// "My orders" (dev/PIANO_AUTH_CLIENTI.md, step 7.2): the orders of the logged-in account.
// The server decides who is logged in (session cookie): a 401 shows the login prompt,
// so the page works even before /api/me has filled the store.
const myOrdersLogic = {
    name: "MyOrders",
    components: { FoodImage, Icon, PaginateEl },
    data: () => ({
        // "loading" | "ready" | "guest" | "error"
        status: "loading",
        orders: [],
        pagination: { current: 1, last: 1 }
    }),
    computed: {
        ...mapState(["user"]),
        // recomputed when the language changes (currentLocale reads a reactive ref)
        rows() {
            const locale = currentLocale();
            return this.orders.map(order => toOrderView(order, locale));
        }
    },
    watch: {
        // logged in from another tab, or a different account: load again
        user(user, previous) {
            const changed = (user?.id ?? null) !== (previous?.id ?? null);
            if (changed && (previous || this.status === "guest")) this.load(1);
        }
    },
    mounted() {
        this.load(1);
    },
    methods: {
        t,
        formatPrice,
        async load(page) {
            this.status = "loading";
            try {
                const response = await fetchMyOrders(page);
                this.orders = response.data;
                this.pagination = toPagination(response);
                this.status = "ready";
            } catch (error) {
                this.orders = [];
                if (error?.response?.status === 401) {
                    this.status = "guest";
                    return;
                }
                console.error(error);
                this.status = "error";
            }
        },
        async goTo(page) {
            await this.load(page);
            scrollToTop();
        }
    }
};

export { myOrdersLogic };
