// Reviews: written from "My orders" for a delivered order, read by the restaurant page (routes/api.php)
import axios from "axios";

// write or edit the review of an order of the account: { review, can_review }; 422 validation, 403 not allowed
const saveReview = async (orderId, { rating, comment }) =>
    (await axios.put(`/api/me/orders/${orderId}/review`, { rating, comment })).data;

// summary (average, count, distribution) and the visible reviews with a text, a Laravel paginator
const fetchRestaurantReviews = async (slug, page = 1) =>
    (await axios.get(`/api/restaurants/${slug}/reviews`, { params: { page } })).data;

export { fetchRestaurantReviews, saveReview };
