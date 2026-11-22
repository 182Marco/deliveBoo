// Laravel paginator -> { current, last } used by PaginateEl
const toPagination = ({ current_page, last_page }) => ({ current: current_page, last: last_page });

export { toPagination };
