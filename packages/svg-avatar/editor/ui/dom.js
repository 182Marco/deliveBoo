// Tiny DOM helpers. Text goes through textContent and attributes through setAttribute:
// host-provided labels can never become markup.

// h("button", { class: "x", "aria-label": "…" }, ["text" | Node, …])
const h = (tag, attrs = {}, children = []) => {
    const el = document.createElement(tag);
    Object.entries(attrs).forEach(([name, value]) => {
        if (value !== null && value !== undefined && value !== false) el.setAttribute(name, value === true ? "" : String(value));
    });
    children.forEach(child => el.append(typeof child === "string" ? document.createTextNode(child) : child));
    return el;
};

const setAttr = (el, name, value) => {
    if (value === null || value === undefined || value === false) el.removeAttribute(name);
    else el.setAttribute(name, String(value));
};

// the only markup injection: SVG strings produced (and escaped) by renderAvatarSvg; skipped when unchanged
const setSvg = (el, markup) => {
    if (el.dataset.svg === markup) return;
    el.innerHTML = markup;
    el.dataset.svg = markup;
};

export { h, setAttr, setSvg };
