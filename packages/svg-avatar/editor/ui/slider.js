// Slider of the editor sections (layout "slider"): one section per slide, moved by horizontal
// scrolling (trackpad, swipe; snaps one slide at a time) or by the two chevrons and the dots.
// Slides out of view are inert, so Tab stays in the section on screen.
import { interpolate } from "../labels.js";
import { h, setAttr } from "./dom.js";

const SVG_NS = "http://www.w3.org/2000/svg";

// a chevron drawn with DOM nodes (no markup strings)
const chevron = direction => {
    const svg = document.createElementNS(SVG_NS, "svg");
    svg.setAttribute("class", "sa-editor-nav-icon");
    svg.setAttribute("viewBox", "0 0 24 24");
    svg.setAttribute("aria-hidden", "true");
    svg.setAttribute("focusable", "false");
    const path = document.createElementNS(SVG_NS, "path");
    path.setAttribute("d", direction === "prev" ? "M15 5l-7 7 7 7" : "M9 5l7 7-7 7");
    path.setAttribute("fill", "none");
    path.setAttribute("stroke", "currentColor");
    path.setAttribute("stroke-width", "2.25");
    path.setAttribute("stroke-linecap", "round");
    path.setAttribute("stroke-linejoin", "round");
    svg.append(path);
    return svg;
};

// index of the slide in view from the horizontal position (slides are as wide as the track)
const slideAt = (scrollLeft, width, total) =>
    width > 0 ? Math.min(total - 1, Math.max(0, Math.round(scrollLeft / width))) : 0;

/**
 * @param {{ id: string, labels: object, signal: AbortSignal, slides: Element[], titles: string[] }} options
 * @returns {{ el: Element, goTo: (index: number) => void, current: () => number, setFill: (on: boolean) => void }}
 */
const createSlider = ({ id, labels, signal, slides, titles }) => {
    const total = slides.length;
    const trackId = `${id}-track`;
    let current = 0;
    // while the chevrons or the dots scroll to a slide, the slides passed on the way are ignored
    let target = null;
    // "fill": the slider takes the height the host gives it and each slide scrolls on its own
    let fill = false;

    const prev = h("button", { type: "button", class: "sa-editor-nav", "aria-controls": trackId, "aria-label": labels.previous }, [chevron("prev")]);
    const next = h("button", { type: "button", class: "sa-editor-nav", "aria-controls": trackId, "aria-label": labels.next }, [chevron("next")]);
    const title = h("p", { class: "sa-editor-slider-title" });
    const count = h("span", { class: "sa-editor-slider-count" });
    const dots = titles.map(text => h("button", { type: "button", class: "sa-editor-dot", "aria-controls": trackId, "aria-label": text }));

    slides.forEach((slide, i) => {
        slide.classList.add("sa-editor-slide");
        slide.setAttribute("aria-label", interpolate(labels.slideOf, { name: titles[i], current: i + 1, total }));
    });
    const track = h("div", { class: "sa-editor-track", id: trackId }, slides);

    const bar = h("div", { class: "sa-editor-slider-bar" }, [
        prev,
        h("div", { class: "sa-editor-slider-center" }, [
            h("div", { class: "sa-editor-slider-status", "aria-live": "polite" }, [title, count]),
            h("div", { class: "sa-editor-dots" }, dots)
        ]),
        next
    ]);
    const el = h("div", { class: "sa-editor-slider", role: "region", "aria-label": labels.features }, [bar, track]);

    // the track follows the height of the slide in view (a short section leaves no empty band)
    const fitHeight = () => {
        if (fill) {
            track.style.height = "";
            return;
        }
        const height = slides[current]?.offsetHeight ?? 0;
        track.style.height = height > 0 ? `${height}px` : "";
    };

    const show = index => {
        current = index;
        title.textContent = titles[index];
        count.textContent = interpolate(labels.slideCount, { current: index + 1, total });
        prev.disabled = index === 0;
        next.disabled = index === total - 1;
        dots.forEach((dot, i) => {
            dot.classList.toggle("sa-editor-dot--active", i === index);
            setAttr(dot, "aria-current", i === index ? "true" : null);
        });
        slides.forEach((slide, i) => {
            slide.inert = i !== index;
            setAttr(slide, "aria-hidden", i !== index ? "true" : null);
        });
        fitHeight();
    };

    const goTo = index => {
        const to = Math.min(total - 1, Math.max(0, index));
        if (to === current && target === null) return;
        target = to;
        show(to);
        const left = to * track.clientWidth;
        const still = globalThis.matchMedia?.("(prefers-reduced-motion: reduce)").matches;
        if (typeof track.scrollTo === "function") track.scrollTo({ left, behavior: still ? "auto" : "smooth" });
        else track.scrollLeft = left;
        if (track.clientWidth === 0) target = null;
    };

    track.addEventListener("scroll", () => {
        const index = slideAt(track.scrollLeft, track.clientWidth, total);
        if (target !== null) {
            if (index === target) target = null;
            return;
        }
        if (index !== current) show(index);
    }, { passive: true, signal });
    // a resize keeps the current slide in place (the snap points move with the width)
    globalThis.addEventListener?.("resize", () => {
        track.scrollLeft = current * track.clientWidth;
        fitHeight();
    }, { passive: true, signal });
    // a slide can change height (look filter, wrapping thumbnails, the editor mounted later)
    if (typeof ResizeObserver === "function") {
        const observer = new ResizeObserver(fitHeight);
        slides.forEach(slide => observer.observe(slide));
        signal.addEventListener("abort", () => observer.disconnect());
    }

    prev.addEventListener("click", () => goTo(current - 1), { signal });
    next.addEventListener("click", () => goTo(current + 1), { signal });
    dots.forEach((dot, i) => dot.addEventListener("click", () => goTo(i), { signal }));

    const setFill = on => {
        fill = !!on;
        el.classList.toggle("sa-editor-slider--fill", fill);
        bar.classList.toggle("sa-editor-slider-bar--fill", fill);
        [prev, next].forEach(button => button.classList.toggle("sa-editor-nav--fill", fill));
        track.classList.toggle("sa-editor-track--fill", fill);
        slides.forEach(slide => slide.classList.toggle("sa-editor-slide--fill", fill));
        fitHeight();
        track.scrollLeft = current * track.clientWidth;
    };

    show(0);

    return { el, goTo, current: () => current, setFill };
};

export { createSlider, slideAt };
