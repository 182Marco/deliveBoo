// Test-only helpers for the compatibility contract (tests/fixtures/compat.json).
import { createHash } from "node:crypto";
import parts from "../assets/parts.json";
import palette from "../palettes/default.json";
import seedsFixture from "./fixtures/seeds.json";
import { avatarTraits, avatarOptions, renderAvatarSvg } from "../index.js";

// categories and draws that existed before the editor phase: they must never change
const LEGACY_CATEGORIES = ["face", "hair", "eyes", "brows", "mouth", "clothes", "accessory"];
const DRAW_PLAN = [...LEGACY_CATEGORIES, "bgColor", "skinColor", "hairColor", "clothColor", "accentColor", "irisColor"];
// fields added later for the editor only (not part of the drawing)
const EDITOR_ONLY_FIELDS = new Set(["look"]);

const canonical = value => {
    if (Array.isArray(value)) return `[${value.map(canonical).join(",")}]`;
    if (value && typeof value === "object") {
        return `{${Object.keys(value).filter(k => !EDITOR_ONLY_FIELDS.has(k)).sort()
            .map(k => `${JSON.stringify(k)}:${canonical(value[k])}`).join(",")}}`;
    }
    return JSON.stringify(value);
};

// variant order matters (weighted draw): keep it in the hash as an ordered list
const categoryHashInput = category => Object.entries(parts.categories[category]).map(([name, v]) => [name, v]);

const sha = text => createHash("sha256").update(text).digest("hex");

const { nose: _nose, ...baseWithoutNose } = parts.base;

const dataHashes = () => ({
    categories: Object.fromEntries(LEGACY_CATEGORIES.map(c => [c, sha(canonical(categoryHashInput(c)))])),
    layers: sha(canonical(parts.layers)),
    baseWithoutNose: sha(canonical(baseWithoutNose)),
    palette: sha(canonical(palette))
});

const compatSeeds = () => [
    ...seedsFixture.cases.map(c => c.seed),
    ...Array.from({ length: 500 }, (_, i) => `user-${i + 1}`)
];

// one fixed seed per override value that could be saved before the editor phase
const legacyOverrideSamples = (options = avatarOptions()) => Object.entries({ ...options.shapes, ...options.colors })
    .filter(([trait]) => trait !== "nose")
    .flatMap(([trait, values]) => values.map(value => ({ seed: `compat-${trait}`, overrides: { [trait]: value } })));

const svgHash = (seed, overrides = {}) => sha(renderAvatarSvg(avatarTraits(seed, overrides)));

export { DRAW_PLAN, LEGACY_CATEGORIES, canonical, sha, dataHashes, compatSeeds, legacyOverrideSamples, svgHash };
