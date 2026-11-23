import { describe, expect, it } from "vitest";
import { applyRules, hiddenLayers } from "../rules/index.js";
import { avatarTraits, normalizeTraits } from "../core/traits/index.js";
import rules from "../rules/rules.json";

const forbidden = traits => rules.exclude.some(rule => Object.entries(rule)
    .filter(([key]) => key !== "prefer")
    .every(([category, names]) => names.includes(traits[category])));

describe("rules", () => {
    it("never produces a forbidden combination over 1000 seeds", () => {
        for (let i = 0; i < 1000; i++) {
            expect(forbidden(avatarTraits(`seed-${i}`))).toBe(false);
        }
    });

    it("fixes the preferred category when nothing is locked", () => {
        expect(applyRules({ hair: "afro", accessory: "beanie" })).toMatchObject({ hair: "afro", accessory: "none" });
    });

    it("keeps a locked choice and fixes the other category", () => {
        const fixed = applyRules({ hair: "afro", accessory: "beanie" }, new Set(["accessory"]));
        expect(fixed.accessory).toBe("beanie");
        expect(rules.exclude[0].hair).not.toContain(fixed.hair);
    });

    it("falls back to the preferred category when both are locked", () => {
        expect(avatarTraits("x", { hair: "afro", accessory: "beanie" })).toMatchObject({ hair: "afro", accessory: "none" });
    });

    it("does not mutate its input", () => {
        const traits = { hair: "bun", accessory: "beanie" };
        applyRules(traits);
        expect(traits.accessory).toBe("beanie");
    });

    it("hides ears under long hair and the front hair under a hat", () => {
        const long = normalizeTraits({ hair: "long", accessory: "none" });
        expect([...hiddenLayers(long)]).toEqual(["ears"]);
        const beanie = normalizeTraits({ hair: "crop", accessory: "beanie" });
        expect([...hiddenLayers(beanie)]).toEqual(["hairFront"]);
        expect(hiddenLayers(normalizeTraits({ hair: "crop", accessory: "none" })).size).toBe(0);
    });
});
