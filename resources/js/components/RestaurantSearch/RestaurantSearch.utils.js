// helpers of the header search: what to ask, how to show a suggestion (texts in the current language)
import { t } from "../../utils/i18n.js";

// same threshold of RestaurantController@search: shorter queries are not sent
const MIN_QUERY_LENGTH = 2;

const normalizeQuery = query => query.trim().replace(/\s+/g, " ");

const isSearchable = query => normalizeQuery(query).length >= MIN_QUERY_LENGTH;

// "Pizza Napoli" + "nap" -> [{ text: "Pizza ", match: false }, { text: "Nap", match: true }, { text: "oli", match: false }]
const highlightParts = (text, query) => {
    const needle = normalizeQuery(query).toLowerCase();
    const start = needle ? text.toLowerCase().indexOf(needle) : -1;
    return start < 0
        ? [{ text, match: false }]
        : [
              { text: text.slice(0, start), match: false },
              { text: text.slice(start, start + needle.length), match: true },
              { text: text.slice(start + needle.length), match: false }
          ].filter(e => e.text);
};

// 1..4 -> "€".."€€€€"; unknown -> ""
const priceLevel = range => (range ? "€".repeat(range) : "");

// "Pizzeria, Fast Food · Modena · €€"
const resultMeta = rest =>
    [(rest.types ?? []).join(", "), rest.city, priceLevel(rest.price_range)].filter(Boolean).join(" · ");

const ratingLabel = average => (average ? average.toFixed(1) : t("search.new"));

const resultsMessage = (count, query) =>
    count ? t("search.live", { count }) : t("search.liveNone", { query });

export { highlightParts, isSearchable, normalizeQuery, ratingLabel, resultMeta, resultsMessage };
