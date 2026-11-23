import "./editor.css";
import { COLOR_TRAITS, defaultPalette } from "../palettes/index.js";
import { composeLayers, renderAvatarSvg, toSvgString } from "../core/layers/index.js";
import { randomSeed } from "../core/random/index.js";
import { SECTIONS, LOOKS, THUMB_VIEWBOX, kindOf } from "./groups.js";
import { resolveLabels, interpolate, traitLabel, variantLabel } from "./labels.js";
import { createStore, editorReducer, initialState, isDirty, optionsFor, previewTraits, sideChanges, valueOf } from "./state/index.js";
import { createLiveRegion, createLookFilter, createPreview, createRadioGroup, createSlider, createSwatchGroup, createToolbar } from "./ui/index.js";
import { h } from "./ui/dom.js";

let instances = 0;

const schedule = globalThis.requestAnimationFrame
    ? { request: cb => globalThis.requestAnimationFrame(cb), cancel: id => globalThis.cancelAnimationFrame(id) }
    : { request: cb => setTimeout(cb, 16), cancel: id => clearTimeout(id) };

const sameValue = (a, b) => a.seed === b.seed && JSON.stringify(a.overrides) === JSON.stringify(b.overrides);

const copy = value => ({ seed: value.seed, overrides: { ...value.overrides } });

// swatch = base colour of the palette entry ([base, shade] pairs show the base)
const swatchColor = (palette, trait, key) => {
    const color = palette[COLOR_TRAITS[trait]]?.[key];
    return Array.isArray(color) ? color[0] : color;
};

/**
 * Mounts the avatar editor inside `element` (its content is replaced).
 * @param {Element} element
 * @param {{ value?: { seed: string, overrides?: object }, palette?: object, labels?: object,
 *           layout?: "list" | "slider", fillMedia?: string,
 *           onChange?: (value: { seed: string, overrides: object }) => void }} [options]
 * layout "list" (default): every section one under the other; "slider": one section at a time,
 * moved by horizontal scrolling, two chevrons and dots.
 * fillMedia (slider only), a media query: while it matches, the editor fills the height of `element`
 * (preview and controls on top, the slide in view takes the rest and scrolls on its own), so the
 * preview stays on screen while choosing. The host gives `element` a height (e.g. a full-screen sheet).
 * @returns {{ getValue, setValue, isDirty, reset, destroy }}
 */
