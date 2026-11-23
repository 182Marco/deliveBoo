import { h } from "./dom.js";

// polite announcements for changes the user did not make directly (rules moving a trait)
const createLiveRegion = () => {
    const el = h("div", { class: "sa-editor-sr", role: "status", "aria-live": "polite" });
    const announce = text => {
        el.textContent = "";
        if (text) el.textContent = text;
    };
    return { el, announce };
};

export { createLiveRegion };
