import { mapState } from "vuex";
import { FoodImage, Icon, PaginateEl, ReviewModal, StarsBox } from "../../components";
import { fetchMyOrders } from "../../api";
import { currentLocale, formatPrice, scrollToTop, t, toPagination } from "../../utils";
import { REVIEW_WINDOW_DAYS, toOrderView, withReview } from "./MyOrders.utils.js";

// how long the "review published" notice stays
const NOTICE_MS = 4000;

// "My orders" (dev/PIANO_AUTH_CLIENTI.md, step 7.2): the orders of the logged-in account.
// The server decides who is logged in (session cookie): a 401 shows the login prompt,
// so the page works even before /api/me has filled the store.
const myOrdersLogic = {
    name: "MyOrders",
    components: { FoodImage, Icon, PaginateEl, ReviewModal, StarsBox },
    data: () => ({
        // "loading" | "ready" | "guest" | "error"
        status: "loading",
        orders: [],
        pagination: { current: 1, last: 1 },
        // the order whose review dialog is open (null = closed) and the button that opened it
        reviewing: null,
        opener: null,
        notice: "",
        noticeTimer: null,
        reviewDays: REVIEW_WINDOW_DAYS
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
    beforeUnmount() {
        clearTimeout(this.noticeTimer);
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
        openReview(order, event) {
            this.opener = event?.currentTarget ?? null;
            this.reviewing = order;
        },
        // the focus goes back to the button that opened the dialog
        closeReview() {
            this.reviewing = null;
            this.$nextTick(() => this.opener?.focus());
        },
        // the saved review replaces the old one in the list, without loading the page again
        reviewSaved(result) {
            const id = this.reviewing.id;
            this.orders = this.orders.map(e => (e.id === id ? withReview(e, result) : e));
            this.closeReview();
            this.notice = t("reviews.saved");
            clearTimeout(this.noticeTimer);
            this.noticeTimer = setTimeout(() => (this.notice = ""), NOTICE_MS);
        },
        async goTo(page) {
            await this.load(page);
            scrollToTop();
        }
    }
};

export { myOrdersLogic };
