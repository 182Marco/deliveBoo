// svg-avatar: deterministic SVG avatars, no runtime dependencies.
// Public core API (no Vue). The Vue component is exported by ./vue.js.
export { avatarTraits, normalizeTraits, avatarOptions } from "./core/traits/index.js";
export { randomSeed } from "./core/random/index.js";
export { renderAvatarSvg } from "./core/layers/index.js";
export { defaultPalette } from "./palettes/index.js";
