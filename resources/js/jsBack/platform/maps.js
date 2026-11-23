// Maps tab of the platform backoffice. This entry is tiny on purpose: Leaflet (and its CSS) is a
// separate chunk imported only when the map box is actually on screen, together with the
// aggregated data of GET /platform/data/map. The other tabs never load it.
import { fetchMapData, whenVisible } from "./map/loader.js";

const box = document.getElementById("platform-map");

if (box) {
    const text = JSON.parse(box.dataset.text || "{}");
    whenVisible(box, async () => {
        try {
            const [{ renderPlatformMap }, data] = await Promise.all([import("./map/platformMap.js"), fetchMapData(box.dataset.src)]);
            renderPlatformMap(box, data, text);
        } catch (error) {
            console.error(error);
            box.replaceChildren(Object.assign(document.createElement("p"), { className: "plat-map-status", textContent: text.error }));
        }
    });
}
