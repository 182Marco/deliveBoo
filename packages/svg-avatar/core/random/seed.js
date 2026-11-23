import { cyrb53 } from "./hash.js";
import { mulberry32 } from "./prng.js";

// generator for a seed string: the low 32 bits of its hash start mulberry32
const seededRandom = seed => mulberry32(cyrb53(String(seed ?? "")) % 4294967296);

// fresh random seed: 16 hex chars from the platform CSPRNG when available
const randomSeed = () => {
    const bytes = new Uint8Array(8);
    if (globalThis.crypto?.getRandomValues) {
        globalThis.crypto.getRandomValues(bytes);
    } else {
        bytes.forEach((_, i) => { bytes[i] = Math.floor(Math.random() * 256); });
    }
    return Array.from(bytes, b => b.toString(16).padStart(2, "0")).join("");
};

export { seededRandom, randomSeed };
