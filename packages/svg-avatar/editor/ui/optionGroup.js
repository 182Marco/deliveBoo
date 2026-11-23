import { h, setAttr, setSvg } from "./dom.js";

// Radio group of buttons (WAI-ARIA radio pattern): one tab stop, arrows/Home/End move and select.
// Option content: { svg } thumbnail, { swatch } colour or { text } label.
// hideLabel: the label stays for assistive technology only (e.g. it repeats the section heading)
const createRadioGroup = ({ id, label, signal, onChoose, className = "sa-editor-options", hideLabel = false }) => {
    const labelEl = h("p", { class: hideLabel ? "sa-editor-sr" : "sa-editor-group-label", id: `${id}-label` }, [label]);
    const list = h("div", { class: className, role: "radiogroup", "aria-labelledby": `${id}-label` });
    const el = h("div", { class: "sa-editor-group" }, [labelEl, list]);
    const buttons = new Map();
    const divider = h("p", { class: "sa-editor-divider", "aria-hidden": "true" });

    const ordered = () => [...list.querySelectorAll("[role=radio]")];

    const focusAndChoose = button => {
        button.focus();
        onChoose(button.dataset.value);
    };

    list.addEventListener("keydown", event => {
        const items = ordered();
        const index = items.indexOf(event.target);
        if (index < 0) return;
        const moves = {
            ArrowRight: index + 1, ArrowDown: index + 1, ArrowLeft: index - 1, ArrowUp: index - 1,
            Home: 0, End: items.length - 1
        };
        if (!(event.key in moves)) return;
        event.preventDefault();
        focusAndChoose(items[(moves[event.key] + items.length) % items.length]);
    }, { signal });

    const buttonFor = value => {
        if (!buttons.has(value)) {
            const button = h("button", { type: "button", role: "radio", class: "sa-editor-option", "data-value": value });
            button.addEventListener("click", () => onChoose(value), { signal });
            buttons.set(value, button);
        }
        return buttons.get(value);
    };

    const fill = (button, option) => {
        if (option.svg !== undefined) {
            if (!button.firstChild) button.append(h("span", { class: "sa-editor-thumb", "aria-hidden": "true" }));
            setSvg(button.firstChild, option.svg);
        } else if (option.swatch !== undefined) {
            if (!button.firstChild) button.append(h("span", { class: "sa-editor-swatch", "aria-hidden": "true" }));
            button.firstChild.style.backgroundColor = option.swatch;
        } else {
            button.textContent = option.text;
        }
    };

    // options: [{ value, name?, selected, suggested?, svg | swatch | text }], in display order
    const update = ({ options, otherLabel = "" }) => {
        const children = [];
        options.forEach((option, index) => {
            if (index > 0 && option.suggested === false && options[index - 1].suggested !== false) {
                divider.textContent = otherLabel;
                children.push(divider);
            }
            const button = buttonFor(option.value);
            fill(button, option);
            setAttr(button, "aria-label", option.name ?? null);
            setAttr(button, "aria-checked", option.selected ? "true" : "false");
            button.tabIndex = option.selected ? 0 : -1;
            button.className = option.selected ? "sa-editor-option sa-editor-option--selected" : "sa-editor-option";
            children.push(button);
        });
        // keep the group reachable with Tab even if nothing is selected
        if (!options.some(option => option.selected) && children.length) buttonFor(options[0].value).tabIndex = 0;
        const current = [...list.children];
        if (current.length !== children.length || current.some((node, i) => node !== children[i])) list.replaceChildren(...children);
    };

    return { el, update };
};

export { createRadioGroup };
