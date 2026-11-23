import { looksOf, variantNames } from "../../assets/catalog.js";
import { colorKeys } from "../../palettes/index.js";
import { TRAIT_NAMES, avatarTraits, normalizeTraits } from "../../core/traits/index.js";
import { kindOf, LOOK_TRAITS } from "../groups.js";

const traitsOf = state => state.traits;

const differences = (traits, base) => Object.fromEntries(TRAIT_NAMES
    .filter(trait => traits[trait] !== base[trait])
    .map(trait => [trait, traits[trait]]));

const sameTraits = (a, b) => TRAIT_NAMES.every(trait => a[trait] === b[trait]);

// minimal overrides, in TRAIT_NAMES order: the differences from the avatar the seed alone gives;
// when the rules make that ambiguous (a seed draw they had removed would come back), the differences
// from the raw draws, which always reproduce the shown traits (avatarTraits starts from them)
const valueOf = state => {
    const fromAvatar = differences(state.traits, state.seedAvatar);
    const reproduces = sameTraits(avatarTraits(state.seed, fromAvatar, { palette: state.palette }), state.traits);
    return { seed: state.seed, overrides: reproduces ? fromAvatar : differences(state.traits, state.seedTraits) };
};

const isDirty = state => TRAIT_NAMES.some(trait => state.traits[trait] !== state.initial.traits[trait]);

const valuesOf = (state, trait) => (kindOf(trait) === "color" ? colorKeys(state.palette, trait) : variantNames(trait));

// options of a trait; with a look, the suggested ones come first (nothing is hidden)
const optionsFor = (state, trait) => {
    const values = valuesOf(state, trait);
    const ranked = state.look === "all" || !LOOK_TRAITS.includes(trait)
        ? values.map(value => ({ value, suggested: true }))
        : [
            ...values.filter(value => (looksOf(trait, value) ?? []).includes(state.look)).map(value => ({ value, suggested: true })),
            ...values.filter(value => !(looksOf(trait, value) ?? []).includes(state.look)).map(value => ({ value, suggested: false }))
        ];
    return ranked.map(option => ({ ...option, selected: state.traits[trait] === option.value }));
};

// what the avatar would look like with one option chosen (thumbnails)
const previewTraits = (state, trait, value) =>
    normalizeTraits({ ...state.traits, [trait]: value }, { palette: state.palette, locked: new Set([trait]) });

// traits moved by the rules in the last change, other than the one the user chose
const sideChanges = (state, chosenTrait) => state.changes.filter(change => change.trait !== chosenTrait);

export { traitsOf, valueOf, isDirty, optionsFor, previewTraits, sideChanges };
