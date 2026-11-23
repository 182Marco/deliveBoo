// Existing avatars must never change (fixture frozen before the editor phase, see compat.json "note").
import { describe, expect, it, vi } from "vitest";
import { readFileSync } from "node:fs";
import { dirname, join } from "node:path";
import { fileURLToPath } from "node:url";
import parts from "../assets/parts.json";
import compat from "./fixtures/compat.json";
import { traitsFromSeed } from "../core/traits/index.js";
import { defaultPalette } from "../palettes/index.js";
import { DRAW_PLAN, sha, dataHashes, svgHash } from "./compat.helpers.js";

// spy generator: counts how many numbers each seed consumes
const draws = vi.hoisted(() => ({ count: 0 }));
vi.mock("../core/random/index.js", async importOriginal => {
    const actual = await importOriginal();
    return {
        ...actual,
        seededRandom: seed => {
            const next = actual.seededRandom(seed);
            return () => {
                draws.count += 1;
                return next();
            };
        }
    };
});

describe("compatibility with existing avatars", () => {
    it("keeps the same draw plan: 13 draws, same traits, same order", () => {
        expect(DRAW_PLAN).toEqual(compat.drawPlan);
        ["user-1", "", "anna@example.com"].forEach(seed => {
            draws.count = 0;
            const traits = traitsFromSeed(seed, defaultPalette);
            expect(draws.count).toBe(compat.drawPlan.length);
            expect(Object.keys(traits).filter(key => compat.drawPlan.includes(key))).toEqual(compat.drawPlan);
        });
    });

    it("keeps the drawing data of every existing category, layers, base shapes and palette", () => {
        expect(dataHashes()).toEqual(compat.data);
    });

    it("draws the old nose by default", () => {
        const nose = parts.categories.nose;
        if (nose) {
            expect(Object.keys(nose)[0]).toBe("soft");
            expect(nose.soft.nose).toEqual(compat.legacyNose);
            expect(nose.soft.seeded ?? parts.categories.nose.seeded).not.toBe(true);
        } else {
            expect(parts.base.nose).toEqual(compat.legacyNose);
        }
        expect(parts.layers.indexOf("nose")).toBe(9);
    });

    it("leaves seeds.json untouched", () => {
        expect(sha(readFileSync(join(dirname(fileURLToPath(import.meta.url)), "fixtures/seeds.json"), "utf8"))).toBe(compat.seedsJson);
    });

    it("renders the same SVG for every frozen seed", () => {
        Object.entries(compat.svg).forEach(([seed, hash]) => expect(svgHash(seed), seed).toBe(hash));
    });

    it("renders the same SVG for every override that could be saved before", () => {
        compat.overrides.forEach(({ seed, overrides, svg }) => expect(svgHash(seed, overrides), JSON.stringify(overrides)).toBe(svg));
    });
});
