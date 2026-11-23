// partner area: jsBack/avatarEditor.js mounts the svg-avatar editor and keeps the form in sync
import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";

const page = ({ value = '{"seed":"user-3","overrides":{"hair":"bun"}}', unsaved = false } = {}) => {
    document.body.innerHTML = `
        <form data-avatar-form ${unsaved ? "data-avatar-unsaved" : ""}>
            <input type="hidden" name="seed" value="user-3" data-avatar-seed-input>
            <input type="hidden" name="overrides" value='{"hair":"bun"}' data-avatar-overrides-input>
            <div data-avatar-editor data-value='${value}' data-labels='{"surprise":"Sorprendimi"}'></div>
            <button type="submit" data-avatar-save disabled>Save</button>
        </form>`;
};

const radio = value => document.querySelector(`[role=radio][data-value="${value}"]`);
let current = null;

const load = async () => {
    const { setupAvatarEditorPage } = await import("../../resources/js/jsBack/avatarEditor.js");
    current?.destroy();
    current = setupAvatarEditorPage();
    return current;
};

describe("jsBack/avatarEditor.js", () => {
    beforeEach(() => page());
    afterEach(() => {
        current?.destroy();
        current = null;
    });

    it("mounts the editor with the stored value and the host labels; Save starts disabled", async () => {
        await load();
        expect(radio("bun").getAttribute("aria-checked")).toBe("true");
        expect([...document.querySelectorAll(".sa-editor-action")].map(b => b.textContent)).toContain("Sorprendimi");
        expect(document.querySelector("[data-avatar-save]").disabled).toBe(true);
    });

    it("a change fills the hidden fields and enables Save; undoing it disables Save again", async () => {
        await load();
        radio("button").click();
        const overrides = JSON.parse(document.querySelector("[data-avatar-overrides-input]").value);
        expect(overrides).toEqual({ hair: "bun", nose: "button" });
        expect(document.querySelector("[data-avatar-seed-input]").value).toBe("user-3");
        expect(document.querySelector("[data-avatar-save]").disabled).toBe(false);
        document.querySelector(`[role=radio][aria-label="Nose: Soft"]`).click();
        expect(document.querySelector("[data-avatar-save]").disabled).toBe(true);
    });

    it("warns before leaving with unsaved changes, not after submitting", async () => {
        await load();
        const leave = () => {
            const event = new Event("beforeunload", { cancelable: true });
            window.dispatchEvent(event);
            return event.defaultPrevented;
        };
        expect(leave()).toBe(false);
        radio("button").click();
        expect(leave()).toBe(true);
        document.querySelector("[data-avatar-form]").dispatchEvent(new Event("submit"));
        expect(leave()).toBe(false);
    });

    it("falls back to the hidden fields when the data attribute is broken", async () => {
        page({ value: "{not json" });
        await load();
        expect(radio("bun").getAttribute("aria-checked")).toBe("true");
    });

    it("after a rejected submit Save stays enabled to retry", async () => {
        page({ unsaved: true });
        await load();
        expect(document.querySelector("[data-avatar-save]").disabled).toBe(false);
    });

    it("does nothing on pages without the form", async () => {
        document.body.innerHTML = "<p>other page</p>";
        const { setupAvatarEditorPage } = await import("../../resources/js/jsBack/avatarEditor.js");
        expect(setupAvatarEditorPage()).toBeNull();
        vi.restoreAllMocks();
    });
});
