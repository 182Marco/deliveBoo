// "Customise avatar" page (/account/avatar, every role). Mounts the svg-avatar editor and keeps the form in sync;
// saving is a normal form submit (validated by UpdateAvatarRequest).
import { mountAvatarEditor } from "svg-avatar/editor";

// phones: the form is a full-screen sheet over the page (resources/css/admin.css)
const SHEET_MEDIA = "(max-width: 599.98px)";

// while the sheet is open, the page and the site header behind it are out of reach (keyboard, readers)
// and the page does not scroll under it
const behindTheSheet = (root, sheet) => {
    root.documentElement?.classList.add("adm-scroll-lock");
    const query = globalThis.matchMedia?.(SHEET_MEDIA);
    const behind = [...root.querySelectorAll(".adm-header, .plat-header, .adm-skip, [data-avatar-behind]")].filter(e => !e.contains(sheet));
    const apply = () => behind.forEach(e => (e.inert = !!query?.matches));
    query?.addEventListener?.("change", apply);
    apply();
    return () => {
        query?.removeEventListener?.("change", apply);
        behind.forEach(e => (e.inert = false));
        root.documentElement?.classList.remove("adm-scroll-lock");
    };
};

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
        // on phones the editor fills the sheet: the preview stays on screen while choosing
        fillMedia: SHEET_MEDIA,
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
    const release = behindTheSheet(root.ownerDocument ?? root, form);
    form.addEventListener("submit", () => window.removeEventListener("beforeunload", guard));

    return {
        editor,
        destroy: () => {
            window.removeEventListener("beforeunload", guard);
            release();
            editor.destroy();
        }
    };
};

setupAvatarEditorPage();

export { setupAvatarEditorPage };
