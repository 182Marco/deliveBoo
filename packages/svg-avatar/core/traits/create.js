import { defaultPalette } from "../../palettes/index.js";
import { traitsFromSeed } from "./pick.js";
import { normalizeTraits, validOnly } from "./normalize.js";

// seed -> traits; valid overrides win over the seed and are kept when a rule needs a fix
const avatarTraits = (seed, overrides = {}, { palette = defaultPalette } = {}) => {
    const chosen = validOnly(overrides, palette);
    return normalizeTraits(
        { ...traitsFromSeed(seed, palette), ...chosen },
        { palette, locked: new Set(Object.keys(chosen)) }
    );
};

export { avatarTraits };
