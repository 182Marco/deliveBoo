import { VIEWBOX } from "../../assets/catalog.js";

const ESCAPES = { "&": "&amp;", "<": "&lt;", ">": "&gt;", "\"": "&quot;", "'": "&#39;" };
const escape = value => String(value).replace(/[&<>"']/g, ch => ESCAPES[ch]);

const attrString = attrs => Object.entries(attrs).map(([name, value]) => `${name}="${escape(value)}"`).join(" ");

const CLIP = { circle: "clip-path:circle(50%)", square: "" };

// paths -> standalone <svg> markup; decorative unless a title is given.
// viewBox (internal, used by the editor to zoom thumbnails) defaults to the whole avatar.
const toSvgString = (paths, { size, title = "", shape = "circle", viewBox = `0 0 ${VIEWBOX} ${VIEWBOX}` } = {}) => {
    const sizeAttrs = size ? ` width="${escape(size)}" height="${escape(size)}"` : "";
    const clip = CLIP[shape] ?? CLIP.circle;
    const style = clip ? ` style="${clip}"` : "";
    const a11y = title ? ` role="img" aria-label="${escape(title)}"` : " aria-hidden=\"true\"";
    const label = title ? `<title>${escape(title)}</title>` : "";
    const body = paths.map(attrs => `<path ${attrString(attrs)}/>`).join("");
    return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="${escape(viewBox)}"${sizeAttrs}${style}${a11y} focusable="false">${label}${body}</svg>`;
};

export { toSvgString };
