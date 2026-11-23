import { BASE, CATEGORIES, LAYERS, variantOf } from "../../assets/catalog.js";
import { resolveColors } from "../../palettes/index.js";
import { hiddenLayers } from "../../rules/index.js";
import { TRAIT_NAMES } from "../traits/index.js";

const MAX_CACHED = 500;
const cacheByPalette = new WeakMap();

const round = n => Math.round(n * 100) / 100;

// circles and ellipses become paths, so every layer is a single <path>
const ellipsePath = (cx, cy, rx, ry) =>
    `M${round(cx - rx)} ${cy}a${rx} ${ry} 0 1 0 ${round(rx * 2)} 0a${rx} ${ry} 0 1 0 ${round(-rx * 2)} 0Z`;

const pathOf = shape => {
    if (shape.d) return shape.d;
    if (shape.c) return ellipsePath(shape.c[0], shape.c[1], shape.c[2], shape.c[2]);
    return ellipsePath(...shape.e);
};

const attrsOf = (shape, colors) => ({
    d: pathOf(shape),
    fill: shape.f ? colors[shape.f] : "none",
    ...(shape.s ? {
        stroke: colors[shape.s],
        "stroke-width": shape.w,
        "stroke-linecap": "round",
        "stroke-linejoin": "round"
    } : {}),
    ...(shape.o === undefined ? {} : { opacity: shape.o })
});

const shapesOf = (layer, traits) => [
    ...(BASE[layer] ?? []),
    ...CATEGORIES.flatMap(category => variantOf(category, traits[category])?.[layer] ?? [])
];

const build = (traits, palette) => {
    const colors = resolveColors(traits, palette);
    const hidden = hiddenLayers(traits);
    return Object.freeze(LAYERS
        .filter(layer => !hidden.has(layer))
        .flatMap(layer => shapesOf(layer, traits))
        .map(shape => Object.freeze(attrsOf(shape, colors))));
};

// normalized traits -> ordered list of <path> attributes; memoized per palette object
const composeLayers = (traits, palette) => {
    if (!cacheByPalette.has(palette)) cacheByPalette.set(palette, new Map());
    const cache = cacheByPalette.get(palette);
    const key = TRAIT_NAMES.map(trait => traits[trait]).join("|");
    if (!cache.has(key)) {
        if (cache.size >= MAX_CACHED) cache.delete(cache.keys().next().value);
        cache.set(key, build(traits, palette));
    }
    return cache.get(key);
};

export { composeLayers };
