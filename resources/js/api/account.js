import axios from "axios";

// the logged-in account (null for guests): same session cookie as the Laravel pages (Sanctum SPA mode)
const fetchMe = async () => (await axios.get("/api/me")).data.user;

// laravel/ui logout; axios adds the X-XSRF-TOKEN header itself (same origin)
const logout = async () => {
    await axios.post("/logout");
};

// "My orders" of the logged-in account: a Laravel paginator; a guest gets 401 (the page shows the login)
const fetchMyOrders = async (page = 1) => (await axios.get("/api/me/orders", { params: { page } })).data;

export { fetchMe, fetchMyOrders, logout };
