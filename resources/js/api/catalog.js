// Read-only catalog endpoints of the customer site (routes/api.php)
import axios from "axios";

const fetchTypes = async () => (await axios.get("/api/types")).data.types;

// typeIds is the list of selected types: the API reads it as "1,2,3"
const fetchRestaurantsByTypes = async (typeIds, page) =>
    (await axios.get(`/api/restaurants/${typeIds.join(",")}?page=${page}`)).data;

const fetchRestaurantMenu = async (slug, page) =>
    (await axios.get(`/api/restaurantsMenu/${slug}?page=${page}`)).data;

const fetchTopRated = async () => (await axios.get("/api/restaurantsTopRated")).data;

export { fetchTypes, fetchRestaurantsByTypes, fetchRestaurantMenu, fetchTopRated };
