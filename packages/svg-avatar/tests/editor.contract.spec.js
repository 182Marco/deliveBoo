import { afterEach, describe, expect, it, vi } from "vitest";
import * as editorEntry from "../editor.js";
import { mountAvatarEditor, defaultEditorLabels } from "../editor.js";
import { avatarTraits } from "../index.js";

const tick = () => new Promise(resolve => setTimeout(resolve, 40));
const hosts = [];
const host = () => {
    const el = document.createElement("div");
    el.textContent = "fallback";
    document.body.append(el);
    hosts.push(el);
    return el;
};
const clickOption = (el, value) => el.querySelector(`[role=radio][data-value="${value}"]`).click();

afterEach(() => hosts.splice(0).forEach(el => el.remove()));

describe("editor public contract", () => {
    it("exports only mountAvatarEditor and defaultEditorLabels", () => {
        expect(Object.keys(editorEntry).sort()).toEqual(["defaultEditorLabels", "mountAvatarEditor"]);
        expect(defaultEditorLabels.surprise).toBe("Surprise me");
    });

    it("needs a DOM element and replaces its content", () => {
        expect(() => mountAvatarEditor(null)).toThrow(TypeError);
        const el = host();
        const editor = mountAvatarEditor(el, { value: { seed: "a" } });
        expect(el.textContent).not.toContain("fallback");
        expect(el.querySelector(".sa-editor")).not.toBeNull();
        editor.destroy();
    });

    it("getValue returns a fresh, minimal { seed, overrides } that reproduces the preview", () => {
        const editor = mountAvatarEditor(host(), { value: { seed: "user-5", overrides: { nose: "long", hair: "mohawk" } } });
        const value = editor.getValue();
        expect(value).toEqual({ seed: "user-5", overrides: { nose: "long" } });
        value.overrides.nose = "button";
        expect(editor.getValue().overrides.nose).toBe("long");
        expect(editor.getValue()).not.toBe(editor.getValue());
        editor.destroy();
    });

    it("onChange: never at mount nor on setValue; once per real change made in the editor", () => {
        const onChange = vi.fn();
        const el = host();
        const editor = mountAvatarEditor(el, { value: { seed: "user-5" }, onChange });
        expect(onChange).not.toHaveBeenCalled();
        editor.setValue({ seed: "other", overrides: {} });
        expect(onChange).not.toHaveBeenCalled();
        clickOption(el, "button");
        clickOption(el, "button"); // same value again: no change
        expect(onChange).toHaveBeenCalledTimes(1);
        expect(onChange.mock.calls[0][0]).toEqual(editor.getValue());
        editor.destroy();
    });

    it("setValue changes the current value only: reset and isDirty keep the initial reference", async () => {
        const onChange = vi.fn();
        const el = host();
        const editor = mountAvatarEditor(el, { value: { seed: "user-5", overrides: { hair: "bun" } }, onChange });
        expect(editor.isDirty()).toBe(false);
        editor.setValue({ seed: "user-6", overrides: {} });
        expect(editor.getValue()).toEqual({ seed: "user-6", overrides: {} });
        expect(editor.isDirty()).toBe(true);
        await tick();
        expect(el.querySelector("[role=radio][aria-checked=true][data-value]")).not.toBeNull();
        editor.reset();
        expect(editor.getValue()).toEqual({ seed: "user-5", overrides: { hair: "bun" } });
        expect(editor.isDirty()).toBe(false);
        expect(onChange).toHaveBeenCalledTimes(1);
        editor.reset(); // already initial: no change, no call
        expect(onChange).toHaveBeenCalledTimes(1);
        editor.destroy();
    });

    it("isDirty compares the resulting avatar, not the raw overrides", () => {
        const seedHair = avatarTraits("user-5").hair;
        const el = host();
        const editor = mountAvatarEditor(el, { value: { seed: "user-5" } });
        clickOption(el, seedHair === "bun" ? "crop" : "bun");
        expect(editor.isDirty()).toBe(true);
        clickOption(el, seedHair);
        expect(editor.isDirty()).toBe(false);
        editor.destroy();
    });
});

describe("editor lifecycle", () => {
    it("destroy removes the editor, releases listeners and is idempotent", async () => {
        const onChange = vi.fn();
        const el = host();
        const editor = mountAvatarEditor(el, { value: { seed: "user-5" }, onChange });
        const button = el.querySelector("[role=radio][data-value=button]");
        const last = editor.getValue();
        editor.destroy();
        editor.destroy();
        expect(el.children).toHaveLength(0);
        button.click(); // detached node, listeners aborted
        editor.reset();
        editor.setValue({ seed: "x" });
        expect(onChange).not.toHaveBeenCalled();
        expect(editor.getValue()).toEqual(last);
        expect(editor.isDirty()).toBe(false);
    });

    it("cancels a pending frame on destroy", () => {
        const cancel = vi.spyOn(globalThis, "cancelAnimationFrame");
        const el = host();
        const editor = mountAvatarEditor(el, { value: { seed: "user-5" } });
        clickOption(el, "long"); // schedules a render
        editor.destroy();
        expect(cancel).toHaveBeenCalled();
    });

    it("two editors on the same page are independent, with distinct ids", () => {
        const a = host();
        const b = host();
        const first = mountAvatarEditor(a, { value: { seed: "one" } });
        const second = mountAvatarEditor(b, { value: { seed: "two" } });
        const ids = [...document.querySelectorAll("[id]")].map(node => node.id);
        expect(new Set(ids).size).toBe(ids.length);
        clickOption(a, "button"); // "button" exists only among the noses
        expect(first.getValue().overrides.nose).toBe("button");
        expect(second.getValue().overrides).toEqual({});
        first.destroy();
        second.destroy();
    });
});