const mountAvatarEditor = (element, { value, palette = defaultPalette, labels: customLabels, layout = "list", fillMedia, onChange } = {}) => {
    if (!element || typeof element.replaceChildren !== "function") throw new TypeError("mountAvatarEditor: a DOM element is required");

    instances += 1;
    const id = `sa-editor-${instances}`;
    const labels = resolveLabels(customLabels);
    const controller = new AbortController();
    const { signal } = controller;
    const store = createStore(editorReducer, initialState({ value, palette }));
    let destroyed = false;
    let pending = null;
    let lastValue = copy(valueOf(store.getState()));

    // ---- intents coming from the UI (or reset()): report value changes to the host
    const act = action => {
        if (destroyed) return;
        const before = valueOf(store.getState());
        const state = store.dispatch(action);
        const after = valueOf(state);
        if (action.type === "choose") {
            const moved = sideChanges(state, action.trait)
                .map(change => interpolate(labels.ruleChanged, {
                    trait: traitLabel(labels, change.trait),
                    value: variantLabel(labels, change.trait, change.to),
                    cause: variantLabel(labels, action.trait, action.value)
                }));
            live.announce(moved.join(" "));
        }
        if (!sameValue(before, after)) onChange?.(copy(after));
    };

    // ---- UI
    const live = createLiveRegion();
    const preview = createPreview();
    const toolbar = createToolbar({
        labels, signal,
        onSurprise: () => act({ type: "surprise", seed: randomSeed() }),
        onReset: () => act({ type: "reset" })
    });
    const lookFilter = createLookFilter({
        id: `${id}-look`, label: labels.look, signal,
        onChoose: look => act({ type: "setLook", look })
    });

    const groups = SECTIONS.flatMap(section => section.traits.map(trait => {
        const create = kindOf(trait) === "color" ? createSwatchGroup : createRadioGroup;
        return [trait, create({
            id: `${id}-${trait}`, label: traitLabel(labels, trait), signal,
            // a label that repeats the section heading stays for screen readers only
            hideLabel: section.traits.length === 1
                || traitLabel(labels, trait).toLowerCase() === String(labels.sections[section.id]).toLowerCase(),
            onChoose: chosen => act({ type: "choose", trait, value: chosen })
        })];
    }));
    const groupOf = Object.fromEntries(groups);

    const slider = layout === "slider";
    let sliderUi = null;
    const sections = SECTIONS.map(section => {
        const headingId = `${id}-section-${section.id}`;
        return h("section", { class: "sa-editor-section", "aria-labelledby": slider ? null : headingId }, [
            // in the slider the bar above names the section: the heading stays for screen readers
            h("h2", { class: slider ? "sa-editor-heading sa-editor-heading--hidden" : "sa-editor-heading", id: headingId }, [labels.sections[section.id]]),
            ...section.traits.map(trait => groupOf[trait].el)
        ]);
    });

    if (slider) sliderUi = createSlider({ id, labels, signal, slides: sections, titles: SECTIONS.map(section => labels.sections[section.id]) });
    const aside = h("div", { class: "sa-editor-aside" }, [preview.el, toolbar.el, lookFilter.el]);
    const root = h("div", { class: "sa-editor", id }, [
        aside,
        slider ? sliderUi.el : h("div", { class: "sa-editor-sections" }, sections),
        live.el
    ]);

    // ---- render (state -> UI); first render is synchronous, later ones once per frame
    const thumbnail = (state, trait, value) => toSvgString(
        composeLayers(previewTraits(state, trait, value), state.palette),
        { size: "100%", viewBox: THUMB_VIEWBOX[trait] }
    );

    const optionView = (state, trait) => optionsFor(state, trait).map(option => ({
        ...option,
        name: interpolate(labels.option, { trait: traitLabel(labels, trait), value: variantLabel(labels, trait, option.value) }),
        ...(kindOf(trait) === "color"
            ? { swatch: swatchColor(state.palette, trait, option.value) }
            : { svg: thumbnail(state, trait, option.value) })
    }));

    const render = () => {
        pending = null;
        if (destroyed) return;
        const state = store.getState();
        preview.update({ svg: renderAvatarSvg(state.traits, { palette: state.palette, size: "100%", title: labels.preview }) });
        toolbar.update({ canReset: isDirty(state) });
        lookFilter.update({ options: LOOKS.map(look => ({ value: look, text: labels.looks[look], selected: state.look === look })) });
        groups.forEach(([trait, group]) => group.update({ options: optionView(state, trait), otherLabel: labels.otherOptions }));
    };

    const unsubscribe = store.subscribe(() => {
        lastValue = copy(valueOf(store.getState()));
        if (pending === null) pending = schedule.request(render);
    });

    render();
    element.replaceChildren(root);

    // ---- fill mode, while the host's media query matches
    const fillQuery = slider && fillMedia && globalThis.matchMedia ? globalThis.matchMedia(fillMedia) : null;
    const applyFill = () => {
        const on = !!fillQuery?.matches;
        root.classList.toggle("sa-editor--fill", on);
        aside.classList.toggle("sa-editor-aside--fill", on);
        preview.el.classList.toggle("sa-editor-preview--fill", on);
        toolbar.el.classList.toggle("sa-editor-toolbar--fill", on);
        lookFilter.el.classList.toggle("sa-editor-look--fill", on);
        sliderUi?.setFill(on);
    };
    if (fillQuery) {
        fillQuery.addEventListener?.("change", applyFill, { signal });
        applyFill();
    }

    // ---- public handle
    return {
        getValue: () => (destroyed ? copy(lastValue) : copy(valueOf(store.getState()))),
        setValue: next => {
            if (!destroyed) store.dispatch({ type: "setValue", value: next });
        },
        isDirty: () => !destroyed && isDirty(store.getState()),
        reset: () => act({ type: "reset" }),
        destroy: () => {
            if (destroyed) return;
            destroyed = true;
            controller.abort();
            unsubscribe();
            if (pending !== null) schedule.cancel(pending);
            pending = null;
            root.remove();
        }
    };
};

export { mountAvatarEditor };
