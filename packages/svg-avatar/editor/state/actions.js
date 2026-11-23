import { looksOf, variantNames } from "../../assets/catalog.js";
import { defaultPalette } from "../../palettes/index.js";
import { avatarTraits, normalizeTraits, traitsFromSeed } from "../../core/traits/index.js";
import { seededRandom } from "../../core/random/index.js";

// Pure reducer of the editor. Randomness enters only through action payloads (the seed of "surprise").

const sameTraits = (a, b) => Object.keys(a).every(key => a[key] === b[key]);

// current value -> resolved traits; overrides kept only where they still hold after the rules.
// seedTraits are the raw draws (before the rules), the same base avatarTraits starts from:
// that keeps avatarTraits(seed, minimal overrides) equal to the traits shown in the editor.
const resolve = (palette, seed, overrides, locked = new Set(Object.keys(overrides))) => {
    const seedTraits = traitsFromSeed(seed, palette);
    const traits = normalizeTraits({ ...seedTraits, ...overrides }, { palette, locked });
    const kept = Object.fromEntries(Object.entries(overrides).filter(([trait, value]) => traits[trait] === value));
    return { seedTraits, seedAvatar: avatarTraits(seed, {}, { palette }), traits, overrides: kept };
};

const isPlainObject = value => value !== null && typeof value === "object" && !Array.isArray(value);

// { seed, overrides } from the host: unknown traits or values are ignored, as avatarTraits does
const fromValue = (palette, value) => {
    const seed = typeof value?.seed === "string" ? value.seed : "";
    const raw = isPlainObject(value?.overrides) ? value.overrides : {};
    const valid = Object.fromEntries(Object.entries(raw).filter(([trait, chosen]) =>
        typeof chosen === "string" && avatarTraits(seed, { [trait]: chosen }, { palette })[trait] === chosen));
    return { seed, ...resolve(palette, seed, valid) };
};

const initialState = ({ value, palette = defaultPalette } = {}) => {
    const current = fromValue(palette, value);
    return {
        palette,
        initial: { seed: current.seed, overrides: current.overrides, traits: current.traits },
        seed: current.seed,
        seedTraits: current.seedTraits,
        seedAvatar: current.seedAvatar,
        overrides: current.overrides,
        traits: current.traits,
        look: "all",
        changes: []
    };
};

const withValue = (state, seed, overrides, locked) => {
    const next = resolve(state.palette, seed, overrides, locked);
    if (seed === state.seed && sameTraits(next.traits, state.traits)) return state;
    const changes = Object.keys(next.traits)
        .filter(trait => next.traits[trait] !== state.traits[trait])
        .map(trait => ({ trait, from: state.traits[trait], to: next.traits[trait] }));
    return { ...state, seed, seedTraits: next.seedTraits, seedAvatar: next.seedAvatar, overrides: next.overrides, traits: next.traits, changes };
};

const choose = (state, { trait, value }) => {
    if (!Object.hasOwn(state.traits, trait) || typeof value !== "string") return state;
    const candidate = avatarTraits(state.seed, { [trait]: value }, { palette: state.palette });
    if (candidate[trait] !== value) return state; // unknown value
    // the latest choice wins: only it is locked, older overrides may give way to the rules
    return withValue(state, state.seed, { ...state.overrides, [trait]: value }, new Set([trait]));
};

const pick = (rand, list) => list[Math.floor(rand() * list.length)];

// new seed; with a look, hairstyle and outfit come from that look; a random nose in any case
const surprise = (state, { seed }) => {
    if (typeof seed !== "string" || seed === "") return state;
    const rand = seededRandom(`${seed}:surprise`);
    const suits = category => variantNames(category).filter(name => (looksOf(category, name) ?? []).includes(state.look));
    const overrides = { nose: pick(rand, variantNames("nose")) };
    if (state.look !== "all") {
        overrides.hair = pick(rand, suits("hair"));
        overrides.clothes = pick(rand, suits("clothes"));
    }
    return withValue(state, seed, overrides);
};

const reset = state => withValue(state, state.initial.seed, state.initial.overrides);

const setValue = (state, { value }) => {
    const next = fromValue(state.palette, value);
    return withValue(state, next.seed, next.overrides);
};

const setLook = (state, { look }) => (["all", "m", "f"].includes(look) && look !== state.look ? { ...state, look, changes: [] } : state);

const HANDLERS = { choose, surprise, reset, setValue, setLook };

const editorReducer = (state, action) => HANDLERS[action?.type]?.(state, action) ?? state;

export { initialState, editorReducer };
