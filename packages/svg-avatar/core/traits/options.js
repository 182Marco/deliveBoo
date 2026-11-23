import { CATEGORIES, variantNames } from "../../assets/catalog.js";
import { COLOR_TRAITS, colorKeys, defaultPalette } from "../../palettes/index.js";

// public list of what can be chosen: for an editor UI or a server-side whitelist
const avatarOptions = (palette = defaultPalette) => ({
    shapes: Object.fromEntries(CATEGORIES.map(category => [category, variantNames(category)])),
    colors: Object.fromEntries(Object.keys(COLOR_TRAITS).map(trait => [trait, colorKeys(palette, trait)]))
});

export { avatarOptions };
