import axios from "axios";

// the logged-in account (null for guests): same session cookie as the Laravel pages (Sanctum SPA mode)
const fetchMe = async () => (await axios.get("/api/me")).data.user;

// laravel/ui logout; axios adds the X-XSRF-TOKEN header itself (same origin)
const logout = async () => {
    await axios.post("/logout");
};

export { fetchMe, logout };
