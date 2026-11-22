import axios from "axios";

// POST /api/orders answers 200 also on validation errors, with { errors }
const postOrder = async order => (await axios.post("/api/orders", order)).data;

export { postOrder };
