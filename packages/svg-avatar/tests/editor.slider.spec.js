import { afterEach, describe, expect, it } from "vitest";
import { mountAvatarEditor } from "../editor.js";
import { slideAt } from "../editor/ui/slider.js";

const mounted = [];

const setup = (options = {}) => {
    const host = document.createElement("div");
    document.body.append(host);
    const editor = mountAvatarEditor(host, { value: { seed: "user-1" }, layout: "slider", ...options });
    mounted.push([host, editor]);
    return { host, editor };
};

const nav = host => [...host.querySelectorAll(".sa-editor-nav")];
const slides = host => [...host.querySelectorAll(".sa-editor-slide")];
const status = host => `${host.querySelector(".sa-editor-slider-title").textContent} ${host.querySelector(".sa-editor-slider-count").textContent}`;
const inView = host => slides(host).map(slide => !slide.inert);

afterEach(() => {
    mounted.splice(0).forEach(([host, editor]) => {
        editor.destroy();
        host.remove();
    });
});

describe("slider layout", () => {
    it("the list stays the default layout", () => {
        const host = document.createElement("div");
        document.body.append(host);
        const editor = mountAvatarEditor(host, { value: { seed: "user-1" } });
        expect(host.querySelector(".sa-editor-slider")).toBeNull();
        expect(host.querySelector(".sa-editor-sections").children).toHaveLength(9);
        editor.destroy();
        host.remove();
    });

    it("puts the 9 sections in one row of slides, the first in view", () => {
        const { host } = setup();
        expect(slides(host)).toHaveLength(9);
        expect(status(host)).toBe("Face 1 / 9");
        expect(inView(host)).toEqual([true, false, false, false, false, false, false, false, false]);
        expect(slides(host)[1].getAttribute("aria-hidden")).toBe("true");
        expect(slides(host)[0].getAttribute("aria-label")).toBe("Face, 1 of 9");
        // the bar names the section: the heading of the slide is for screen readers only
        expect(slides(host)[0].querySelector("h2").classList.contains("sa-editor-heading--hidden")).toBe(true);
        // every option of every section is still there
        expect(host.querySelectorAll("[role=radiogroup]")).toHaveLength(15);
    });

    it("the chevrons move one section and stop at both ends", () => {
        const { host } = setup();
        const [prev, next] = nav(host);
        expect(prev.disabled).toBe(true);
        expect(prev.getAttribute("aria-label")).toBe("Previous");
        expect(next.getAttribute("aria-label")).toBe("Next");

        next.click();
        expect(status(host)).toBe("Hair 2 / 9");
        expect(inView(host).indexOf(true)).toBe(1);
        expect(prev.disabled).toBe(false);

        prev.click();
        expect(status(host)).toBe("Face 1 / 9");
        for (let i = 0; i < 12; i++) next.click();
        expect(status(host)).toBe("Background 9 / 9");
        expect(next.disabled).toBe(true);
    });

    it("a dot jumps to its section and is marked as current", () => {
        const { host } = setup();
        const dots = [...host.querySelectorAll(".sa-editor-dot")];
        expect(dots.map(dot => dot.getAttribute("aria-label"))).toEqual(
            ["Face", "Hair", "Eyes", "Eyebrows", "Nose", "Mouth", "Outfit", "Accessory", "Background"]
        );
        dots[6].click();
        expect(status(host)).toBe("Outfit 7 / 9");
        expect(dots[6].getAttribute("aria-current")).toBe("true");
        expect(dots[0].hasAttribute("aria-current")).toBe(false);
    });

    it("finds the slide in view from the horizontal scroll", () => {
        expect(slideAt(0, 600, 9)).toBe(0);
        expect(slideAt(590, 600, 9)).toBe(1);
        expect(slideAt(9999, 600, 9)).toBe(8);
        expect(slideAt(-20, 600, 9)).toBe(0);
        expect(slideAt(300, 0, 9)).toBe(0);
    });

    it("uses the host labels, as text", () => {
        const { host } = setup({ labels: { previous: "Precedente", next: "<b>Successiva</b>", slideCount: ":current di :total", sections: { face: "Viso" } } });
        const [prev, next] = nav(host);
        expect(prev.getAttribute("aria-label")).toBe("Precedente");
        expect(next.getAttribute("aria-label")).toBe("<b>Successiva</b>");
        expect(host.querySelector("b")).toBeNull();
        expect(status(host)).toBe("Viso 1 di 9");
    });

    it("choosing an option inside a slide still reports the value", async () => {
        let reported = null;
        const { host } = setup({ onChange: value => (reported = value) });
        nav(host)[1].click();
        const radio = slides(host)[1].querySelector("[role=radio][data-value=bun]");
        radio.click();
        expect(reported.overrides.hair).toBe("bun");
    });

    it("destroy releases the slider listeners", () => {
        const { host, editor } = setup();
        const [, next] = nav(host);
        const title = host.querySelector(".sa-editor-slider-title");
        editor.destroy();
        next.click();
        expect(host.querySelector(".sa-editor-slider")).toBeNull();
        expect(title.textContent).toBe("Face");
    });
});
