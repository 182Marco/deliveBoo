import { afterEach, describe, expect, it, vi } from "vitest";
import { mountAvatarEditor } from "../editor.js";
import { avatarTraits } from "../index.js";

const tick = () => new Promise(resolve => setTimeout(resolve, 40));
const mounted = [];

const setup = (options = {}) => {
    const host = document.createElement("div");
    document.body.append(host);
    const editor = mountAvatarEditor(host, { value: { seed: "user-1", overrides: { hair: "crop" } }, ...options });
    mounted.push([host, editor]);
    return { host, editor };
};

const group = (host, label) => [...host.querySelectorAll("[role=radiogroup]")]
    .find(list => host.querySelector(`#${list.getAttribute("aria-labelledby")}`).textContent === label);

const radios = list => [...list.querySelectorAll("[role=radio]")];
const checked = list => radios(list).find(radio => radio.getAttribute("aria-checked") === "true");

afterEach(() => {
    mounted.splice(0).forEach(([host, editor]) => {
        editor.destroy();
        host.remove();
    });
});

describe("editor interface", () => {
    it("renders one radiogroup per trait inside 9 labelled sections, with a preview", () => {
        const { host } = setup();
        expect(host.querySelectorAll("section")).toHaveLength(9);
        expect(host.querySelectorAll("[role=radiogroup]")).toHaveLength(15); // 14 traits + look filter
        const preview = host.querySelector(".sa-editor-preview svg");
        expect(preview.getAttribute("role")).toBe("img");
        expect(preview.getAttribute("aria-label")).toBe("Avatar preview");
        expect(checked(group(host, "Hairstyle")).dataset.value).toBe("crop");
    });

    it("gives every option a descriptive, unique accessible name", () => {
        const { host } = setup();
        host.querySelectorAll("[role=radiogroup]").forEach(list => {
            const names = radios(list).map(radio => radio.getAttribute("aria-label") ?? radio.textContent);
            names.forEach(name => {
                expect(name.trim()).not.toBe("");
                expect(name).not.toMatch(/option \d+/i);
            });
            expect(new Set(names).size).toBe(names.length);
        });
        expect(radios(group(host, "Hairstyle")).map(r => r.getAttribute("aria-label"))).toContain("Hairstyle: Bun");
        expect(radios(group(host, "Skin tone")).map(r => r.getAttribute("aria-label"))).toContain("Skin tone: Porcelain");
    });

    it("a click selects the option, updates the preview and reports the value", async () => {
        const onChange = vi.fn();
        const { host } = setup({ onChange });
        const before = host.querySelector(".sa-editor-preview-frame").innerHTML;
        radios(group(host, "Hairstyle")).find(r => r.dataset.value === "bun").click();
        expect(onChange).toHaveBeenCalledTimes(1);
        expect(onChange.mock.calls[0][0].overrides.hair).toBe("bun");
        await tick();
        expect(checked(group(host, "Hairstyle")).dataset.value).toBe("bun");
        expect(host.querySelector(".sa-editor-preview-frame").innerHTML).not.toBe(before);
    });

    it("keyboard: one tab stop per group, arrows and Home/End move and select (wrapping)", async () => {
        const onChange = vi.fn();
        const { host } = setup({ onChange });
        const list = group(host, "Nose");
        expect(radios(list).filter(r => r.tabIndex === 0)).toHaveLength(1);
        const current = checked(list);
        current.focus();
        current.dispatchEvent(new KeyboardEvent("keydown", { key: "ArrowRight", bubbles: true }));
        await tick();
        expect(checked(list).dataset.value).toBe("button");
        expect(document.activeElement).toBe(checked(list));
        checked(list).dispatchEvent(new KeyboardEvent("keydown", { key: "End", bubbles: true }));
        await tick();
        expect(checked(list).dataset.value).toBe("long");
        checked(list).dispatchEvent(new KeyboardEvent("keydown", { key: "ArrowDown", bubbles: true }));
        await tick();
        expect(checked(list).dataset.value).toBe("soft");
        checked(list).dispatchEvent(new KeyboardEvent("keydown", { key: "Home", bubbles: true }));
        await tick();
        expect(checked(list).dataset.value).toBe("soft");
        expect(radios(list).filter(r => r.tabIndex === 0)).toHaveLength(1);
    });

    it("the look filter reorders hairstyles with an 'Other options' divider, without changing the value", async () => {
        const onChange = vi.fn();
        const { host, editor } = setup({ onChange });
        const looks = group(host, "Suggestions for");
        radios(looks).find(r => r.textContent === "Feminine").click();
        await tick();
        expect(onChange).not.toHaveBeenCalled();
        expect(editor.getValue()).toEqual({ seed: "user-1", overrides: { hair: "crop" } });
        const hair = group(host, "Hairstyle");
        expect(radios(hair)[0].dataset.value).toBe("curly");
        expect(hair.querySelector(".sa-editor-divider").textContent).toBe("Other options");
        expect(radios(hair)).toHaveLength(12);
    });

    it("announces traits moved by the rules", async () => {
        const { host } = setup({ value: { seed: "user-1", overrides: { hair: "afro" } } });
        radios(group(host, "Accessory")).find(r => r.dataset.value === "beanie").click();
        const status = host.querySelector("[role=status]");
        expect(status.textContent).toMatch(/^Hairstyle changed to .+ to go with Beanie\.$/);
    });

    it("Surprise me changes the avatar; Reset is enabled only when something changed", async () => {
        const onChange = vi.fn();
        const { host, editor } = setup({ onChange });
        const [surprise, reset] = host.querySelectorAll(".sa-editor-action");
        expect(reset.disabled).toBe(true);
        surprise.click();
        await tick();
        expect(onChange).toHaveBeenCalledTimes(1);
        expect(editor.getValue().seed).not.toBe("user-1");
        expect(reset.disabled).toBe(false);
        reset.click();
        await tick();
        expect(editor.getValue()).toEqual({ seed: "user-1", overrides: { hair: "crop" } });
        expect(reset.disabled).toBe(true);
    });

    it("host labels are used and always rendered as text", () => {
        const { host } = setup({ labels: { sections: { hair: "<img src=x onerror=alert(1)>" }, variants: { hair: { bun: "Chignon" } } } });
        expect(host.querySelector("img")).toBeNull();
        expect([...host.querySelectorAll("h2")].map(h2 => h2.textContent)).toContain("<img src=x onerror=alert(1)>");
        expect(radios(group(host, "Hairstyle")).map(r => r.getAttribute("aria-label"))).toContain("Hairstyle: Chignon");
    });

    it("labels repeating a section heading are kept for screen readers only", () => {
        const { host } = setup();
        const nose = group(host, "Nose");
        const label = host.querySelector(`#${nose.getAttribute("aria-labelledby")}`);
        expect(label.className).toBe("sa-editor-sr");
        const outfit = group(host, "Outfit");
        expect(host.querySelector(`#${outfit.getAttribute("aria-labelledby")}`).className).toBe("sa-editor-sr");
        expect(host.querySelector(`#${group(host, "Hairstyle").getAttribute("aria-labelledby")}`).className).toBe("sa-editor-group-label");
    });

    it("facial feature thumbnails zoom on the face; hairstyles show the whole avatar", () => {
        const { host } = setup();
        const viewBoxOf = (label, value) => radios(group(host, label)).find(r => r.dataset.value === value)
            .querySelector("svg").getAttribute("viewBox");
        expect(viewBoxOf("Nose", "button")).toBe("36 32 48 48");
        expect(viewBoxOf("Face shape", "round")).toBe("24 14 72 72");
        expect(viewBoxOf("Hairstyle", "bun")).toBe("0 0 120 120");
    });

    it("thumbnails show the current avatar with that option applied", () => {
        const { host } = setup();
        const bun = radios(group(host, "Hairstyle")).find(r => r.dataset.value === "bun");
        expect(bun.querySelector(".sa-editor-thumb").getAttribute("aria-hidden")).toBe("true");
        expect(bun.querySelectorAll("path").length).toBeGreaterThan(10);
        expect(avatarTraits("user-1", { hair: "crop" }).hair).toBe("crop");
    });
});
