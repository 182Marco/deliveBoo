// Laravel paginator -> { current, last } used by PaginateEl
const toPagination = ({ current_page, last_page }) => ({ current: current_page, last: last_page });

// page numbers to show: all of them up to `max`, otherwise first, last and `span` around the current one,
// with "…" (null) for the gaps, like the Blade view pagination/foodsy (a long history stays on one row)
const pageWindow = (current, last, span = 1, max = 7) => {
    if (last <= max) return Array.from({ length: last }, (_, i) => i + 1);
    const from = Math.max(2, current - span);
    const to = Math.min(last - 1, current + span);
    const middle = Array.from({ length: to - from + 1 }, (_, i) => from + i);
    return [1, ...(from > 2 ? [null] : []), ...middle, ...(to < last - 1 ? [null] : []), last];
};

export { pageWindow, toPagination };
