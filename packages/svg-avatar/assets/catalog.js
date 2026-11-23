import parts from "./parts.json";

// read-only helpers over parts.json; the raw structure never leaves the package
const CATEGORIES = Object.keys(parts.categories);

// categories never drawn from the seed (added later: their first variant is the default,
// so avatars created before them keep the same look)
const UNSEEDED = new Set(parts.unseededCategories ?? []);
const SEEDED_CATEGORIES = CATEGORIES.filter(category => !UNSEEDED.has(category));
const UNSEEDED_CATEGORIES = CATEGORIES.filter(category => UNSEEDED.has(category));

const variantNames = category => Object.keys(parts.categories[category] ?? {});

const variantOf = (category, name) => parts.categories[category]?.[name];

const hasVariant = (category, name) => Object.hasOwn(parts.categories[category] ?? {}, name);

const weightOf = (category, name) => variantOf(category, name)?.weight ?? 1;

// editor hint: looks ("m", "f") a variant suits; variants without the field suit every look
const looksOf = (category, name) => variantOf(category, name)?.look ?? null;

// tags of the variants a traits object selects (e.g. "coversEars", "hat")
const tagsOf = traits => new Set(CATEGORIES.flatMap(category => variantOf(category, traits[category])?.tags ?? []));

const { layers: LAYERS, base: BASE, viewBox: VIEWBOX } = parts;

export {
    CATEGORIES, SEEDED_CATEGORIES, UNSEEDED_CATEGORIES, LAYERS, BASE, VIEWBOX,
    variantNames, variantOf, hasVariant, weightOf, looksOf, tagsOf
};
