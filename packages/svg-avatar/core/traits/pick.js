import { SEEDED_CATEGORIES, UNSEEDED_CATEGORIES, variantNames, weightOf } from "../../assets/catalog.js";
import { COLOR_TRAITS, colorKeys } from "../../palettes/index.js";
import { seededRandom } from "../random/index.js";

// weighted choice: variants default to weight 1
const pickWeighted = (rand, category) => {
    const names = variantNames(category);
    const total = names.reduce((sum, name) => sum + weightOf(category, name), 0);
    let target = rand() * total;
    return names.find(name => (target -= weightOf(category, name)) < 0) ?? names[names.length - 1];
};

const pickUniform = (rand, keys) => keys[Math.floor(rand() * keys.length)];

// one draw per seeded category, then one per colour trait, always in this order (part of the contract);
// unseeded categories take their first variant without drawing, so older seeds keep their avatar
const traitsFromSeed = (seed, palette) => {
    const rand = seededRandom(seed);
    const shapes = Object.fromEntries(SEEDED_CATEGORIES.map(category => [category, pickWeighted(rand, category)]));
    const colors = Object.fromEntries(Object.keys(COLOR_TRAITS).map(trait => [trait, pickUniform(rand, colorKeys(palette, trait))]));
    const defaults = Object.fromEntries(UNSEEDED_CATEGORIES.map(category => [category, variantNames(category)[0]]));
    return { ...shapes, ...colors, ...defaults };
};

export { traitsFromSeed };
