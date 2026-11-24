import axios from "axios";

// The cart in the database (dev/PIANO_CARRELLO.md): of the logged-in account, otherwise of the guest
// cookie, which the server sets and the site never reads. Every answer is { cart, notices };
// errors (409 other restaurant, 422 not orderable or limits) carry the current cart too.
// A 404 on GET means the server cart is switched off: the site keeps its local cart.
const fetchCart = async () => (await axios.get("/api/cart")).data;

// absolute number of portions (idempotent: a repeated request changes nothing)
const putCartItem = async (plateId, quantity) => (await axios.put(`/api/cart/items/${plateId}`, { quantity })).data;

const deleteCartItem = async plateId => (await axios.delete(`/api/cart/items/${plateId}`)).data;

const clearServerCart = async () => (await axios.delete("/api/cart")).data;

// one-off: the cart kept in localStorage by the previous version of the site, only into an empty cart
const importCart = async items => (await axios.post("/api/cart/import", { items })).data;

// the CSRF token Laravel leaves in the XSRF-TOKEN cookie (axios reads it by itself; fetch does not)
const xsrfToken = () =>
    decodeURIComponent(
        document.cookie
            .split("; ")
            .find(e => e.startsWith("XSRF-TOKEN="))
            ?.slice("XSRF-TOKEN=".length) ?? ""
    );

// while the page unloads (reload, closed tab, link to a Laravel page): fetch with keepalive still
// reaches the server, axios would be cancelled. Absolute quantity: sending it twice changes nothing.
const sendCartItemOnUnload = (plateId, quantity) =>
    fetch(`/api/cart/items/${plateId}`, {
        method: quantity > 0 ? "PUT" : "DELETE",
        keepalive: true,
        credentials: "same-origin",
        headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "X-XSRF-TOKEN": xsrfToken()
        },
        body: quantity > 0 ? JSON.stringify({ quantity }) : undefined
    }).catch(() => {});

export { clearServerCart, deleteCartItem, fetchCart, importCart, putCartItem, sendCartItemOnUnload };
