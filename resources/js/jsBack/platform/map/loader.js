// loading helpers of the Maps tab (no Leaflet here: this file is part of the small entry)

// runs `start` once, the first time the element enters the viewport (at once without IntersectionObserver)
const whenVisible = (element, start) => {
    if (!("IntersectionObserver" in window)) {
        start();
        return;
    }
    const observer = new IntersectionObserver(entries => {
        if (entries.some(entry => entry.isIntersecting)) {
            observer.disconnect();
            start();
        }
    });
    observer.observe(element);
};

// aggregated data of the map, with the session cookie of the backoffice
const fetchMapData = async url => {
    const response = await fetch(url, { headers: { Accept: "application/json" }, credentials: "same-origin" });
    if (!response.ok) throw new Error(`map data: HTTP ${response.status}`);
    return response.json();
};

export { fetchMapData, whenVisible };
