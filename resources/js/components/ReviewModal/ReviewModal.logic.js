import { StarInput } from "../StarInput";
import { saveReview } from "../../api";
import { t } from "../../utils";
import { COMMENT_MAX, FOCUSABLE, saveError, trapTarget } from "./ReviewModal.utils.js";

// dialog of "My orders": stars and an optional text for one delivered order (new review or edit).
// Emits "saved" with the API answer ({ review, can_review }) and "close"; Esc and a click outside close it.
const reviewModalLogic = {
    name: "ReviewModal",
    components: { StarInput },
    props: {
        orderId: { type: Number, required: true },
        restaurant: { type: String, default: "" },
        review: { type: Object, default: null }
    },
    emits: ["close", "saved"],
    data() {
        return {
            rating: this.review?.rating ?? 0,
            comment: this.review?.comment ?? "",
            saving: false,
            error: "",
            max: COMMENT_MAX
        };
    },
    computed: {
        editing() {
            return Boolean(this.review);
        }
    },
    mounted() {
        this.$refs.stars.focus();
    },
    methods: {
        t,
        close() {
            if (!this.saving) this.$emit("close");
        },
        // Tab and Shift+Tab never leave the dialog
        trap(event) {
            const elements = [...this.$refs.dialog.querySelectorAll(FOCUSABLE)];
            const target = trapTarget(elements, document.activeElement, event.shiftKey);
            if (!target) return;
            event.preventDefault();
            target.focus();
        },
        async submit() {
            if (!this.rating) {
                this.error = t("reviews.chooseStars");
                this.$refs.stars.focus();
                return;
            }
            this.saving = true;
            this.error = "";
            try {
                const result = await saveReview(this.orderId, { rating: this.rating, comment: this.comment.trim() || null });
                this.$emit("saved", result);
            } catch (error) {
                this.error = saveError(error, t);
            } finally {
                this.saving = false;
            }
        }
    }
};

export { reviewModalLogic };
