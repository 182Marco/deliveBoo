// Translations of the Vue site. The strings live in ONE object, strings.json (read by Laravel too,
// app/I18n/Strings.php): only its "site" and "locales" branches end up in the bundle.
// Leaves are { en, it, de, fr, es }; ":name" placeholders and "one|other" plurals, as in Laravel.
import { ref } from "vue";
import { locales, site } from "./strings.json";

const DEFAULT_LOCALE = "en";
// same cookie read by app/Http/Middleware/SetLocale.php: API answers and mails follow the switch
const COOKIE = "foodsy_locale";
const ONE_YEAR = 60 * 60 * 24 * 365;

const LOCALES = Object.entries(locales).map(([code, e]) => ({ code, ...e }));

const isLocale = code => Object.hasOwn(locales, code ?? "");

// the page is rendered by Laravel in the chosen language: <html lang> is the starting point
const initialLocale = () => {
    const lang = typeof document === "undefined" ? "" : document.documentElement.lang;
    return isLocale(lang) ? lang : DEFAULT_LOCALE;
};

// reactive: every template that calls t() re-renders when the language changes
const locale = ref(initialLocale());

const leafOf = key => key.split(".").reduce((acc, cur) => acc?.[cur], site);

// "1 item|:count items" -> the form for `count` (Intl knows the plural rules of each language)
const pluralForm = (text, count, code) => {
    const forms = text.split("|");
    if (forms.length < 2 || count === undefined) return text;
    return new Intl.PluralRules(code).select(count) === "one" ? forms[0] : forms[1];
};

// ":name" -> params.name; longer names first, so ":names" with { name } keeps its "s" (like Laravel)
const replaceParams = (text, params) =>
    Object.keys(params)
        .sort((a, b) => b.length - a.length)
        .reduce((acc, cur) => acc.split(`:${cur}`).join(String(params[cur])), text);

// translation of `key` (path under "site") in `code`; falls back to English, then to the key itself
const translate = (code, key, params = {}) => {
    const leaf = leafOf(key);
    const text = leaf?.[code] ?? leaf?.[DEFAULT_LOCALE];
    return typeof text === "string" ? replaceParams(pluralForm(text, params.count, code), params) : key;
};

const t = (key, params) => translate(locale.value, key, params);

const currentLocale = () => locale.value;

// switches the whole site at once, and tells the server (cookie) and the browser (<html lang>)
const setLocale = code => {
    if (!isLocale(code)) return;
    locale.value = code;
    document.documentElement.lang = code;
    document.cookie = `${COOKIE}=${code}; path=/; max-age=${ONE_YEAR}; SameSite=Lax`;
};

export { currentLocale, DEFAULT_LOCALE, isLocale, LOCALES, setLocale, t, translate };
