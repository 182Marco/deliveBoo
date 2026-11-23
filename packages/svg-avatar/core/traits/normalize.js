import { CATEGORIES, hasVariant, variantNames } from "../../assets/catalog.js";
import { COLOR_TRAITS, colorKeys, defaultPalette } from "../../palettes/index.js";
import { applyRules } from "../../rules/index.js";

const isValid = (trait, value, palette) => (CATEGORIES.includes(trait)
    ? hasVariant(trait, value)
    : colorKeys(palette, trait).includes(value));

const fallback = (trait, palette) => (CATEGORIES.includes(trait) ? variantNames(trait)[0] : colorKeys(palette, trait)[0]);

const TRAIT_NAMES = [...CATEGORIES, ...Object.keys(COLOR_TRAITS)];

// keep only known trait names with valid values
const validOnly = (traits = {}, palette = defaultPalette) => Object.fromEntries(
    TRAIT_NAMES.filter(trait => isValid(trait, traits?.[trait], palette)).map(trait => [trait, traits[trait]])
);

// complete, valid traits: unknown values fall back to the first option, then the compatibility rules apply
const normalizeTraits = (traits = {}, { palette = defaultPalette, locked = new Set() } = {}) => {
    const complete = Object.fromEntries(TRAIT_NAMES.map(trait => [
        trait,
        isValid(trait, traits?.[trait], palette) ? traits[trait] : fallback(trait, palette)
    ]));
    return applyRules(complete, locked);
};

export { TRAIT_NAMES, validOnly, normalizeTraits };
