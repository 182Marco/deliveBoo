import { describe, expect, it } from "vitest";
import parts from "../assets/parts.json";
import rules from "../rules/rules.json";
import optionsFixture from "./fixtures/options.json";
import { COLOR_TRAITS, defaultPalette, resolveColors } from "../palettes/index.js";
import { mulberry32 } from "../core/random/index.js";
import { avatarTraits } from "../index.js";
import { excludedPairs, optionsContract } from "./data.helpers.js";

const variants = () => Object.entries(parts.categories)
    .flatMap(([category, list]) => Object.entries(list).map(([name, variant]) => ({ category, name, variant })));

const shapesOf = variant => Object.entries(variant)
    .filter(([key]) => parts.layers.includes(key))
    .flatMap(([, shapes]) => shapes);

const allShapes = () => [...Object.values(parts.base).flat(), ...variants().flatMap(({ variant }) => shapesOf(variant))];

describe("shared data consistency", () => {
    it("rules.json only names existing categories, variants, layers and tags", () => {
        const tags = new Set(variants().flatMap(({ variant }) => variant.tags ?? []));
        rules.hideLayers.forEach(rule => {
            expect(tags.has(rule.tag), rule.tag).toBe(true);
            rule.layers.forEach(layer => expect(parts.layers).toContain(layer));
        });
        rules.exclude.forEach(rule => {
            const categories = Object.keys(rule).filter(key => key !== "prefer");
            expect(categories).toHaveLength(2);
            expect(categories).toContain(rule.prefer);
            categories.forEach(category => rule[category].forEach(name => expect(parts.categories[category], `${category}.${name}`).toHaveProperty(name)));
        });
    });

    it("variants only draw on known layers with known slots", () => {
        variants().forEach(({ category, name, variant }) => Object.keys(variant).forEach(key => {
            expect(["look", "tags", "weight", ...parts.layers], `${category}.${name}.${key}`).toContain(key);
        }));
        allShapes().forEach(shape => {
            if (shape.f) expect(parts.slots).toContain(shape.f);
            if (shape.s) expect(parts.slots).toContain(shape.s);
            expect([shape.d, shape.c, shape.e].filter(Boolean)).toHaveLength(1);
        });
        Object.keys(parts.base).forEach(layer => expect(parts.layers).toContain(layer));
    });

    it("the default palette resolves every slot and covers every colour trait", () => {
        Object.values(COLOR_TRAITS).forEach(group => expect(defaultPalette).toHaveProperty(group));
        ["skin", "hair", "cloth", "accent"].forEach(group =>
            Object.values(defaultPalette[group]).forEach(pair => expect(pair).toHaveLength(2)));
        const colors = resolveColors(avatarTraits("x"), defaultPalette);
        parts.slots.forEach(slot => expect(colors[slot], slot).toMatch(/^#[0-9a-f]{6}$/));
    });

    it("unseeded categories exist and look hints are m/f lists on hair, clothes and accessories", () => {
        parts.unseededCategories.forEach(category => expect(parts.categories).toHaveProperty(category));
        ["hair", "clothes", "accessory"].forEach(category => Object.entries(parts.categories[category]).forEach(([name, variant]) => {
            expect(variant.look, `${category}.${name}`).toBeInstanceOf(Array);
            expect(variant.look.length).toBeGreaterThan(0);
            variant.look.forEach(look => expect(["m", "f"]).toContain(look));
        }));
    });

    it("tests/fixtures/options.json matches the package data (shared with server validators)", () => {
        const { note, ...fixture } = optionsFixture;
        expect(note).toBeTypeOf("string");
        expect(fixture).toEqual(optionsContract());
    });
});

describe("saved overrides are always respected", () => {
    // deterministic sampler: same cases on every run
    const rand = mulberry32(20261123);
    const pick = list => list[Math.floor(rand() * list.length)];
    const { traits } = optionsContract();
    const pairs = excludedPairs();
    const containsPair = (values, pair) => Object.entries(pair).every(([k, v]) => values[k] === v);

    it("2000 seed + override cases: no forbidden pair in the result, every override kept", () => {
        let checked = 0;
        for (let i = 0; i < 2000; i++) {
            const overrides = Object.fromEntries(Object.entries(traits)
                .filter(() => rand() < 0.35)
                .map(([trait, values]) => [trait, pick(values)]));
            if (pairs.some(pair => containsPair(overrides, pair))) continue;
            const result = avatarTraits(`prop-${i}`, overrides);
            expect(result).toMatchObject(overrides);
            expect(pairs.some(pair => containsPair(result, pair))).toBe(false);
            checked += 1;
        }
        expect(checked).toBeGreaterThan(1500);
    });
});
