import rules from "./rules.json";
import { tagsOf, variantNames } from "../assets/catalog.js";

// a rule matches when every category it lists holds one of its forbidden names
const conflicts = (rule, traits) => Object.entries(rule)
    .filter(([key]) => key !== "prefer")
    .every(([category, names]) => names.includes(traits[category]));

// change the preferred category unless the consumer locked it (explicit override)
const categoryToFix = (rule, locked) => {
    const categories = Object.keys(rule).filter(key => key !== "prefer");
    const free = [rule.prefer, ...categories].find(category => !locked.has(category));
    return free ?? rule.prefer;
};

// first variant of the category that the rule does not forbid ("none" for accessories)
const replacement = (rule, category) => variantNames(category).find(name => !rule[category].includes(name));

// returns a copy of traits with every forbidden combination resolved
const applyRules = (traits, locked = new Set()) => rules.exclude.reduce((current, rule) => {
    if (!conflicts(rule, current)) return current;
    const category = categoryToFix(rule, locked);
    return { ...current, [category]: replacement(rule, category) };
}, { ...traits });

// layers that the selected variants hide (e.g. ears under long hair)
const hiddenLayers = traits => {
    const tags = tagsOf(traits);
    return new Set(rules.hideLayers.filter(rule => tags.has(rule.tag)).flatMap(rule => rule.layers));
};

export { applyRules, hiddenLayers };
