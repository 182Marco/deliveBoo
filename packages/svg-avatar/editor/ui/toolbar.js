import { h } from "./dom.js";

const createToolbar = ({ labels, signal, onSurprise, onReset }) => {
    const surprise = h("button", { type: "button", class: "sa-editor-action" }, [labels.surprise]);
    const reset = h("button", { type: "button", class: "sa-editor-action" }, [labels.reset]);
    surprise.addEventListener("click", onSurprise, { signal });
    reset.addEventListener("click", onReset, { signal });
    const el = h("div", { class: "sa-editor-toolbar" }, [surprise, reset]);
    const update = ({ canReset }) => {
        reset.disabled = !canReset;
    };
    return { el, update };
};

export { createToolbar };
