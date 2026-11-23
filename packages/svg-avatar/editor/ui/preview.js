import { h, setSvg } from "./dom.js";

// large avatar preview; the SVG carries the accessible name (title)
const createPreview = () => {
    const frame = h("div", { class: "sa-editor-preview-frame" });
    const el = h("div", { class: "sa-editor-preview" }, [frame]);
    const update = ({ svg }) => setSvg(frame, svg);
    return { el, update };
};

export { createPreview };
