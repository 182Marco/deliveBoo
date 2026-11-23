import { VIEWBOX } from "../../assets/catalog.js";
import { defaultPalette } from "../../palettes/index.js";
import { avatarTraits, normalizeTraits } from "../../core/traits/index.js";
import { composeLayers } from "../../core/layers/index.js";

// Renders the same paths as renderAvatarSvg; computed values are cached by Vue and by composeLayers.
// Decorative by default: pass `label` to give it an accessible name.
const avatarLogic = {
    name: "Avatar",
    props: {
        seed: { type: String, default: "" },
        overrides: { type: Object, default: null },
        traits: { type: Object, default: null },
        palette: { type: Object, default: null },
        size: { type: [Number, String], default: 40 },
        label: { type: String, default: "" },
        shape: { type: String, default: "circle", validator: value => ["circle", "square"].includes(value) }
    },
    computed: {
        activePalette() {
            return this.palette ?? defaultPalette;
        },
        resolvedTraits() {
            const palette = this.activePalette;
            return this.traits
                ? normalizeTraits(this.traits, { palette })
                : avatarTraits(this.seed, this.overrides ?? {}, { palette });
        },
        paths() {
            return composeLayers(this.resolvedTraits, this.activePalette);
        },
        viewBox() {
            return `0 0 ${VIEWBOX} ${VIEWBOX}`;
        }
    }
};

export { avatarLogic };
