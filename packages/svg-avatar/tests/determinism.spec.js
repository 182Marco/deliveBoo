import { describe, expect, it } from "vitest";
import { cyrb53, mulberry32, seededRandom, randomSeed } from "../core/random/index.js";
import { avatarTraits, normalizeTraits, avatarOptions } from "../core/traits/index.js";
import { defaultPalette } from "../palettes/index.js";
import { avatarTraits as publicTraits } from "../index.js";
import fixture from "./fixtures/seeds.json";

describe("core/random", () => {
    it("hashes the same string to the same 53-bit integer", () => {
        expect(cyrb53("user-1")).toBe(cyrb53("user-1"));
        expect(cyrb53("user-1")).not.toBe(cyrb53("user-2"));
        expect(Number.isSafeInteger(cyrb53("anything"))).toBe(true);
    });

    it("keeps known values stable (contract for other implementations)", () => {
        expect(cyrb53("")).toBe(3338908027751811);
        expect(cyrb53("a")).toBe(7929297801672961);
    });

    it("produces the same float sequence for the same seed", () => {
        const a = mulberry32(42);
        const b = mulberry32(42);
        const seqA = Array.from({ length: 5 }, () => a());
        expect(Array.from({ length: 5 }, () => b())).toEqual(seqA);
        seqA.forEach(n => {
            expect(n).toBeGreaterThanOrEqual(0);
            expect(n).toBeLessThan(1);
        });
    });

    it("treats any seed value as a string", () => {
        expect(seededRandom(7)()).toBe(seededRandom("7")());
        expect(seededRandom(null)()).toBe(seededRandom("")());
    });

    it("creates distinct hex random seeds", () => {
        const seeds = new Set(Array.from({ length: 50 }, randomSeed));
        expect(seeds.size).toBe(50);
        seeds.forEach(s => expect(s).toMatch(/^[0-9a-f]{16}$/));
    });
});

describe("traits", () => {
    it("gives the same traits for the same seed and different traits across seeds", () => {
        expect(avatarTraits("user-1")).toEqual(avatarTraits("user-1"));
        const distinct = new Set(Array.from({ length: 50 }, (_, i) => JSON.stringify(avatarTraits(`user-${i}`))));
        expect(distinct.size).toBeGreaterThan(45);
    });

    it("applies valid overrides and ignores invalid ones", () => {
        const base = avatarTraits("user-1");
        expect(avatarTraits("user-1", { hair: "bun", skinColor: "deep" })).toMatchObject({ hair: "bun", skinColor: "deep" });
        expect(avatarTraits("user-1", { hair: "mohawk", skinColor: "#000", other: 1 })).toEqual(base);
        expect(avatarTraits("user-1", null)).toEqual(base);
    });

    it("an override does not shift the other traits drawn from the seed", () => {
        const base = avatarTraits("user-9");
        const changed = avatarTraits("user-9", { mouth: base.mouth === "calm" ? "grin" : "calm" });
        expect({ ...changed, mouth: base.mouth }).toEqual(base);
    });

    it("completes partial or broken traits with the first options", () => {
        const options = avatarOptions();
        const traits = normalizeTraits({ face: "triangle" });
        expect(traits.face).toBe(options.shapes.face[0]);
        expect(traits.skinColor).toBe(options.colors.skinColor[0]);
        expect(Object.keys(traits)).toHaveLength(14);
    });

    it("reaches every variant and colour over 3000 seeds, with about half without accessory", () => {
        const seen = {};
        let none = 0;
        for (let i = 0; i < 3000; i++) {
            const t = avatarTraits(`s${i}`);
            Object.entries(t).forEach(([k, v]) => { (seen[k] ??= new Set()).add(v); });
            if (t.accessory === "none") none += 1;
        }
        const { shapes, colors } = avatarOptions();
        const { nose, ...seededShapes } = shapes;
        Object.entries({ ...seededShapes, ...colors }).forEach(([k, values]) => expect([...seen[k]].sort()).toEqual([...values].sort()));
        // nose is not drawn from the seed: always the first variant unless overridden
        expect([...seen.nose]).toEqual([nose[0]]);
        expect(none / 3000).toBeGreaterThan(0.4);
        expect(none / 3000).toBeLessThan(0.6);
    });

    it("picks colours from a custom palette", () => {
        const palette = { ...defaultPalette, bg: { mint: "#d8f0e4" } };
        expect(avatarTraits("user-1", {}, { palette }).bgColor).toBe("mint");
        expect(avatarOptions(palette).colors.bgColor).toEqual(["mint"]);
    });
});

describe("seed fixture (cross-implementation contract)", () => {
    it.each(fixture.cases.map(c => [c.seed, c.traits]))("seed %j keeps its traits", (seed, traits) => {
        // the frozen fields never change; categories added later take their first variant
        expect(publicTraits(seed)).toEqual({ ...traits, nose: "soft" });
    });
});
