// "Customise avatar" page (/account/avatar, every role). Mounts the svg-avatar editor and keeps the form in sync;
// saving is a normal form submit (validated by UpdateAvatarRequest).
import { mountAvatarEditor } from "svg-avatar/editor";

const parse = (raw, fallback) => {
    try {
        const value = JSON.parse(raw);
        return value && typeof value === "object" && !Array.isArray(value) ? value : fallback;
    } catch {
        return fallback;
    }
};

const setupAvatarEditorPage = (root = document) => {
    const form = root.querySelector("[data-avatar-form]");
    if (!form) return null;
    const mountEl = form.querySelector("[data-avatar-editor]");
    const seedInput = form.querySelector("[data-avatar-seed-input]");
    const overridesInput = form.querySelector("[data-avatar-overrides-input]");
    const save = form.querySelector("[data-avatar-save]");
    // after a rejected submit the page reopens on the unsaved composition: it can be saved again
    const unsaved = form.hasAttribute("data-avatar-unsaved");

    // broken attribute: fall back to the value in the hidden fields
    const fallback = { seed: seedInput.value, overrides: parse(overridesInput.value, {}) };
    let editor = null;

    const sync = value => {
        seedInput.value = value.seed;
        overridesInput.value = JSON.stringify(value.overrides);
        save.disabled = !(unsaved || editor?.isDirty());
    };

    editor = mountAvatarEditor(mountEl, {
        value: parse(mountEl.dataset.value, fallback),
        labels: parse(mountEl.dataset.labels, {}),
        // one section at a time: horizontal scroll, two chevrons and dots (no long page of options)
        layout: "slider",
        onChange: sync
    });
    sync(editor.getValue());

    // warn before leaving with unsaved changes; never after submitting
    const guard = event => {
        if (editor.isDirty()) {
            event.preventDefault();
            event.returnValue = "";
        }
    };
    window.addEventListener("beforeunload", guard);
    form.addEventListener("submit", () => window.removeEventListener("beforeunload", guard));

    return {
        editor,
        destroy: () => {
            window.removeEventListener("beforeunload", guard);
            editor.destroy();
        }
    };
};

setupAvatarEditorPage();

export { setupAvatarEditorPage };
