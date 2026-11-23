import { describe, expect, it } from "vitest";
import { defaultEditorLabels, resolveLabels, interpolate, humanize, variantLabel, traitLabel } from "../editor/labels.js";
import { SECTIONS } from "../editor/groups.js";
import { optionsContract } from "./data.helpers.js";

describe("editor labels", () => {
    it("has a distinct default label for every trait value, trait and section", () => {
        const { traits } = optionsContract();
        Object.entries(traits).forEach(([trait, values]) => {
            expect(defaultEditorLabels.traits[trait], trait).toBeTypeOf("string");
            const names = values.map(value => defaultEditorLabels.variants[trait]?.[value]);
            names.forEach((name, i) => expect(name, `${trait}.${values[i]}`).toBeTypeOf("string"));
            expect(new Set(names).size, trait).toBe(values.length);
        });
        SECTIONS.forEach(section => expect(defaultEditorLabels.sections[section.id]).toBeTypeOf("string"));
    });

    it("merges host translations, keeping defaults for missing or non-string values", () => {
        const labels = resolveLabels({
            surprise: "Sorprendimi",
            reset: 42,
            sections: { hair: "Capelli", nose: null },
            variants: { hair: { bun: "Chignon", crop: "" }, custom: { x: "X" }, eyes: "bad" },
            unknown: "ignored"
        });
        expect(labels.surprise).toBe("Sorprendimi");
        expect(labels.reset).toBe("Reset");
        expect(labels.sections.hair).toBe("Capelli");
        expect(labels.sections.nose).toBe("Nose");
        expect(labels.variants.hair.bun).toBe("Chignon");
        expect(labels.variants.hair.crop).toBe("Crop");
        expect(labels.variants.custom.x).toBe("X");
        expect(labels.variants.eyes.open).toBe("Open");
        expect(labels).not.toHaveProperty("unknown");
        expect(resolveLabels(undefined).surprise).toBe("Surprise me");
    });

    it("interpolates placeholders as plain text only", () => {
        expect(interpolate(":trait: :value", { trait: "Hair", value: "<b>Bun</b>" })).toBe("Hair: <b>Bun</b>");
        expect(interpolate(":missing stays", {})).toBe(":missing stays");
    });

    it("falls back to readable names", () => {
        expect(humanize("sidePart")).toBe("Side part");
        expect(variantLabel(resolveLabels({}), "hair", "newStyle")).toBe("New style");
        expect(traitLabel(resolveLabels({}), "hairColor")).toBe("Hair colour");
    });
});
