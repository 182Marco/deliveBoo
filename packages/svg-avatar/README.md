# svg-avatar

Deterministic cartoon avatars (half-bust, flat style) drawn with plain SVG paths.
No runtime dependencies; an optional Vue 3 component and a framework-free editor.

- The same **seed** (any string) always gives the same avatar.
- **Overrides** let a user pick single traits (hair, eyes, colours…) on top of the seed.
- Colours come from a **palette** you can extend or replace without touching the drawings.
- Output: a `<svg>` string (any framework) or a Vue component that renders the same paths.

## Install / import

The package is currently consumed from source through a bundler alias
(the host app maps `svg-avatar` to this folder). It expects a bundler that can import JSON
(Vite, webpack, Rollup with the JSON plugin).

```js
import { avatarTraits, renderAvatarSvg, randomSeed } from "svg-avatar"; // core, no Vue
import { Avatar } from "svg-avatar/vue";                                 // Vue 3 component
import { mountAvatarEditor } from "svg-avatar/editor";                   // editor (DOM + CSS, no framework)
```

## Public API

### Core (`svg-avatar`)

| Export | Description |
|---|---|
| `avatarTraits(seed, overrides?, { palette? })` | Complete, valid `Traits` for a seed. Valid overrides win over the seed; invalid ones are ignored. |
| `normalizeTraits(traits, { palette? })` | Completes partial or broken traits (unknown values → first option) and applies the compatibility rules. |
| `avatarOptions(palette?)` | `{ shapes: { face: [...], hair: [...], … }, colors: { bgColor: [...], … } }`, for editors and server-side whitelists. |
| `renderAvatarSvg(traits, { palette?, size?, title?, shape? })` | `<svg>` markup. Decorative (`aria-hidden`) unless `title` is given. `shape`: `"circle"` (default, CSS `clip-path`) or `"square"`. |
| `randomSeed()` | 16 hex characters from `crypto.getRandomValues`. |
| `defaultPalette` | The default palette, to spread and customise. |

### Vue (`svg-avatar/vue`)

`<Avatar seed="user-42" :overrides="{ hair: 'bun' }" :size="40" label="Anna" />`

Props: `seed` (String), `overrides` (Object), `traits` (Object, skips the seed), `palette` (Object),
`size` (Number|String, default 40), `label` (String: accessible name; empty = decorative).

### Traits

`face`, `hair`, `eyes`, `brows`, `mouth`, `clothes`, `accessory`, `nose` (shape names) and
`bgColor`, `skinColor`, `hairColor`, `clothColor`, `accentColor`, `irisColor` (palette keys).
The full list of values is returned by `avatarOptions()`. Contracts in JSDoc: `core/types.js`.

## Editor (`svg-avatar/editor`)

A complete "customise your avatar" interface: preview, one radio group per trait (thumbnails of the current
avatar with that option, colour swatches), a look filter, *Surprise me* and *Reset*. Plain DOM and CSS
(`editor/editor.css`, imported by the module: use a bundler that handles CSS imports).
It knows nothing about routes, storage or the host page: you give it an element and read the value.

```js
import { mountAvatarEditor } from "svg-avatar/editor";

const editor = mountAvatarEditor(element, {
    value: { seed: "user-42", overrides: { hair: "bun" } }, // the stored value (invalid parts are ignored)
    palette,  // optional, defaults to defaultPalette
    labels,   // optional translations, same shape as defaultEditorLabels (any subset, strings only)
    layout: "list", // or "slider": one section at a time (see below)
    onChange: value => { /* { seed, overrides } */ }
});
```

**Layouts.** `"list"` (default) shows the nine sections one under the other. `"slider"` shows one
section at a time in a row of slides: it moves with horizontal scrolling (trackpad, swipe; it snaps one
section at a time), with the two chevrons of the bar above (disabled at both ends) and, from 600px, with
one dot per section. The bar names the section and its position (`slideCount`, e.g. "2 / 9"); the track
takes the height of the section in view; slides out of view are `inert`, so Tab stays in the visible
section. Slider labels: `features` (region), `previous`, `next`, `slideCount`, `slideOf` (`:name`,
`:current`, `:total`).

The element's content is replaced. The value format is the same one `avatarTraits(seed, overrides)` reads.

| Method / callback | Behaviour |
|---|---|
| **initial reference** | The `value` given to `mountAvatarEditor`. It never changes during the instance's life (neither `setValue` nor saving updates it). For a new reference, `destroy()` and mount again (in Vue: change the component `key`). |
| `getValue()` | A **new object** `{ seed, overrides }` every call. `overrides` are minimal (only what differs from what the seed alone gives) and always reproduce the preview: `avatarTraits(seed, overrides)` equals the shown avatar. After `destroy()` it returns the last value. |
| `setValue(value)` | Replaces the **current** value (invalid parts ignored) and redraws. Does **not** call `onChange` and does not touch the initial reference. No-op when it resolves to the current avatar. |
| `reset()` | Back to the initial reference; calls `onChange` if the value changed. The *Reset* button does the same. |
| `isDirty()` | `true` when the **resulting avatar** differs from the initial one (two values that draw the same avatar are not dirty). |
| `onChange(value)` | Called with the same object `getValue()` returns, only when the value really changes because of the editor: a choice, *Surprise me*, *Reset* / `reset()`. Never on mount, `setValue`, the look filter or `destroy`. |
| `destroy()` | Removes the editor, aborts all its listeners and any pending frame. Idempotent; afterwards every method is a no-op. |

