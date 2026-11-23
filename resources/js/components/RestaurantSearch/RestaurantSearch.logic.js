import { nextTick } from "vue";
import { FoodImage } from "../FoodImage";
import { Icon } from "../Icon";
import { searchRestaurants } from "../../api";
import { debounce } from "../../utils";
import { highlightParts, isSearchable, normalizeQuery, ratingLabel, resultMeta, resultsMessage } from "./RestaurantSearch.utils.js";

// pause in typing before asking the server: short enough to feel instant, long enough
// to skip the intermediate words of a fast typist
const DEBOUNCE_MS = 280;

const isAbort = error => error?.name === "CanceledError" || error?.name === "AbortError";

const restaurantSearchLogic = {
    name: "RestaurantSearch",
    components: { FoodImage, Icon },
    data: () => ({
        open: false,
        query: "",
        // suggestions of the last answered query (kept on screen while the next one loads)
        results: [],
        // idle | loading | done | error
        status: "idle",
        answeredQuery: "",
        // index of the highlighted suggestion (keyboard or pointer), -1 = none
        active: -1
    }),
    computed: {
        showDropdown() {
            return isSearchable(this.query) && this.status !== "idle";
        },
        showSkeleton() {
            return this.status === "loading" && !this.results.length;
        },
        activeId() {
            return this.active >= 0 ? this.optionId(this.active) : null;
        },
        liveMessage() {
            return this.status === "done" ? resultsMessage(this.results.length, this.answeredQuery) : "";
        },
        countLabel() {
            return this.status === "done" && this.results.length ? `${this.results.length} found` : "";
        }
    },
    watch: {
        // any navigation (a suggestion, a header link, back) closes the search
        $route() {
            this.close(false);
        }
    },
    created() {
        // not reactive on purpose: a timer and the in-flight request are not view state
        this.request = null;
        this.debouncedSearch = debounce(query => this.search(query), DEBOUNCE_MS);
    },
    beforeUnmount() {
        this.stopPending();
    },
    methods: {
        highlight: highlightParts,
        meta: resultMeta,
        rating: ratingLabel,
        optionId: i => `restaurant-search-option-${i}`,
        async openSearch() {
            this.open = true;
            await nextTick();
            this.$refs.input?.focus();
        },
        // Esc, backdrop, close button and navigation: the typed text is forgotten
        close(restoreFocus = true) {
            if (!this.open) return;
            this.stopPending();
            Object.assign(this, { open: false, query: "", results: [], status: "idle", answeredQuery: "", active: -1 });
            if (restoreFocus) nextTick(() => this.$refs.toggle?.focus());
        },
        stopPending() {
            this.debouncedSearch.cancel();
            this.request?.abort();
            this.request = null;
        },
        onInput() {
            this.active = -1;
            if (!isSearchable(this.query)) {
                this.stopPending();
                Object.assign(this, { results: [], status: "idle", answeredQuery: "" });
                return;
            }
            this.status = "loading";
            this.debouncedSearch(normalizeQuery(this.query));
        },
        async search(query) {
            // a newer query makes the previous answer useless: drop it on the wire
            this.request?.abort();
            const request = new AbortController();
            this.request = request;
            try {
                const results = await searchRestaurants(query, request.signal);
                if (this.request !== request) return;
                Object.assign(this, { results, status: "done", answeredQuery: query, active: -1 });
            } catch (error) {
                if (isAbort(error) || this.request !== request) return;
                console.error(error);
                Object.assign(this, { results: [], status: "error" });
            } finally {
                if (this.request === request) this.request = null;
            }
        },
        move(step) {
            const count = this.results.length;
            if (!count) return;
            this.active = (this.active + step + count) % count;
            nextTick(() => document.getElementById(this.activeId)?.scrollIntoView({ block: "nearest" }));
        },
        // Enter opens the highlighted suggestion, or the first one of an up-to-date list
        chooseActive() {
            const rest = this.results[this.active] ?? (this.status === "done" ? this.results[0] : null);
            if (rest) this.choose(rest);
        },
        choose(rest) {
            this.close(false);
            this.$router.push({ name: "restMenu", params: { slug: rest.slug } });
        }
    }
};

export { restaurantSearchLogic };
