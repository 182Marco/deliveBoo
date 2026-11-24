import { Avatar } from "svg-avatar/vue";
import { Icon } from "../Icon";
import { PaginateEl } from "../PaginateEl";
import { StarsBox } from "../StarsBox";
import { fetchRestaurantReviews } from "../../api";
import { currentLocale, formatRating, t, toPagination } from "../../utils";
import { distributionRows, formatDay } from "./RestReviews.utils.js";

// "What guests say" under the menu: summary of the counted reviews and the reviews with a text,
// 4 per page, with the reply of the restaurant. Loads by itself from the slug of the restaurant.
const restReviewsLogic = {
    name: "RestReviews",
    components: { Avatar, Icon, PaginateEl, StarsBox },
    props: {
        slug: { type: String, required: true },
        restaurant: { type: String, default: "" }
    },
    data: () => ({
        // "loading" | "ready" | "error"
        status: "loading",
        summary: { average: null, count: 0, distribution: {} },
        reviews: [],
        pagination: { current: 1, last: 1 }
    }),
    computed: {
        rows() {
            return distributionRows(this.summary.distribution);
        },
        average() {
            return formatRating(this.summary.average);
        },
        // recomputed when the language changes (currentLocale reads a reactive ref)
        items() {
            const locale = currentLocale();
            return this.reviews.map(e => ({ ...e, day: formatDay(e.created_at, locale) }));
        }
    },
    watch: {
        slug() {
            this.load(1);
        }
    },
    mounted() {
        this.load(1);
    },
    methods: {
        t,
        async load(page) {
            this.status = "loading";
            try {
                const response = await fetchRestaurantReviews(this.slug, page);
                this.summary = response.summary;
                this.reviews = response.reviews.data;
                this.pagination = toPagination(response.reviews);
                this.status = "ready";
            } catch (error) {
                console.error(error);
                this.status = "error";
            }
        },
        async goTo(page) {
            await this.load(page);
            this.$refs.list?.scrollIntoView({ behavior: "smooth", block: "start" });
        }
    }
};

export { restReviewsLogic };
