// Read-only catalog endpoints of the customer site (routes/api.php)
import axios from "axios";

const fetchTypes = async () => (await axios.get("/api/types")).data.types;

// typeIds is the list of selected types: the API reads it as "1,2,3"
const fetchRestaurantsByTypes = async (typeIds, page) =>
    (await axios.get(`/api/restaurants/${typeIds.join(",")}?page=${page}`)).data;

const fetchRestaurantMenu = async (slug, page) =>
    (await axios.get(`/api/restaurantsMenu/${slug}?page=${page}`)).data;

// suggestions of the header search; `signal` (AbortController) drops a request made obsolete by typing
const searchRestaurants = async (query, signal) =>
    (await axios.get("/api/restaurantsSearch", { params: { q: query }, signal })).data;

const fetchTopRated = async () => (await axios.get("/api/restaurantsTopRated")).data;

export { fetchTypes, fetchRestaurantsByTypes, fetchRestaurantMenu, fetchTopRated, searchRestaurants };
