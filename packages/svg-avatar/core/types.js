// JSDoc contracts only: this module has no runtime code.

/**
 * Complete avatar description. Shape names come from assets/parts.json, colour keys from the palette.
 * @typedef {object} Traits
 * @property {string} face
 * @property {string} hair
 * @property {string} eyes
 * @property {string} brows
 * @property {string} mouth
 * @property {string} clothes
 * @property {string} accessory
 * @property {string} bgColor
 * @property {string} skinColor
 * @property {string} hairColor
 * @property {string} clothColor
 * @property {string} accentColor
 * @property {string} irisColor
 */

/**
 * Any subset of Traits chosen by the consumer; unknown names or values are ignored.
 * @typedef {Partial<Traits>} Overrides
 */

/**
 * Palette: groups of named colours. skin, hair, cloth and accent are [base, shade] pairs.
 * @typedef {object} Palette
 * @property {Record<string, string>} bg
 * @property {Record<string, [string, string]>} skin
 * @property {Record<string, [string, string]>} hair
 * @property {Record<string, [string, string]>} cloth
 * @property {Record<string, [string, string]>} accent
 * @property {Record<string, string>} iris
 * @property {{ ink: string, mouth: string, white: string, blush: string, metal: string }} fixed
 */

/**
 * One drawn shape: attributes of a <path>, colours already resolved.
 * @typedef {{ d: string, fill: string, stroke?: string, "stroke-width"?: number,
 *   "stroke-linecap"?: string, "stroke-linejoin"?: string, opacity?: number }} PathAttrs
 */

export {};
