import { describe, expect, it } from "vitest";
import { avatarOptions, avatarTraits, normalizeTraits } from "../core/traits/index.js";
import { avatarPaths, renderAvatarSvg } from "../core/layers/index.js";
import { defaultPalette } from "../palettes/index.js";

const parse = markup => new DOMParser().parseFromString(markup, "image/svg+xml");
const COLOR = /^(#[0-9a-f]{6}|none)$/;

describe("layers and SVG output", () => {
    const { shapes } = avatarOptions();

    it.each(Object.entries(shapes).flatMap(([category, names]) => names.map(name => [category, name])))(
        "%s %s renders a well-formed SVG with resolved colours",
        (category, name) => {
            const doc = parse(renderAvatarSvg({ ...avatarTraits("probe"), [category]: name }));
            expect(doc.querySelector("parsererror")).toBeNull();
            const paths = [...doc.querySelectorAll("path")];
            expect(paths.length).toBeGreaterThan(10);
            paths.forEach(path => {
                expect(path.getAttribute("d")).toMatch(/^M/);
                expect(path.getAttribute("fill")).toMatch(COLOR);
                if (path.hasAttribute("stroke")) expect(path.getAttribute("stroke")).toMatch(COLOR);
            });
        }
    );

    it("hides the ears under long hair", () => {
        const ears = traits => avatarPaths(normalizeTraits(traits))
            .filter(p => p.d.startsWith("M33.5 52a5.5") || p.d.startsWith("M75.5 52a5.5")).length;
        expect(ears({ hair: "crop", skinColor: "light" })).toBe(2);
        expect(ears({ hair: "long", skinColor: "light" })).toBe(0);
    });

    it("escapes the title and sets accessibility attributes", () => {
        const doc = parse(renderAvatarSvg(avatarTraits("a"), { title: "<b>\"Anna\" & co</b>", size: 48 }));
        const svg = doc.documentElement;
        expect(doc.querySelector("parsererror")).toBeNull();
        expect(svg.getAttribute("role")).toBe("img");
        expect(svg.getAttribute("aria-label")).toBe("<b>\"Anna\" & co</b>");
        expect(svg.querySelector("title").textContent).toBe("<b>\"Anna\" & co</b>");
        expect(svg.getAttribute("width")).toBe("48");
        expect(parse(renderAvatarSvg(avatarTraits("a"))).documentElement.getAttribute("aria-hidden")).toBe("true");
    });

    it("clips to a circle by default and can stay square", () => {
        expect(renderAvatarSvg(avatarTraits("a"))).toContain("clip-path:circle(50%)");
        expect(renderAvatarSvg(avatarTraits("a"), { shape: "square" })).not.toContain("clip-path");
    });

    it("uses the colours of a custom palette and escapes them", () => {
        const palette = { ...defaultPalette, bg: { night: "#101820\"><script>" } };
        const markup = renderAvatarSvg(avatarTraits("a", {}, { palette }), { palette });
        expect(markup).not.toContain("<script>");
        expect(markup).toContain("#101820&quot;&gt;&lt;script&gt;");
    });

    it("returns the same frozen path list for the same traits (cache)", () => {
        const traits = avatarTraits("cached");
        const first = avatarPaths(traits);
        expect(avatarPaths({ ...traits })).toBe(first);
        expect(Object.isFrozen(first)).toBe(true);
    });

    it("is identical for the same seed", () => {
        expect(renderAvatarSvg(avatarTraits("user-42"))).toBe(renderAvatarSvg(avatarTraits("user-42")));
    });
});
