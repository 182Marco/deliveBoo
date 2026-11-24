import { Icon } from "../Icon";
import { formatRating, t } from "../../utils";
import { starStates } from "./StarsBox.utils.js";

// read-only stars of an average (0-5, decimals shown as half stars); with `count` also "4.6 · 123 reviews"
const starsBoxLogic = {
    name: "StarsBox",
    components: { Icon },
    props: {
        average: { type: Number, default: 0 },
        size: { type: Number, default: 16 },
        count: { type: Number, default: null }
    },
    computed: {
        states() {
            return starStates(this.average);
        },
        label() {
            return formatRating(this.average);
        }
    },
    methods: {
        t
    }
};

export { starsBoxLogic };