Behaviour:
- **Look filter** (Everyone / Masculine / Feminine): reorders hairstyles, outfits and accessories (suggested first,
  then "Other options"); nothing is hidden and the value never changes. *Surprise me* picks hairstyle and outfit
  from the active look.
- **Choices and rules**: the latest choice wins; if a compatibility rule has to move another trait, that trait
  changes and the change is announced in a polite live region.
- **Accessibility**: WAI-ARIA radio groups (one tab stop each, arrows/Home/End), descriptive option names
  (`":trait: :value"`, e.g. "Hairstyle: Bun"), focus-visible outlines, 44 px targets.
- **Theming**: set any of `--sa-ink`, `--sa-muted`, `--sa-surface`, `--sa-well`, `--sa-line`, `--sa-accent`,
  `--sa-radius`, `--sa-font`, `--sa-heading-font` on the element.

A Vue wrapper needs no change in the core: mount in `mounted`, `destroy` in `beforeUnmount`,
`onChange` → `emit("update:modelValue")`, watch `modelValue` → `setValue`, `key` for a new initial reference.

**Saving is the host's job**: read `getValue()` (or `onChange`) and validate it on your server against the
package data (`tests/fixtures/options.json` lists the allowed values and the forbidden override pairs).

## Custom palette

```js
import { defaultPalette, avatarTraits, renderAvatarSvg } from "svg-avatar";

const palette = { ...defaultPalette, bg: { mint: "#d8f0e4", sand: "#efe6d2" } };
renderAvatarSvg(avatarTraits("user-1", {}, { palette }), { palette });
```

Groups `skin`, `hair`, `cloth`, `accent` are `[base, shade]` pairs; `bg` and `iris` single colours;
`fixed` holds `ink`, `mouth`, `white`, `blush`, `metal`.

## Data contract (for other implementations, e.g. a server-side adapter)

1. `seed` is converted to a string and hashed with **cyrb53** over its UTF-16 code units (salt 0).
2. **mulberry32** is started with `hash mod 2^32`.
3. One draw per **seeded** shape category in this order: `face, hair, eyes, brows, mouth, clothes, accessory`.
   Categories listed in `parts.json → unseededCategories` (`nose`) are never drawn: they take their first
   variant, so avatars created before they existed keep the same look.
   Weighted choice: `target = rand() * totalWeight`, walk the variants in file order subtracting each
   weight (default 1) and take the first that makes `target < 0`.
4. One draw per colour trait in this order: `bgColor, skinColor, hairColor, clothColor, accentColor, irisColor`:
   `keys[floor(rand() * keys.length)]` over the palette group keys in insertion order.
5. Valid overrides replace drawn values; then `rules/rules.json` → `exclude` is applied
   (fix the `prefer` category unless it was overridden; replacement = first variant not forbidden).
6. Drawing: layers in `parts.json → layers` order, skipping the ones listed by `rules.json → hideLayers`
   for the tags of the selected variants; each layer draws `base[layer]` then every selected variant's shapes for it.

`tests/fixtures/seeds.json` holds seeds with the expected traits (plus `nose: "soft"`, added later and never drawn):
an implementation is correct when it reproduces them. `tests/fixtures/compat.json` freezes the drawing of 517 seeds
and every pre-editor override: existing avatars must never change. `tests/fixtures/options.json` lists the allowed
values and forbidden override pairs for server-side validation.

### `assets/parts.json`

- `viewBox`: 120 (square). `layers`: drawing order. `slots`: allowed colour slots.
- `base`: shapes drawn for every avatar (background, neck, ears, cheeks).
- `unseededCategories`: categories not drawn from the seed (default = first variant).
- `categories.<category>.<variant>`: `{ weight?, tags?, look?, <layer>: [shape, …] }` (`look`: editor hint, `["m"]`, `["f"]` or both).
- Shape: one of `d` (path data), `c` `[cx, cy, r]`, `e` `[cx, cy, rx, ry]`;
  `f` fill slot, `s` stroke slot, `w` stroke width, `o` opacity. Never a literal colour.

## Structure and dependency rules

| Level | Module | May import |
|---|---|---|
| 0 | `assets/`, `palettes/`, `core/random/`, `core/types.js` | level 0 only |
| 1 | `rules/` | 0 |
| 2 | `core/traits/` | 0–1 |
| 3 | `core/layers/` | 0–2 |
| 4 | `components/` | 0–3 and `vue` |
| 5 | `editor/state/`, `editor/groups.js`, `editor/labels.js`, `editor/editor.css` | 0–5 |
| 6 | `editor/ui/` | 0–6 |
| 7 | `editor/mount.js` | 0–7 |
| 8 | `index.js`, `vue.js`, `editor.js` | anything below |

- Imports only point to lower levels (or within the same module); nothing inside the package imports
  `index.js` / `vue.js` / `editor.js`; nothing imports outside the package except `vue`.
- The editor never looks up the host page (`document.querySelector`, `getElementById`, `body`…).
- `tests/boundaries.spec.js` enforces these rules.

## Tests

From the host project: `npx vitest run packages/svg-avatar`.

## Credits

All drawings are original. The general look (flat cartoon half-bust) is inspired by the
illustration style popularised by avatar libraries such as Avataaars; no shapes or assets are copied.
