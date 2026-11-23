// Default English labels. Hosts pass translations with the same shape (any subset).
// Labels are plain text: they are only ever written with textContent / setAttribute.
const defaultEditorLabels = Object.freeze({
    preview: "Avatar preview",
    look: "Suggestions for",
    looks: { all: "Everyone", m: "Masculine", f: "Feminine" },
    otherOptions: "Other options",
    surprise: "Surprise me",
    reset: "Reset",
    option: ":trait: :value",
    ruleChanged: ":trait changed to :value to go with :cause.",
    sections: {
        face: "Face", hair: "Hair", eyes: "Eyes", brows: "Eyebrows", nose: "Nose", mouth: "Mouth",
        clothes: "Outfit", accessory: "Accessory", background: "Background"
    },
    traits: {
        face: "Face shape", skinColor: "Skin tone", hair: "Hairstyle", hairColor: "Hair colour",
        eyes: "Eye shape", irisColor: "Eye colour", brows: "Eyebrows", nose: "Nose", mouth: "Mouth",
        clothes: "Outfit", clothColor: "Outfit colour", accessory: "Accessory", accentColor: "Accessory colour",
        bgColor: "Background"
    },
    variants: {
        face: { oval: "Oval", round: "Round", square: "Square", heart: "Heart" },
        hair: {
            buzz: "Buzz cut", crop: "Crop", sidePart: "Side part", quiff: "Quiff", curly: "Short curls", afro: "Afro",
            pixie: "Pixie", bob: "Bob", long: "Long and straight", wavy: "Long and wavy", bun: "Bun", ponytail: "Ponytail"
        },
        eyes: { open: "Open", lashes: "With lashes", happy: "Smiling", wink: "Wink", sleepy: "Sleepy" },
        brows: { soft: "Soft", bold: "Bold", arched: "Arched", flat: "Straight" },
        nose: { soft: "Soft", button: "Button", long: "Long" },
        mouth: { laugh: "Laugh", grin: "Grin", smile: "Smile", smirk: "Half smile", calm: "Calm" },
        clothes: {
            tee: "T-shirt", stripes: "Striped tee", vneck: "V-neck", hoodie: "Hoodie", shirt: "Shirt",
            blazer: "Blazer", turtleneck: "Turtleneck", strapTop: "Strap top"
        },
        accessory: {
            none: "None", roundGlasses: "Round glasses", squareGlasses: "Square glasses",
            sunglasses: "Sunglasses", earrings: "Earrings", beanie: "Beanie"
        },
        bgColor: { oat: "Oat", peach: "Peach", sage: "Sage", butter: "Butter", sky: "Sky", rose: "Rose" },
        skinColor: { porcelain: "Porcelain", light: "Light", tan: "Tan", olive: "Olive", brown: "Brown", deep: "Deep" },
        hairColor: { black: "Black", espresso: "Espresso", chestnut: "Chestnut", copper: "Copper", honey: "Honey", silver: "Silver" },
        clothColor: { forest: "Forest", olive: "Olive", saffron: "Saffron", paprika: "Paprika", denim: "Denim", stone: "Stone" },
        accentColor: { tomato: "Tomato", mustard: "Mustard", teal: "Teal", plum: "Plum" },
        irisColor: { brown: "Brown", hazel: "Hazel", green: "Green", blue: "Blue" }
    }
});

const isPlainObject = value => value !== null && typeof value === "object" && !Array.isArray(value);

// deep merge that only accepts strings where the defaults have strings
const mergeLabels = (defaults, custom) => {
    if (!isPlainObject(custom)) return defaults;
    return Object.fromEntries(Object.entries(defaults).map(([key, base]) => {
        const value = custom[key];
        if (isPlainObject(base)) return [key, mergeLabels(base, value)];
        return [key, typeof value === "string" && value !== "" ? value : base];
    }));
};

// variant labels may include values unknown to the defaults (custom data): merge those too
const mergeVariants = (defaults, custom) => {
    const merged = { ...defaults };
    if (!isPlainObject(custom)) return merged;
    Object.entries(custom).forEach(([trait, values]) => {
        if (!isPlainObject(values)) return;
        const strings = Object.entries(values).filter(([, v]) => typeof v === "string" && v !== "");
        merged[trait] = { ...(defaults[trait] ?? {}), ...Object.fromEntries(strings) };
    });
    return merged;
};

const resolveLabels = custom => ({
    ...mergeLabels(defaultEditorLabels, custom),
    variants: mergeVariants(defaultEditorLabels.variants, custom?.variants)
});

// ":name" placeholders replaced with plain text
const interpolate = (template, params = {}) =>
    String(template).replace(/:([a-zA-Z]+)/g, (match, name) => (Object.hasOwn(params, name) ? String(params[name]) : match));

// "sidePart" -> "Side part": last resort when no label exists
const humanize = key => {
    const words = String(key).replace(/([a-z0-9])([A-Z])/g, "$1 $2").toLowerCase();
    return words.charAt(0).toUpperCase() + words.slice(1);
};

const variantLabel = (labels, trait, value) => labels.variants?.[trait]?.[value] ?? humanize(value);

const traitLabel = (labels, trait) => labels.traits?.[trait] ?? humanize(trait);

export { defaultEditorLabels, resolveLabels, interpolate, humanize, variantLabel, traitLabel };
