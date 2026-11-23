// colour traits and the palette group each one picks from
const COLOR_TRAITS = {
    bgColor: "bg",
    skinColor: "skin",
    hairColor: "hair",
    clothColor: "cloth",
    accentColor: "accent",
    irisColor: "iris"
};

const colorKeys = (palette, trait) => Object.keys(palette[COLOR_TRAITS[trait]] ?? {});

// unknown keys fall back to the first colour of the group
const colorOf = (palette, trait, key) => {
    const group = palette[COLOR_TRAITS[trait]] ?? {};
    return group[key] ?? group[colorKeys(palette, trait)[0]];
};

const pair = value => (Array.isArray(value) ? value : [value, value]);

// slot name -> CSS colour, for one traits object
const resolveColors = (traits, palette) => {
    const [skin, skinShade] = pair(colorOf(palette, "skinColor", traits.skinColor));
    const [hair, hairShade] = pair(colorOf(palette, "hairColor", traits.hairColor));
    const [cloth, clothShade] = pair(colorOf(palette, "clothColor", traits.clothColor));
    const [accent, accentShade] = pair(colorOf(palette, "accentColor", traits.accentColor));
    return {
        ...palette.fixed,
        bg: colorOf(palette, "bgColor", traits.bgColor),
        skin, skinShade, hair, hairShade, cloth, clothShade, accent, accentShade,
        iris: colorOf(palette, "irisColor", traits.irisColor)
    };
};

export { COLOR_TRAITS, colorKeys, resolveColors };
