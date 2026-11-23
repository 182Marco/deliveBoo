// Default palette: warm, soft tones, kept as JSON so non-JS consumers (e.g. a server whitelist) can read it.
// Consumers may extend or replace it without touching the assets (spread it, never mutate it:
// it is frozen because rendered paths are cached per palette object).
// Paired groups are [base, shade]; the slot names they feed are listed in assets/parts.json ("slots").
import palette from "./default.json";

const deepFreeze = value => {
    if (value && typeof value === "object") Object.values(value).forEach(deepFreeze);
    return Object.freeze(value);
};

const defaultPalette = deepFreeze(palette);

export { defaultPalette };
