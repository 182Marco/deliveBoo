// cyrb53: fast, well-distributed 53-bit string hash (public domain, bryc).
// Integer-only maths, so the same string gives the same number in every JS engine.
const cyrb53 = (text, salt = 0) => {
    let h1 = 0xdeadbeef ^ salt;
    let h2 = 0x41c6ce57 ^ salt;
    for (let i = 0; i < text.length; i++) {
        const ch = text.charCodeAt(i);
        h1 = Math.imul(h1 ^ ch, 2654435761);
        h2 = Math.imul(h2 ^ ch, 1597334677);
    }
    h1 = Math.imul(h1 ^ (h1 >>> 16), 2246822507);
    h1 ^= Math.imul(h2 ^ (h2 >>> 13), 3266489909);
    h2 = Math.imul(h2 ^ (h2 >>> 16), 2246822507);
    h2 ^= Math.imul(h1 ^ (h1 >>> 13), 3266489909);
    return 4294967296 * (2097151 & h2) + (h1 >>> 0);
};

export { cyrb53 };
