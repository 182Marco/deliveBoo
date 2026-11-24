import { Icon } from "../Icon";
import { t } from "../../utils";
import { MAX_STARS, nextValue, tabIndexFor } from "./StarInput.utils.js";

// v-model 0-5: the stars fill up to the one under the pointer (preview), then to the chosen one.
// Accessible radio group: one Tab stop, arrows / Home / End, every star says "4 stars, very good".
const starInputLogic = {
    name: "StarInput",
    components: { Icon },
    props: {
        modelValue: { type: Number, default: 0 },
        size: { type: Number, default: 36 },
        labelledby: { type: String, default: null },
        describedby: { type: String, default: null }
    },
    emits: ["update:modelValue"],
    data: () => ({
        max: MAX_STARS,
        preview: 0
    }),
    computed: {
        // what the stars show: the star under the pointer, otherwise the chosen one
        shown() {
            return this.preview || this.modelValue;
        },
        level() {
            return this.shown ? t(`reviews.level.${this.shown}`) : "";
        }
    },
    methods: {
        t,
        tabIndex(star) {
            return tabIndexFor(star, this.modelValue);
        },
        label: star => `${t("reviews.star", { count: star })}, ${t(`reviews.level.${star}`)}`,
        choose(star) {
            this.$emit("update:modelValue", star);
        },
        onKey(event) {
            const next = nextValue(event.key, this.modelValue);
            if (next === null) return;
            event.preventDefault();
            this.choose(next);
            this.$refs.stars[next - 1]?.focus();
        },
        // the first star of the group, for the dialog that opens on it
        focus() {
            this.$refs.stars[(this.modelValue || 1) - 1]?.focus();
        }
    }
};

export { starInputLogic };
