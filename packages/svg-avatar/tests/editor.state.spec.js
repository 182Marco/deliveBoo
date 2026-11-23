import { describe, expect, it } from "vitest";
import { createStore, editorReducer, initialState, isDirty, optionsFor, previewTraits, sideChanges, traitsOf, valueOf } from "../editor/state/index.js";
import { SECTIONS, LOOK_TRAITS } from "../editor/groups.js";
import { TRAIT_NAMES } from "../core/traits/index.js";
import { mulberry32 } from "../core/random/index.js";
import { avatarTraits } from "../index.js";
import { optionsContract } from "./data.helpers.js";

const start = value => createStore(editorReducer, initialState({ value }));

describe("editor groups", () => {
    it("every trait is edited exactly once", () => {
        expect(SECTIONS.flatMap(section => section.traits).sort()).toEqual([...TRAIT_NAMES].sort());
        expect(LOOK_TRAITS).toEqual(["hair", "clothes", "accessory"]);
    });
});

describe("editor state", () => {
    it("starts from the given value and is not dirty", () => {
        const store = start({ seed: "user-1", overrides: { hair: "bun" } });
        expect(traitsOf(store.getState())).toEqual(avatarTraits("user-1", { hair: "bun" }));
        expect(isDirty(store.getState())).toBe(false);
    });

    it("ignores invalid initial values like avatarTraits does", () => {
        const store = start({ seed: 42, overrides: { hair: "mohawk", nose: "button", other: "x" } });
        expect(valueOf(store.getState())).toEqual({ seed: "", overrides: { nose: "button" } });
        expect(start(null).getState().seed).toBe("");
        expect(start({ seed: "a", overrides: ["hair"] }).getState().overrides).toEqual({});
    });

    it("choose: the latest choice wins and the rules move the other trait", () => {
        const store = start({ seed: "user-1", overrides: { hair: "afro" } });
        store.dispatch({ type: "choose", trait: "accessory", value: "beanie" });
        const state = store.getState();
        expect(state.traits.accessory).toBe("beanie");
        expect(state.traits.hair).not.toBe("afro");
        expect(state.overrides.hair).toBeUndefined();
        expect(sideChanges(state, "accessory").map(change => change.trait)).toEqual(["hair"]);
        expect(isDirty(state)).toBe(true);
    });

    it("choose ignores unknown traits, unknown values and no-op choices (same state object)", () => {
        const store = start({ seed: "user-1" });
        const before = store.getState();
        store.dispatch({ type: "choose", trait: "wings", value: "big" });
        store.dispatch({ type: "choose", trait: "hair", value: "mohawk" });
        store.dispatch({ type: "choose", trait: "hair", value: before.traits.hair });
        expect(store.getState()).toBe(before);
    });

    it("the look reorders without filtering and never changes the value", () => {
        const store = start({ seed: "user-1" });
        const all = optionsFor(store.getState(), "hair").map(option => option.value);
        store.dispatch({ type: "setLook", look: "f" });
        const feminine = optionsFor(store.getState(), "hair");
        expect(feminine.map(option => option.value).sort()).toEqual([...all].sort());
        expect(feminine[0].suggested).toBe(true);
        expect(feminine.findIndex(option => !option.suggested)).toBeGreaterThan(0);
        expect(feminine.slice(feminine.findIndex(option => !option.suggested)).every(option => !option.suggested)).toBe(true);
        expect(valueOf(store.getState())).toEqual({ seed: "user-1", overrides: {} });
        expect(optionsFor(store.getState(), "eyes").every(option => option.suggested)).toBe(true);
    });

    it("surprise: new seed, overrides reset; with a look, hairstyle and outfit suit it", () => {
        const store = start({ seed: "user-1", overrides: { mouth: "grin" } });
        store.dispatch({ type: "setLook", look: "m" });
        for (let i = 0; i < 30; i++) {
            store.dispatch({ type: "surprise", seed: `s-${i}` });
            const state = store.getState();
            expect(state.seed).toBe(`s-${i}`);
            expect(optionsFor(state, "hair").find(option => option.selected).suggested).toBe(true);
            expect(optionsFor(state, "clothes").find(option => option.selected).suggested).toBe(true);
        }
        expect(store.dispatch({ type: "surprise", seed: "" })).toBe(store.getState());
    });

    it("reset goes back to the initial value; setValue replaces the current one only", () => {
        const store = start({ seed: "user-1", overrides: { hair: "bun" } });
        store.dispatch({ type: "choose", trait: "mouth", value: "grin" });
        store.dispatch({ type: "reset" });
        expect(valueOf(store.getState())).toEqual({ seed: "user-1", overrides: { hair: "bun" } });
        expect(isDirty(store.getState())).toBe(false);

        store.dispatch({ type: "setValue", value: { seed: "other", overrides: {} } });
        expect(store.getState().seed).toBe("other");
        expect(isDirty(store.getState())).toBe(true);
        store.dispatch({ type: "reset" });
        expect(store.getState().seed).toBe("user-1");
    });

    it("isDirty compares resolved traits, not raw overrides", () => {
        const seedHair = avatarTraits("user-1").hair;
        const store = start({ seed: "user-1" });
        store.dispatch({ type: "choose", trait: "hair", value: seedHair === "bun" ? "crop" : "bun" });
        store.dispatch({ type: "choose", trait: "hair", value: seedHair });
        expect(isDirty(store.getState())).toBe(false);
        expect(valueOf(store.getState()).overrides).toEqual({});
    });

    it("previewTraits shows one option applied with the rules", () => {
        const state = start({ seed: "user-1", overrides: { hair: "afro" } }).getState();
        const preview = previewTraits(state, "accessory", "beanie");
        expect(preview.accessory).toBe("beanie");
        expect(preview.hair).not.toBe("afro");
        expect(state.traits.hair).toBe("afro");
    });

    it("store notifies subscribers only on real changes and can unsubscribe", () => {
        const store = start({ seed: "user-1" });
        const calls = [];
        const unsubscribe = store.subscribe((state, previous) => calls.push([state, previous]));
        store.dispatch({ type: "setLook", look: "all" });
        store.dispatch({ type: "choose", trait: "nose", value: "long" });
        unsubscribe();
        store.dispatch({ type: "choose", trait: "nose", value: "button" });
        expect(calls).toHaveLength(1);
    });

    it("property: after any action sequence, avatarTraits(value) equals the shown traits", () => {
        const rand = mulberry32(42);
        const pick = list => list[Math.floor(rand() * list.length)];
        const { traits } = optionsContract();
        const traitNames = Object.keys(traits);
        for (let run = 0; run < 500; run++) {
            const store = start({ seed: `p-${run}` });
            for (let step = 0; step < 8; step++) {
                const roll = rand();
                if (roll < 0.7) {
                    const trait = pick(traitNames);
                    store.dispatch({ type: "choose", trait, value: pick(traits[trait]) });
                } else if (roll < 0.8) store.dispatch({ type: "setLook", look: pick(["all", "m", "f"]) });
                else if (roll < 0.9) store.dispatch({ type: "surprise", seed: `p-${run}-${step}` });
                else store.dispatch({ type: "reset" });
            }
            const state = store.getState();
            const { seed, overrides } = valueOf(state);
            expect(avatarTraits(seed, overrides)).toEqual(state.traits);
        }
    });
});
