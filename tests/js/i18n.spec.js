// Translations of the Vue site: one object (resources/js/utils/strings.json), five languages, English by default.
import { describe, it, expect, afterEach } from "vitest";
import { mount } from "@vue/test-utils";
import { LocaleSwitch, OrderSummary } from "../../resources/js/components";
import { currentLocale, LOCALES, setLocale, t, translate } from "../../resources/js/utils/i18n.js";
import { createAppStore as createStore } from "../../resources/js/store.js";

afterEach(() => {
    setLocale("en");
    document.body.innerHTML = "";
});

describe("translate", () => {
    it("English by default, with :placeholders", () => {
        expect(currentLocale()).toBe("en");
        expect(t("menu.page", { current: 2, last: 5 })).toBe("Page 2 of 5");
    });

    it("picks the plural form with the rules of each language", () => {
        expect(translate("en", "summary.items", { count: 1 })).toBe("1 item");
        expect(translate("en", "summary.items", { count: 0 })).toBe("0 items");
        // French: 0 is singular
        expect(translate("fr", "summary.items", { count: 0 })).toBe("0 article");
        expect(translate("it", "summary.items", { count: 3 })).toBe("3 articoli");
    });

    it("longer placeholders first: ':names' keeps its s", () => {
        expect(translate("en", "cuisine.titleStarred", { name: "Starred restaurant" })).toBe("Starred restaurants");
    });

    it("an unknown key comes back as is", () => {
        expect(t("nope.missing")).toBe("nope.missing");
    });

    it("five languages, each with flag code and native name", () => {
        expect(LOCALES.map(e => e.code)).toEqual(["en", "it", "de", "fr", "es"]);
        expect(LOCALES.find(e => e.code === "de")).toMatchObject({ short: "DE", name: "Deutsch" });
    });
});

describe("setLocale", () => {
    it("switches texts, <html lang> and the cookie read by Laravel", () => {
        setLocale("de");
        expect(t("cart.title")).toBe("Deine Tasche");
        expect(document.documentElement.lang).toBe("de");
        expect(document.cookie).toContain("foodsy_locale=de");
    });

    it("ignores an unknown language", () => {
        setLocale("xx");
        expect(currentLocale()).toBe("en");
    });

    it("re-renders mounted components at once", async () => {
        const wrapper = mount(OrderSummary, { global: { plugins: [createStore()] } });
        expect(wrapper.text()).toContain("Your order");
        setLocale("es");
        await wrapper.vm.$nextTick();
        expect(wrapper.text()).toContain("Tu pedido");
    });
});

describe("LocaleSwitch", () => {
    it("shows flag + code, lists the five languages and switches on click", async () => {
        const wrapper = mount(LocaleSwitch, { attachTo: document.body });
        const trigger = wrapper.find(".ui-lang-trigger");
        expect(trigger.text()).toBe("EN");
        expect(trigger.attributes("aria-expanded")).toBe("false");

        await trigger.trigger("click");
        const options = wrapper.findAll(".ui-lang-option");
        expect(options).toHaveLength(5);
        expect(options.every(e => e.find("svg").exists())).toBe(true);
        expect(wrapper.find('[aria-current="true"]').attributes("lang")).toBe("en");

        await options.find(e => e.attributes("lang") === "it").trigger("click");
        expect(currentLocale()).toBe("it");
        expect(wrapper.find(".ui-lang-panel").exists()).toBe(false);
        expect(wrapper.find(".ui-lang-trigger").text()).toBe("IT");
        expect(wrapper.find(".ui-lang-trigger").attributes("aria-label")).toBe("Lingua: Italiano");
        wrapper.unmount();
    });

    it("Esc closes the menu", async () => {
        const wrapper = mount(LocaleSwitch, { attachTo: document.body });
        await wrapper.find(".ui-lang-trigger").trigger("click");
        await wrapper.find(".ui-lang").trigger("keydown", { key: "Escape" });
        expect(wrapper.find(".ui-lang-panel").exists()).toBe(false);
        wrapper.unmount();
    });
});
