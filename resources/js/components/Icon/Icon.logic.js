import { iconPath } from "./Icon.utils.js";

// decorative by default: give the parent control an aria-label, or pass `label` for a standalone icon
const iconLogic = {
    name: "Icon",
    props: {
        name: { type: String, required: true },
        size: { type: [Number, String], default: 20 },
        filled: Boolean,
        label: { type: String, default: "" }
    },
    computed: {
        d() {
            return iconPath(this.name);
        }
    }
};

export { iconLogic };
