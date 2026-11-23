// Test-only: the shared options contract (tests/fixtures/options.json), also read by the host's server tests.
import rules from "../rules/rules.json";
import { avatarOptions } from "../index.js";

// every forbidden pair of an exclude rule, as { category: value, category: value }
const excludedPairs = () => rules.exclude.flatMap(rule => {
    const [first, second] = Object.keys(rule).filter(key => key !== "prefer");
    return rule[first].flatMap(a => rule[second].map(b => ({ [first]: a, [second]: b })));
});

const optionsContract = () => {
    const { shapes, colors } = avatarOptions();
    return { traits: { ...shapes, ...colors }, excludedPairs: excludedPairs() };
};

export { excludedPairs, optionsContract };
