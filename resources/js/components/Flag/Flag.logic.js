import { flagHeight, flagShapes } from "./Flag.utils.js";

// flag of a language (3:2), decorative: the language name is always written next to it
const flagLogic = {
    name: "Flag",
    props: {
        code: { type: String, required: true },
        size: { type: Number, default: 20 }
    },
    computed: {
        shapes() {
            return flagShapes(this.code);
        },
        height() {
            return flagHeight(this.size);
        }
    }
};

export { flagLogic };
