import { defaultPalette } from "../../palettes/index.js";
import { normalizeTraits } from "../traits/index.js";
import { composeLayers } from "./compose.js";
import { toSvgString } from "./svg.js";

// paths for any traits object (normalized first): used by the Vue component
const avatarPaths = (traits, palette = defaultPalette) => composeLayers(normalizeTraits(traits, { palette }), palette);

// traits -> <svg> string, for consumers without Vue
const renderAvatarSvg = (traits, { palette = defaultPalette, size, title, shape } = {}) =>
    toSvgString(avatarPaths(traits, palette), { size, title, shape });

export { avatarPaths, renderAvatarSvg };
