// Maps tab of the backoffice: pure helpers, Leaflet loaded only when the map is visible, and the
// build keeping Leaflet out of every other bundle (dev/PIANO_AUTH_CLIENTI.md, step 4.3).
import { describe, it, expect, vi, beforeEach, afterEach } from "vitest";
import { existsSync, readFileSync } from "node:fs";
import { resolve } from "node:path";
import { COLORS, boundsOf, maxOf, money, popupRows, radiusFor } from "../../resources/js/jsBack/platform/map/mapData.js";

const state = vi.hoisted(() => ({ loaded: 0, render: null }));

// counts when the Leaflet chunk is imported (its factory runs only on import)
vi.mock("../../resources/js/jsBack/platform/map/platformMap.js", () => {
    state.loaded++;
    state.render = vi.fn();
    return { renderPlatformMap: (...args) => state.render(...args) };
});

describe("mapData", () => {
    it("radius grows with the square root of the value, small grey dot without orders", () => {
        expect(radiusFor(100, 100)).toBe(14);
        expect(radiusFor(25, 100)).toBeCloseTo(3 + 11 * 0.5);
        expect(radiusFor(0, 100)).toBe(2.5);
        expect(radiusFor(10, 0)).toBe(2.5);
        expect(radiusFor(100, 100, 8, 36)).toBe(36);
    });

    it("bounds and maxima of the points", () => {
        expect(boundsOf([])).toBeNull();
        expect(boundsOf([{ lat: 47.1, lng: 8.9 }, { lat: 47.5, lng: 8.4 }])).toEqual([[47.1, 8.4], [47.5, 8.9]]);
        expect(maxOf([{ v: 3 }, { v: 9 }, {}], "v")).toBe(9);
    });

    it("popup rows of a restaurant and of a zone", () => {
        const text = { gross: "Gross", orders: "Orders", average: "Avg", revenue: "Rev", rating: "Rating" };
        expect(popupRows({ gross_value: 1234.5, completed_orders: 7, average_order: 20, revenue: 300, rating: null }, text)).toEqual([
            ["Gross", "€1,234.50"], ["Orders", "7"], ["Avg", "€20.00"], ["Rev", "€300.00"], ["Rating", "–"]
        ]);
        expect(popupRows({ gross_value: 10, completed_orders: 1, revenue: 2.5 }, text).map(r => r[0])).toEqual(["Gross", "Orders", "Rev"]);
        expect(money(null)).toBe("–");
        expect(Object.values(COLORS)).toHaveLength(3);
    });
});

describe("lazy loading", () => {
    let observers;

    beforeEach(() => {
        vi.resetModules();
        state.loaded = 0;
        observers = [];
        globalThis.IntersectionObserver = class {
            constructor(callback) {
                this.callback = callback;
                observers.push(this);
            }
            observe() {}
            disconnect() {
                this.disconnected = true;
            }
        };
        globalThis.fetch = vi.fn(async () => ({ ok: true, json: async () => ({ restaurants: [], zones: [], missing: { count: 0, items: [] } }) }));
        document.body.innerHTML = `<div id="platform-map" data-src="/platform/data/map?from=2026-01-01" data-text='{"error":"Map error"}'></div>`;
    });

    afterEach(() => {
        delete globalThis.IntersectionObserver;
        document.body.innerHTML = "";
    });

    const flush = () => new Promise(r => setTimeout(r, 0));

    it("imports Leaflet and asks for the data only when the map becomes visible", async () => {
        await import("../../resources/js/jsBack/platform/maps.js");
        await flush();
        expect(state.loaded).toBe(0);
        expect(fetch).not.toHaveBeenCalled();

        observers[0].callback([{ isIntersecting: false }]);
        await flush();
        expect(state.loaded).toBe(0);

        observers[0].callback([{ isIntersecting: true }]);
        await flush();
        await flush();
        expect(state.loaded).toBe(1);
        expect(fetch).toHaveBeenCalledWith("/platform/data/map?from=2026-01-01", expect.objectContaining({ credentials: "same-origin" }));
        expect(state.render).toHaveBeenCalledTimes(1);
        expect(observers[0].disconnected).toBe(true);
    });

    it("does nothing on the other tabs (no map box)", async () => {
        document.body.innerHTML = "";
        await import("../../resources/js/jsBack/platform/maps.js");
        await flush();
        expect(observers).toHaveLength(0);
        expect(state.loaded).toBe(0);
    });

    it("shows the error text when the data cannot be loaded", async () => {
        globalThis.fetch = vi.fn(async () => ({ ok: false, status: 500 }));
        vi.spyOn(console, "error").mockImplementation(() => {});
        await import("../../resources/js/jsBack/platform/maps.js");
        observers[0].callback([{ isIntersecting: true }]);
        await flush();
        await flush();
        expect(document.getElementById("platform-map").textContent).toBe("Map error");
    });
});

// only meaningful after `npm run build` (public/build is build output, not versioned)
const manifestPath = resolve(__dirname, "../../public/build/manifest.json");
describe.skipIf(!existsSync(manifestPath))("build", () => {
    it("Leaflet is a dynamic import of the Maps entry only", () => {
        const manifest = JSON.parse(readFileSync(manifestPath, "utf8"));
        const mapChunk = "resources/js/jsBack/platform/map/platformMap.js";
        const staticClosure = (key, seen = new Set()) => {
            if (seen.has(key)) return seen;
            seen.add(key);
            (manifest[key].imports || []).forEach(i => staticClosure(i, seen));
            return seen;
        };
        for (const [key, chunk] of Object.entries(manifest).filter(([, c]) => c.isEntry)) {
            expect(staticClosure(key).has(mapChunk), `${key} must not import Leaflet statically`).toBe(false);
            const dynamic = chunk.dynamicImports || [];
            expect(dynamic.includes(mapChunk)).toBe(key === "resources/js/jsBack/platform/maps.js");
        }
    });
});
