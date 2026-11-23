// The Leaflet map of the Maps tab (lazy chunk). Tiles come straight from OpenStreetMap to the
// browser (never stored nor proxied by the backend), with the attribution always visible; no
// prefetching beyond Leaflet's defaults. ~260 restaurants need no clustering. SVG renderer (Leaflet's
// default), not canvas: base.css caps every canvas at max-width 100%, which distorts Leaflet's
// oversized canvas, while leaflet.css already protects its SVG; each circle is also a real DOM target.
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import { COLORS, DEFAULT_VIEW, boundsOf, maxOf, popupRows, radiusFor } from "./mapData.js";

// names come from the database: always text nodes, never HTML
const popupContent = (title, subtitle, rows) => {
    const box = document.createElement("div");
    box.className = "plat-pop";
    const name = document.createElement("strong");
    name.className = "plat-pop-title";
    name.textContent = title;
    box.append(name);
    if (subtitle) {
        const sub = document.createElement("span");
        sub.className = "plat-pop-sub";
        sub.textContent = subtitle;
        box.append(sub);
    }
    const list = document.createElement("dl");
    list.className = "plat-pop-rows";
    rows.forEach(([label, value]) => {
        const dt = document.createElement("dt");
        dt.textContent = label;
        const dd = document.createElement("dd");
        dd.textContent = value;
        list.append(dt, dd);
    });
    box.append(list);
    return box;
};

const label = text => Object.assign(document.createElement("span"), { textContent: text });

const renderPlatformMap = (element, data, text) => {
    element.replaceChildren();
    const map = L.map(element, { scrollWheelZoom: false });
    const tiles = L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 19,
        // OSM answers 403 to tiles without a Referer: send the origin even where the page context
        // would strip it (strict privacy settings, embedded previews)
        referrerPolicy: "strict-origin-when-cross-origin",
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors'
    }).addTo(map);
    // tiles refused (403) or unreachable before any loaded: say so on the map, the circles and the
    // numbers on the right still work
    let loaded = false;
    tiles.on("tileload", () => {
        loaded = true;
    });
    tiles.once("tileerror", () => {
        if (loaded || element.querySelector(".plat-map-notice")) return;
        element.append(Object.assign(document.createElement("p"), { className: "plat-map-notice", textContent: text.tilesBlocked }));
    });

    const restaurantMax = maxOf(data.restaurants, "gross_value");
    const restaurants = L.layerGroup(
        // the biggest last, so the small ones stay on top and clickable
        [...data.restaurants]
            .sort((a, b) => b.gross_value - a.gross_value)
            .map(r => {
                const sold = r.gross_value > 0;
                return L.circleMarker([r.lat, r.lng], {
                    radius: radiusFor(r.gross_value, restaurantMax),
                    color: "#ffffff",
                    weight: 2,
                    fillColor: sold ? COLORS.restaurant : COLORS.none,
                    fillOpacity: sold ? 0.75 : 0.45
                })
                    .bindTooltip(label(r.name))
                    .bindPopup(popupContent(r.name, r.city, popupRows(r, text)));
            })
    );

    const zones = data.zones.filter(z => z.lat !== null);
    const zoneMax = maxOf(zones, "gross_value");
    const zoneLayer = L.layerGroup(
        zones.map(z =>
            L.circleMarker([z.lat, z.lng], {
                radius: radiusFor(z.gross_value, zoneMax, 8, 36),
                color: "#ffffff",
                weight: 2,
                fillColor: COLORS.zone,
                fillOpacity: 0.55
            })
                .bindTooltip(label(z.zone))
                .bindPopup(popupContent(z.zone, text.restaurantsInZone.replace(":count", z.restaurants), popupRows(z, text)))
        )
    );

    restaurants.addTo(map);
    L.control.layers(null, { [text.restaurants]: restaurants, [text.zones]: zoneLayer }, { collapsed: false }).addTo(map);

    const bounds = boundsOf(data.restaurants);
    if (bounds) map.fitBounds(bounds, { padding: [24, 24], maxZoom: 14 });
    else map.setView(DEFAULT_VIEW.center, DEFAULT_VIEW.zoom);

    // right size after layout changes (breakpoints, rotation, the side panel moving below)
    if ("ResizeObserver" in window) new ResizeObserver(() => map.invalidateSize()).observe(element);

    return map;
};

export { renderPlatformMap };
