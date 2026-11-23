import { CATEGORIES, looksOf, variantNames } from "../assets/catalog.js";
import { COLOR_TRAITS } from "../palettes/index.js";

// editor layout as data: sections in display order, each with the traits it edits
const SECTIONS = Object.freeze([
    { id: "face", traits: ["face", "skinColor"] },
    { id: "hair", traits: ["hair", "hairColor"] },
    { id: "eyes", traits: ["eyes", "irisColor"] },
    { id: "brows", traits: ["brows"] },
    { id: "nose", traits: ["nose"] },
    { id: "mouth", traits: ["mouth"] },
    { id: "clothes", traits: ["clothes", "clothColor"] },
    { id: "accessory", traits: ["accessory", "accentColor"] },
    { id: "background", traits: ["bgColor"] }
]);

const LOOKS = Object.freeze(["all", "m", "f"]);

// thumbnails of small facial features zoom on the face (viewBox x y w h, avatar space 0–120)
const THUMB_VIEWBOX = Object.freeze({
    face: "24 14 72 72",
    eyes: "36 32 48 48",
    brows: "36 32 48 48",
    nose: "36 32 48 48",
    mouth: "36 32 48 48"
});

// "shape" traits draw parts, "color" traits pick a palette key
const kindOf = trait => (Object.hasOwn(COLOR_TRAITS, trait) ? "color" : "shape");

// traits whose variants carry look hints (hair, clothes, accessories)
const LOOK_TRAITS = Object.freeze(CATEGORIES.filter(category => variantNames(category).some(name => looksOf(category, name))));

export { SECTIONS, LOOKS, LOOK_TRAITS, THUMB_VIEWBOX, kindOf };
